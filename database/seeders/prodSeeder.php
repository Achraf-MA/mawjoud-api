<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            User::firstOrCreate(
                ['email' => 'sunless@test.com'],
                [
                    'role' => 'admin',
                    'first_name' => 'Admin',
                    'last_name' => 'Test',
                    'password' => Hash::make('password'),
                ]
            );
        });
    }
}
