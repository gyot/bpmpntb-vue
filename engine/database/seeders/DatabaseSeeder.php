<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Settings;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@bpmpntb.id'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'id_seksi' => null, 'role' => 'admin']
        );

        Settings::firstOrCreate(
            ['id' => 1],
            ['title' => 'BPMP Provinsi NTB', 'description' => 'Balai Penjaminan Mutu Pendidikan Provinsi NTB', 'alamat' => 'Mataram, NTB']
        );
    }
}
