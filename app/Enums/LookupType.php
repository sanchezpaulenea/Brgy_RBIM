<?php

namespace App\Enums;

use App\Models\Audit\Action;
use App\Models\Authentication\LoginStatus;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\UserManagement\Permission;
use App\Models\UserManagement\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

enum LookupType: string
{
    case PersonnelPosition = 'personnel-position';
    case PersonnelStatus = 'personnel-status';
    case UserStatus = 'user-status';
    case LoginStatus = 'login-status';
    case Action = 'action';
    case Permission = 'permission';

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::PersonnelPosition => PersonnelPosition::class,
            self::PersonnelStatus => PersonnelStatus::class,
            self::UserStatus => UserStatus::class,
            self::LoginStatus => LoginStatus::class,
            self::Action => Action::class,
            self::Permission => Permission::class,
        };
    }

    public function isWritable(): bool
    {
        return $this === self::PersonnelPosition;
    }

    public static function fromSlug(string $slug): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $slug) {
                return $case;
            }
        }

        throw new NotFoundHttpException("Lookup type [{$slug}] is not supported.");
    }
}
