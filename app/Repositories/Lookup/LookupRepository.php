<?php

namespace App\Repositories\Lookup;

use App\Models\AuditLog\Action;
use App\Models\Authentication\LoginStatus;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\UserManagement\Permission;
use App\Models\UserManagement\UserStatus;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class LookupRepository implements LookupRepositoryInterface
{
    /**
     * @var array<string, array{
     *     model: class-string<Model>,
     *     primary_key: string,
     *     label_column: string,
     *     order_column: string,
     *     writable: bool,
     *     extra_columns?: list<string>
     * }>
     */
    private const TYPE_MAP = [
        'personnel-position' => [
            'model' => PersonnelPosition::class,
            'primary_key' => 'position_id',
            'label_column' => 'position_name',
            'order_column' => 'position_name',
            'writable' => true,
        ],
        'personnel-status' => [
            'model' => PersonnelStatus::class,
            'primary_key' => 'personnel_status_id',
            'label_column' => 'personnel_status',
            'order_column' => 'personnel_status',
            'writable' => false,
        ],
        'user-status' => [
            'model' => UserStatus::class,
            'primary_key' => 'user_status_id',
            'label_column' => 'user_status',
            'order_column' => 'user_status',
            'writable' => false,
            'extra_columns' => ['can_login'],
        ],
        'login-status' => [
            'model' => LoginStatus::class,
            'primary_key' => 'login_status_id',
            'label_column' => 'login_status',
            'order_column' => 'login_status',
            'writable' => false,
        ],
        'action' => [
            'model' => Action::class,
            'primary_key' => 'action_id',
            'label_column' => 'action',
            'order_column' => 'action_id',
            'writable' => false,
        ],
        'permission' => [
            'model' => Permission::class,
            'primary_key' => 'permission_id',
            'label_column' => 'permission',
            'order_column' => 'permission',
            'writable' => false,
        ],
    ];

    /**
     * @return list<string>
     */
    public function supportedTypes(): array
    {
        return array_keys(self::TYPE_MAP);
    }

    public function isSupported(string $type): bool
    {
        return array_key_exists($type, self::TYPE_MAP);
    }

    public function isWritable(string $type): bool
    {
        return $this->config($type)['writable'];
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(string $type): Collection
    {
        $config = $this->config($type);

        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        return $modelClass::query()
            ->orderBy($config['order_column'])
            ->get();
    }

    public function findById(string $type, int $id): ?Model
    {
        $config = $this->config($type);

        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        return $modelClass::query()
            ->where($config['primary_key'], $id)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $type, array $attributes): Model
    {
        if (! $this->isWritable($type)) {
            throw new InvalidArgumentException("Lookup type [{$type}] is read-only.");
        }

        $config = $this->config($type);

        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        return $modelClass::create($attributes);
    }

    public function delete(string $type, int $id): bool
    {
        if (! $this->isWritable($type)) {
            throw new InvalidArgumentException("Lookup type [{$type}] is read-only.");
        }

        $record = $this->findById($type, $id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function isInUse(string $type, int $id): bool
    {
        if ($type !== 'personnel-position') {
            return false;
        }

        $record = $this->findById($type, $id);

        if (! $record instanceof PersonnelPosition) {
            return false;
        }

        return $record->personnel()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(string $type, Model $record): array
    {
        $config = $this->config($type);

        $payload = [
            'id' => $record->getAttribute($config['primary_key']),
            'label' => $record->getAttribute($config['label_column']),
        ];

        foreach ($config['extra_columns'] ?? [] as $column) {
            $payload[$column] = $record->getAttribute($column);
        }

        return $payload;
    }

    /**
     * @return array{
     *     model: class-string<Model>,
     *     primary_key: string,
     *     label_column: string,
     *     order_column: string,
     *     writable: bool,
     *     extra_columns?: list<string>
     * }
     */
    private function config(string $type): array
    {
        if (! $this->isSupported($type)) {
            throw new InvalidArgumentException("Unsupported lookup type [{$type}].");
        }

        return self::TYPE_MAP[$type];
    }
}
