<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    /**
     * Demo Guest account for UI testing.
     *
     * Username: guest
     * Password: Temp12345
     * Role: Guest (change password only; read-only lookups)
     */
    public function run(): void
    {
        if (DB::table('user')->where('username', 'guest')->exists()) {
            $this->command?->info('Guest account already exists (username: guest).');

            return;
        }

        $userId = DB::table('user')->insertGetId([
            'username' => 'guest',
            'password_hash' => Hash::make('Temp12345'),
            'user_status_id' => 1,
            'personnel_id' => null,
            'avatar_preset' => 'male',
            'must_change_password' => false,
        ]);

        DB::table('user_role')->insert([
            'user_id' => $userId,
            'role_id' => 4,
            'assigned_by' => $userId,
            'enable' => 1,
        ]);

        $this->command?->info('Guest account created.');
        $this->command?->info('Username: guest');
        $this->command?->info('Password: Temp12345');
    }
}
