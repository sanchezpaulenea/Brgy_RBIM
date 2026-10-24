<?php

namespace App\Services\DatabaseBackup;

use App\Exports\DatabaseWorkbookExport;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class DatabaseBackupService
{
    private const VERSION_LINE = '-- RBIM-BACKUP 1';

    public function __construct(
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array{body: string, filename: string, content_type: string}
     */
    public function export(User $performedBy, string $format): array
    {
        return $this->locked(function () use ($performedBy, $format): array {
            $file = $this->render($format);
            $this->recordExport($performedBy, $format);

            return $file;
        });
    }

    public function restore(User $performedBy, string $sql): void
    {
        try {
            $this->locked(function () use ($performedBy, $sql): void {
                $this->storeSafetyCopy();
                $this->replace($sql);
                $this->recordRestore($performedBy);
            });
        } catch (ValidationException|ConflictHttpException|HttpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new HttpException(500, 'The database could not be restored. Current records were left unchanged.');
        }
    }

    /**
     * @return array{body: string, filename: string, content_type: string}
     */
    private function render(string $format): array
    {
        try {
            $stamp = now()->format('Y-m-d-His');

            return match ($format) {
                'sql' => [
                    'body' => $this->sqlDocument(),
                    'filename' => "rbim-backup-{$stamp}.sql",
                    'content_type' => 'application/sql; charset=UTF-8',
                ],
                'excel' => [
                    'body' => Excel::raw(new DatabaseWorkbookExport($this->workbookSheets()), ExcelFormat::XLSX),
                    'filename' => "rbim-backup-{$stamp}.xlsx",
                    'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ],
                'pdf' => [
                    'body' => Pdf::loadView('backups.records', [
                        'barangay' => $this->barangayName(),
                        'generatedAt' => now()->timezone((string) config('app.timezone'))->format('F j, Y g:i A'),
                        'tables' => $this->printableTables(),
                    ])->setPaper('a4', 'landscape')->output(),
                    'filename' => "rbim-backup-{$stamp}.pdf",
                    'content_type' => 'application/pdf',
                ],
                default => throw ValidationException::withMessages([
                    'format' => 'Format must be SQL, Excel, or PDF.',
                ]),
            };
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (QueryException|RuntimeException $exception) {
            report($exception);

            throw new HttpException(500, 'The backup could not be created. Please try again.');
        }
    }

    private function storeSafetyCopy(): void
    {
        $sql = $this->sqlDocument();
        $path = 'backups/pre-restore-'.now()->format('Ymd-His').'.sql';

        if (! Storage::disk('local')->put($path, $sql)) {
            throw new HttpException(500, 'A safety copy could not be saved, so the restore was stopped.');
        }

        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $file): bool => str_ends_with($file, '.sql'))
            ->sort()
            ->values();

        while ($files->count() > 5) {
            Storage::disk('local')->delete((string) $files->shift());
        }
    }

    private function replace(string $sql): void
    {
        $document = $this->parse($sql);

        Schema::disableForeignKeyConstraints();

        try {
            DB::beginTransaction();

            try {
                foreach ($document['tables'] as $table) {
                    DB::table($table)->delete();
                }

                foreach ($document['rows'] as $table => $rows) {
                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }

                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();

                throw $exception;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        try {
            $this->resetAutoIncrements($document['tables']);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function sqlDocument(): string
    {
        $lines = [
            '-- Generated: '.now()->timezone((string) config('app.timezone'))->toIso8601String(),
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            'START TRANSACTION;',
        ];

        foreach ($this->tableNames() as $table) {
            $columns = $this->columns($table);
            $names = array_column($columns, 'name');
            $lines[] = 'DELETE FROM `'.$table.'`;';

            foreach ($this->rawRows($table, $names) as $row) {
                $values = [];

                foreach ($columns as $column) {
                    $values[] = $this->literal($row[$column['name']] ?? null, (string) $column['type_name']);
                }

                $lines[] = 'INSERT INTO `'.$table.'` ('.$this->columnList($names).') VALUES ('.implode(', ', $values).');';
            }

            foreach ($columns as $column) {
                if (($column['auto_increment'] ?? false) !== true) {
                    continue;
                }

                $max = DB::table($table)->max($column['name']);
                $next = $max === null ? 1 : ((int) $max + 1);
                $lines[] = 'ALTER TABLE `'.$table.'` AUTO_INCREMENT = '.$next.';';
            }
        }

        $lines[] = 'COMMIT;';
        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        $schema = $this->schemaHash();
        $body = implode("\n", $lines)."\n";
        $signature = hash_hmac('sha256', $schema."\n".$body, $this->signingKey());

        return self::VERSION_LINE."\n-- Schema: {$schema}\n-- Signature: {$signature}\n{$body}";
    }

    /**
     * @return array{tables: list<string>, rows: array<string, list<array<string, mixed>>>}
     */
    private function parse(string $sql): array
    {
        $sql = preg_replace("/^\xEF\xBB\xBF/", '', $sql) ?? $sql;
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $lines = explode("\n", $sql);

        if (str_ends_with($sql, "\n")) {
            array_pop($lines);
        }

        if (($lines[0] ?? '') !== self::VERSION_LINE) {
            $this->rejectFile();
        }

        $schemaLine = $lines[1] ?? '';
        $signatureLine = $lines[2] ?? '';

        if (! preg_match('/^-- Schema: ([a-f0-9]{64})$/', $schemaLine, $schemaMatch)) {
            $this->rejectFile();
        }

        if (! preg_match('/^-- Signature: ([a-f0-9]{64})$/', $signatureLine, $signatureMatch)) {
            $this->rejectFile();
        }

        $bodyLines = array_slice($lines, 3);
        $body = implode("\n", $bodyLines)."\n";
        $schema = $schemaMatch[1];
        $expectedSignature = hash_hmac('sha256', $schema."\n".$body, $this->signingKey());

        if (! hash_equals($expectedSignature, $signatureMatch[1])) {
            $this->rejectFile();
        }

        if (! hash_equals($this->schemaHash(), $schema)) {
            throw ValidationException::withMessages([
                'backup' => 'This backup does not match the current database structure.',
            ]);
        }

        $expectedTables = $this->tableNames();
        $deleted = [];
        $rows = [];

        foreach ($bodyLines as $line) {
            if ($line === '' || str_starts_with($line, '-- ') || $this->ignoredStatement($line)) {
                continue;
            }

            if (preg_match('/^DELETE FROM `([A-Za-z0-9_]+)`;$/', $line, $deleteMatch) === 1) {
                $table = $deleteMatch[1];
                $this->assertKnownTable($table, $expectedTables);
                $deleted[] = $table;

                continue;
            }

            if (preg_match('/^ALTER TABLE `([A-Za-z0-9_]+)` AUTO_INCREMENT = \d+;$/', $line, $alterMatch) === 1) {
                $this->assertKnownTable($alterMatch[1], $expectedTables);

                continue;
            }

            $insert = $this->parseInsert($line);

            if ($insert !== null) {
                $this->assertKnownTable($insert['table'], $expectedTables);
                $known = array_column($this->columns($insert['table']), 'name');

                if ($insert['columns'] !== $known) {
                    $this->rejectFile();
                }

                $values = $this->parseValues($insert['values']);

                if (count($values) !== count($insert['columns'])) {
                    $this->rejectFile();
                }

                $rows[$insert['table']][] = array_combine($insert['columns'], $values);

                continue;
            }

            $this->rejectFile();
        }

        $deleted = array_values(array_unique($deleted));
        sort($deleted);
        $expected = $expectedTables;

        if ($deleted !== $expected) {
            throw ValidationException::withMessages([
                'backup' => 'The backup file is incomplete.',
            ]);
        }

        return [
            'tables' => $expected,
            'rows' => $rows,
        ];
    }

    /**
     * @param  list<string>  $expectedTables
     */
    private function assertKnownTable(string $table, array $expectedTables): void
    {
        if (! in_array($table, $expectedTables, true)) {
            $this->rejectFile();
        }
    }

    /**
     * @return array{table: string, columns: list<string>, values: string}|null
     */
    private function parseInsert(string $line): ?array
    {
        $prefix = 'INSERT INTO `';

        if (! str_starts_with($line, $prefix) || ! str_ends_with($line, ');')) {
            return null;
        }

        $body = substr($line, strlen($prefix), -2);
        $marker = ') VALUES (';
        $split = strpos($body, $marker);

        if ($split === false) {
            return null;
        }

        $tableEnd = strpos($body, '`');

        if ($tableEnd === false || $tableEnd > $split) {
            return null;
        }

        $table = substr($body, 0, $tableEnd);

        if (preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) {
            return null;
        }

        $columnSql = substr($body, $tableEnd + 1, $split - ($tableEnd + 1));

        if (! str_starts_with($columnSql, ' (')) {
            return null;
        }

        $columnSql = substr($columnSql, 2);
        $columns = [];

        foreach (explode(', ', $columnSql) as $column) {
            if (preg_match('/^`([A-Za-z0-9_]+)`$/', $column, $match) !== 1) {
                return null;
            }

            $columns[] = $match[1];
        }

        if ($columns === []) {
            return null;
        }

        return [
            'table' => $table,
            'columns' => $columns,
            'values' => substr($body, $split + strlen($marker)),
        ];
    }

    private function ignoredStatement(string $line): bool
    {
        return in_array($line, [
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            'SET FOREIGN_KEY_CHECKS=1;',
            'START TRANSACTION;',
            'COMMIT;',
        ], true);
    }

    /**
     * @return list<mixed>
     */
    private function parseValues(string $input): array
    {
        $values = [];
        $length = strlen($input);
        $index = 0;
        $expectValue = true;

        while ($index < $length) {
            $character = $input[$index];

            if ($character === ' ') {
                $index++;

                continue;
            }

            if ($character === ',') {
                if ($expectValue) {
                    $this->rejectFile();
                }

                $expectValue = true;
                $index++;

                continue;
            }

            if (! $expectValue) {
                $this->rejectFile();
            }

            if (substr($input, $index, 4) === 'NULL' && ! ctype_alnum($input[$index + 4] ?? '')) {
                $values[] = null;
                $index += 4;
                $expectValue = false;

                continue;
            }

            if ($character === "'") {
                [$value, $index] = $this->parseString($input, $index);
                $values[] = $value;
                $expectValue = false;

                continue;
            }

            if (preg_match('/\G-?\d+\.\d+/', $input, $decimal, 0, $index) === 1) {
                $values[] = $decimal[0];
                $index += strlen($decimal[0]);
                $expectValue = false;

                continue;
            }

            if (preg_match('/\G-?\d+/', $input, $integer, 0, $index) === 1) {
                $token = $integer[0];
                $digits = ltrim($token, '-');
                $values[] = strlen($digits) > 15 ? $token : (int) $token;
                $index += strlen($token);
                $expectValue = false;

                continue;
            }

            $this->rejectFile();
        }

        if ($expectValue && $values !== []) {
            $this->rejectFile();
        }

        return $values;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function parseString(string $input, int $index): array
    {
        $index++;
        $length = strlen($input);
        $value = '';

        while ($index < $length) {
            $character = $input[$index];

            if ($character === '\\' && $index + 1 < $length) {
                $next = $input[$index + 1];
                $value .= match ($next) {
                    'n' => "\n",
                    'r' => "\r",
                    '0' => "\0",
                    '\\' => '\\',
                    "'" => "'",
                    default => $next,
                };
                $index += 2;

                continue;
            }

            if ($character === "'") {
                if (($input[$index + 1] ?? '') === "'") {
                    $value .= "'";
                    $index += 2;

                    continue;
                }

                return [$value, $index + 1];
            }

            $value .= $character;
            $index++;
        }

        $this->rejectFile();
    }

    /**
     * @param  list<string>  $tables
     */
    private function resetAutoIncrements(array $tables): void
    {
        foreach ($tables as $table) {
            foreach ($this->columns($table) as $column) {
                if (($column['auto_increment'] ?? false) !== true) {
                    continue;
                }

                $max = DB::table($table)->max($column['name']);
                $next = $max === null ? 1 : ((int) $max + 1);
                $driver = DB::getDriverName();

                if (in_array($driver, ['mysql', 'mariadb'], true)) {
                    DB::statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = '.$next);

                    continue;
                }

                if ($driver === 'sqlite' && Schema::hasTable('sqlite_sequence')) {
                    $updated = DB::table('sqlite_sequence')->where('name', $table)->update(['seq' => max(0, $next - 1)]);

                    if ($updated === 0 && $next > 1) {
                        DB::table('sqlite_sequence')->insert([
                            'name' => $table,
                            'seq' => $next - 1,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @return list<array{title: string, headings: list<string>, rows: list<list<string>>}>
     */
    private function workbookSheets(): array
    {
        $used = [];
        $sheets = [];

        foreach ($this->readingTables(null) as $table) {
            $title = $this->sheetTitle($table['name'], $used);
            $used[$title] = true;
            $sheets[] = [
                'title' => $title,
                'headings' => $table['columns'],
                'rows' => array_map(
                    fn (array $row): array => array_map($this->cell(...), $row),
                    $table['rows'],
                ),
            ];
        }

        return $sheets;
    }

    /**
     * @return list<array{name: string, columns: list<string>, rows: list<list<string>>, total: int, truncated: bool}>
     */
    private function printableTables(): array
    {
        $limit = max(1, (int) config('database_backup.pdf_row_limit', 300));

        return array_map(function (array $table): array {
            return [
                'name' => $table['name'],
                'columns' => $table['columns'],
                'rows' => array_map(
                    fn (array $row): array => array_map($this->cell(...), $row),
                    $table['rows'],
                ),
                'total' => $table['total'],
                'truncated' => $table['total'] > count($table['rows']),
            ];
        }, $this->readingTables($limit));
    }

    /**
     * @return list<array{name: string, columns: list<string>, rows: list<list<mixed>>, total: int}>
     */
    private function readingTables(?int $limit): array
    {
        $redacted = array_fill_keys($this->redactedColumns(), true);
        $tables = [];

        foreach ($this->tableNames() as $table) {
            $columns = array_values(array_filter(
                array_column($this->columns($table), 'name'),
                fn (string $column): bool => ! isset($redacted[$column]),
            ));

            if ($columns === []) {
                continue;
            }

            $total = $limit === null
                ? 0
                : (int) DB::table($table)->count();
            $rows = [];

            foreach ($this->rawRows($table, $columns) as $row) {
                $line = [];

                foreach ($columns as $column) {
                    $line[] = $row[$column] ?? null;
                }

                $rows[] = $line;

                if ($limit !== null && count($rows) >= $limit) {
                    break;
                }
            }

            $tables[] = [
                'name' => $table,
                'columns' => $columns,
                'rows' => $rows,
                'total' => $limit === null ? count($rows) : $total,
            ];
        }

        return $tables;
    }

    /**
     * @param  list<string>  $columns
     * @return \Generator<int, array<string, mixed>>
     */
    private function rawRows(string $table, array $columns): \Generator
    {
        if ($columns === []) {
            return;
        }

        foreach (DB::table($table)->select($columns)->orderBy($columns[0])->lazy(200) as $row) {
            $record = [];

            foreach ($columns as $column) {
                $value = $row->{$column} ?? null;
                $record[$column] = is_bool($value) ? (int) $value : $value;
            }

            yield $record;
        }
    }

    /**
     * @return list<string>
     */
    private function tableNames(): array
    {
        $excluded = array_fill_keys($this->excludedTables(), true);
        $names = [];

        foreach (Schema::getTables() as $table) {
            $name = $table['name'] ?? null;

            if (! is_string($name) || preg_match('/^[A-Za-z0-9_]+$/', $name) !== 1) {
                continue;
            }

            if (str_starts_with($name, 'sqlite_') || isset($excluded[$name])) {
                continue;
            }

            $names[] = $name;
        }

        sort($names);

        return $names;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function columns(string $table): array
    {
        return array_values(array_filter(
            Schema::getColumns($table),
            fn (array $column): bool => is_string($column['name'] ?? null)
                && preg_match('/^[A-Za-z0-9_]+$/', $column['name']) === 1,
        ));
    }

    private function schemaHash(): string
    {
        $parts = [];

        foreach ($this->tableNames() as $table) {
            $columns = array_map(
                fn (array $column): string => $column['name'].':'.strtolower((string) ($column['type_name'] ?? '')),
                $this->columns($table),
            );
            $parts[] = $table.'('.implode(',', $columns).')';
        }

        return hash('sha256', implode('|', $parts));
    }

    /**
     * @param  list<string>  $columns
     */
    private function columnList(array $columns): string
    {
        return implode(', ', array_map(
            fn (string $column): string => '`'.$column.'`',
            $columns,
        ));
    }

    private function literal(mixed $value, string $typeName): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            $value = $value ? 1 : 0;
        }

        if ($this->isIntegerType($typeName) && is_int($value)) {
            return (string) $value;
        }

        if ($this->isIntegerType($typeName) && is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return $value;
        }

        $string = (string) $value;
        $string = str_replace(
            ['\\', "\0", "\n", "\r", "'"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'"],
            $string,
        );

        return "'{$string}'";
    }

    private function isIntegerType(string $typeName): bool
    {
        return in_array(strtolower($typeName), [
            'integer',
            'int',
            'bigint',
            'smallint',
            'mediumint',
            'tinyint',
            'boolean',
            'bool',
        ], true);
    }

    private function cell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;

        if (mb_strlen($text) > 32000) {
            return mb_substr($text, 0, 32000);
        }

        return $text;
    }

    /**
     * @param  array<string, true>  $used
     */
    private function sheetTitle(string $table, array $used): string
    {
        $title = mb_substr($table, 0, 31);
        $suffix = 2;

        while (isset($used[$title])) {
            $ending = '-'.$suffix;
            $title = mb_substr($table, 0, 31 - mb_strlen($ending)).$ending;
            $suffix++;
        }

        return $title;
    }

    private function barangayName(): string
    {
        if (! Schema::hasTable('system_setting')) {
            return 'Barangay RBIM';
        }

        $name = DB::table('system_setting')->where('setting_key', 'barangay_name')->value('setting_value');

        return is_string($name) && $name !== '' ? $name : 'Barangay RBIM';
    }

    /**
     * @return list<string>
     */
    private function excludedTables(): array
    {
        $tables = config('database_backup.excluded_tables', []);

        return is_array($tables) ? array_values(array_filter($tables, 'is_string')) : [];
    }

    /**
     * @return list<string>
     */
    private function redactedColumns(): array
    {
        $columns = config('database_backup.redacted_columns', []);

        return is_array($columns) ? array_values(array_filter($columns, 'is_string')) : [];
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new RuntimeException('Application key is not configured.');
        }

        return $key;
    }

    private function recordExport(User $performedBy, string $format): void
    {
        if (! Schema::hasTable('audit_log')) {
            return;
        }

        $this->auditLogRepository->log(
            performedByUserId: $performedBy->user_id,
            actionId: Action::EXPORT,
            recordId: 0,
            description: 'Export database backup',
            oldValue: null,
            newValue: $format,
            target: 'format',
            entity: 'database_backup',
        );
    }

    private function recordRestore(User $performedBy): void
    {
        if (! Schema::hasTable('audit_log') || ! Schema::hasTable('user')) {
            return;
        }

        if (! DB::table('user')->where('user_id', $performedBy->user_id)->exists()) {
            return;
        }

        try {
            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: 0,
                description: 'Restore database backup',
                oldValue: null,
                newValue: 'SQL backup',
                target: 'restore',
                entity: 'database_backup',
            );
        } catch (QueryException $exception) {
            report($exception);
        }
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function locked(callable $callback): mixed
    {
        $lock = Cache::lock('database-backup', 300);

        if (! $lock->get()) {
            throw new ConflictHttpException('A backup or restore is already running. Wait for it to finish, then try again.');
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }

    private function rejectFile(): never
    {
        throw ValidationException::withMessages([
            'backup' => 'This file is not a backup created by this system.',
        ]);
    }
}
