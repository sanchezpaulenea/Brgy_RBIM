<?php

namespace Tests\Unit\DatabaseBackup;

use App\Models\UserManagement\User;
use App\Services\DatabaseBackup\DatabaseBackupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class DatabaseBackupServiceTest extends TestCase
{
    private DatabaseBackupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DatabaseBackupService::class);

        Schema::dropIfExists('backup_sample');
        Schema::dropIfExists('sessions');

        Schema::create('backup_sample', function ($table): void {
            $table->integer('sample_id')->primary();
            $table->string('label');
            $table->string('password_hash');
        });

        Schema::create('sessions', function ($table): void {
            $table->string('id')->primary();
            $table->string('payload');
        });

        DB::table('backup_sample')->insert([
            'sample_id' => 1,
            'label' => "O'Brien\nline",
            'password_hash' => 'secret-hash',
        ]);

        DB::table('sessions')->insert([
            'id' => 'abc',
            'payload' => 'keep-me',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('backup_sample');
        Schema::dropIfExists('sessions');

        parent::tearDown();
    }

    public function test_sql_backup_round_trips_records_and_leaves_sessions_alone(): void
    {
        Storage::fake('local');

        $sql = $this->service->export($this->user(), 'sql')['body'];

        $this->assertStringContainsString('secret-hash', $sql);
        $this->assertStringNotContainsString('DELETE FROM `sessions`', $sql);

        DB::table('backup_sample')->where('sample_id', 1)->update([
            'label' => 'changed',
            'password_hash' => 'other-hash',
        ]);
        DB::table('sessions')->where('id', 'abc')->update([
            'payload' => 'changed',
        ]);

        $this->service->restore($this->user(), $sql);

        $this->assertSame("O'Brien\nline", DB::table('backup_sample')->where('sample_id', 1)->value('label'));
        $this->assertSame('secret-hash', DB::table('backup_sample')->where('sample_id', 1)->value('password_hash'));
        $this->assertSame('changed', DB::table('sessions')->where('id', 'abc')->value('payload'));
        Storage::disk('local')->assertExists(
            collect(Storage::disk('local')->allFiles())->first(fn (string $path): bool => str_ends_with($path, '.sql')) ?? ''
        );
    }

    public function test_tampered_sql_is_rejected_and_records_stay(): void
    {
        Storage::fake('local');

        $sql = $this->service->export($this->user(), 'sql')['body'];
        $tampered = substr($sql, 0, -4).'nope';

        try {
            $this->service->restore($this->user(), $tampered);
            $this->fail('A tampered backup was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'This file is not a backup created by this system.',
                $exception->errors()['backup'][0],
            );
        }

        $this->assertSame("O'Brien\nline", DB::table('backup_sample')->where('sample_id', 1)->value('label'));
    }

    public function test_backup_from_a_different_structure_is_rejected(): void
    {
        Storage::fake('local');

        $sql = $this->service->export($this->user(), 'sql')['body'];

        Schema::table('backup_sample', function ($table): void {
            $table->string('extra_note')->nullable();
        });

        DB::table('backup_sample')->where('sample_id', 1)->update([
            'label' => 'changed',
        ]);

        try {
            $this->service->restore($this->user(), $sql);
            $this->fail('A backup for a different structure was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'This backup does not match the current database structure.',
                $exception->errors()['backup'][0],
            );
        }

        $this->assertSame('changed', DB::table('backup_sample')->where('sample_id', 1)->value('label'));
    }

    public function test_excel_and_pdf_copies_omit_password_hashes(): void
    {
        $excel = $this->service->export($this->user(), 'excel')['body'];
        $path = storage_path('framework/database-backup-test.xlsx');
        file_put_contents($path, $excel);

        try {
            $spreadsheet = IOFactory::load($path);
            $values = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                foreach ($sheet->toArray() as $row) {
                    foreach ($row as $cell) {
                        $values[] = (string) $cell;
                    }
                }
            }
        } finally {
            @unlink($path);
        }

        $this->assertContains("O'Brien\nline", $values);
        $this->assertNotContains('secret-hash', $values);

        $pdf = $this->service->export($this->user(), 'pdf')['body'];

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    private function user(): User
    {
        $user = new User;
        $user->user_id = 1;

        return $user;
    }
}
