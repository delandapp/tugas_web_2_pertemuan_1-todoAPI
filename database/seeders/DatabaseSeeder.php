<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->call([
            TodoSeeder::class,
        ]);
    }

    private function seedUsers(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Akademik',
            'email' => 'admin@udb.sch.id',
        ]);

        User::factory()->create([
            'name' => 'Mahasiswa Informatika A',
            'email' => 'mahasiswa@udb.sch.id',
        ]);

        User::factory()->create([
            'name' => 'Mahasiswa Informatika B',
            'email' => 'mahasiswa1@udb.sch.id',
        ]);
    }
}
