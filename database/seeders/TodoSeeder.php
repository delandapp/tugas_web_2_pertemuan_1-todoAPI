<?php

namespace Database\Seeders;

use App\Enums\TodoStatus;
use App\Models\Todo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', [
                'mahasiswa@udb.sch.id',
                'mahasiswa1@udb.sch.id',
            ])
            ->get()
            ->keyBy('email');

        $now = CarbonImmutable::now();

        $todos = [
            'mahasiswa@udb.sch.id' => [
                [
                    'title' => 'Menyusun laporan praktikum jaringan komputer',
                    'description' => 'Lengkapi diagram topologi dan analisis packet trace untuk mata kuliah Jaringan Komputer.',
                    'status' => TodoStatus::Pending->value,
                    'priority' => 2,
                    'due_in_days' => 3,
                ],
                [
                    'title' => 'Mempersiapkan presentasi arsitektur cloud',
                    'description' => 'Siapkan slide tentang perbandingan AWS vs GCP untuk tugas Komputasi Awan.',
                    'status' => TodoStatus::InProgress->value,
                    'priority' => 3,
                    'due_in_days' => 5,
                ],
            ],
            'mahasiswa1@udb.sch.id' => [
                [
                    'title' => 'Implementasi modul autentikasi Laravel',
                    'description' => 'Bangun fitur login menggunakan Sanctum untuk proyek akhir mata kuliah Pemrograman Web Lanjut.',
                    'status' => TodoStatus::InProgress->value,
                    'priority' => 1,
                    'due_in_days' => 2,
                ],
                [
                    'title' => 'Riset topik skripsi Kecerdasan Buatan',
                    'description' => 'Kumpulkan minimal lima paper terbaru tentang Computer Vision untuk proposal skripsi.',
                    'status' => TodoStatus::Pending->value,
                    'priority' => 4,
                    'due_in_days' => 7,
                ],
                [
                    'title' => 'Review kode aplikasi mobile kampus',
                    'description' => 'Lakukan code review terhadap modul notifikasi pada proyek kolaborasi Laboratorium Rekayasa Perangkat Lunak.',
                    'status' => TodoStatus::Completed->value,
                    'priority' => 3,
                    'due_in_days' => 1,
                    'completed_offset_days' => 1,
                ],
            ],
        ];

        foreach ($todos as $email => $tasks) {
            $user = $users->get($email);

            if (! $user) {
                continue;
            }

            foreach ($tasks as $task) {
                $status = Arr::get($task, 'status', TodoStatus::Pending->value);
                $dueDate = $now->addDays(Arr::get($task, 'due_in_days', 3));
                $completedAt = null;

                if ($status === TodoStatus::Completed->value) {
                    $completedAt = $now->subDays(
                        Arr::get($task, 'completed_offset_days', 0)
                    );
                }

                Todo::create([
                    'uuid' => (string) Str::uuid(),
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'status' => $status,
                    'priority' => $task['priority'],
                    'due_date' => $dueDate->toDateString(),
                    'completed_at' => $completedAt,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
