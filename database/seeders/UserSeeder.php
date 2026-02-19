<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'CEO SIManajemen',
            'email' => 'ceo@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CEO,
        ]);

        User::create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_DIRECTOR,
        ]);

        User::create([
            'name' => 'Manajer Operasional',
            'email' => 'manajer@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAJER,
        ]);

        User::create([
            'name' => 'Supervisor Area',
            'email' => 'supervisor@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
        ]);

        User::create([
            'name' => 'Staff Administrasi',
            'email' => 'staff@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAFF,
        ]);

        User::create([
            'name' => 'Karyawan 1',
            'email' => 'karyawan@simanajemen.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAFF,
        ]);
    }
}
