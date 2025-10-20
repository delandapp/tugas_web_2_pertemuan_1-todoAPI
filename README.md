# Laporan Proyek REST API Todo – Laravel 12

Proyek ini membangun layanan REST API untuk mengelola data Todo tanpa autentikasi, menerapkan prinsip Clean Architecture pada level aplikasi (Controller → Service → Repository) serta menggunakan resource dan request class untuk menjaga konsistensi data. Seluruh implementasi ditulis dengan Laravel 12 dan basis data MySQL.

## 1. Struktur Proyek

-   `app/Enums/TodoStatus.php` – Enum status todo (`pending`, `in_progress`, `completed`, `archived`).
-   `app/Http/Controllers/TodoController.php` – Controller RESTful yang menerima request HTTP dan memanggil service.
-   `app/Http/Requests/StoreTodoRequest.php` & `UpdateTodoRequest.php` – Validasi input untuk create dan update.
-   `app/Http/Resources/TodoResource.php` – Menstandarkan bentuk respons JSON.
-   `app/Models/Todo.php` – Model Eloquent dengan scope filter, soft delete, dan casting enum.
-   `app/Repositories/TodoRepository.php` – Lapisan akses data (CRUD, pagination, filtering).
-   `app/Services/TodoService.php` – Logika bisnis (sinkronisasi status dan `completed_at`, penentuan UUID, sanitasi `per_page`).
-   `bootstrap/app.php` – Registrasi rute web, api, console, dan health check.
-   `database/migrations/2025_10_13_111119_create_todos_table.php` – Skema tabel `todos` dengan indeks tambahan.
-   `database/factories/TodoFactory.php` – Data contoh untuk pengujian/seeding.
-   `routes/api.php` – Definisi endpoint API versi `v1`.

Struktur direktori lain mengikuti standar Laravel (config, resources, tests, dsb.) dan dapat digunakan tanpa perubahan tambahan.

## 2. Ringkasan Fitur

-   CRUD Todo dengan UUID publik (`uuid`) berbeda dari primary key integer (`id`).
-   Filtering list todo berdasarkan `status`, `priority`, pencarian `search`, dan rentang tanggal jatuh tempo.
-   Penanganan tanggal selesai (`completed_at`) otomatis ketika status berubah ke `completed` atau `archived`.
-   Response JSON rapih dan konsisten melalui `TodoResource`.
-   Tanpa autentikasi/sekuriti sehingga mudah diintegrasikan atau diperluas nantinya.

## 3. Prasyarat Lingkungan

-   PHP >= 8.2 dengan ekstensi `intl`, `mbstring`, `openssl`, `pdo_mysql`.
-   Composer terbaru.
-   Database MySQL/MariaDB.
-   Node.js (opsional, hanya jika ingin menjalankan frontend bawaan Laravel).

## 4. Cara Menjalankan Proyek

1. **Clone dan instal dependensi**
    ```bash
    git clone <repository-url>
    cd Project_Kuliah_Web_2_Pertemuan_1
    composer install
    ```
2. **Konfigurasi lingkungan**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Sesuaikan variabel `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan konfigurasi lain di `.env`.
3. **Migrasi database**
    ```bash
    php artisan migrate
    ```
    Tambahan (opsional): generate data dummy dengan factory
    ```bash
    php artisan tinker
    \App\Models\Todo::factory()->count(10)->create();
    ```
4. **Jalankan server pengembangan**
    ```bash
    php artisan serve
    ```
    API dapat diakses di `http://localhost:8000/api/v1/todos`.
5. **Jalankan test otomatis (opsional)**
    ```bash
    php artisan test
    ```

## 5. Struktur Database

### Tabel `todos`

| Kolom          | Tipe                 | Keterangan                                                |
| -------------- | -------------------- | --------------------------------------------------------- |
| `id`           | BIGINT UNSIGNED      | Primary key auto-increment.                               |
| `uuid`         | CHAR(36)             | UUID publik unik untuk akses API.                         |
| `title`        | VARCHAR(150)         | Judul todo.                                               |
| `description`  | TEXT (nullable)      | Detail tambahan.                                          |
| `status`       | ENUM                 | Nilai: `pending`, `in_progress`, `completed`, `archived`. |
| `priority`     | UNSIGNED TINYINT     | Skala 1–5. Default 3.                                     |
| `due_date`     | DATE (nullable)      | Tenggat pengerjaan.                                       |
| `completed_at` | TIMESTAMP (nullable) | Terisi otomatis saat status selesai/arsip.                |
| `created_at`   | TIMESTAMP            | Timestamp pembuatan.                                      |
| `updated_at`   | TIMESTAMP            | Timestamp pembaruan.                                      |
| `deleted_at`   | TIMESTAMP (nullable) | Soft delete.                                              |

**Indeks**

-   `uuid` unik.
-   Indeks gabungan untuk `status` dan `priority` (mempercepat filter).
-   Indeks tambahan `due_date` untuk pencarian berdasarkan tanggal.

Tabel bawaan Laravel seperti `users`, `cache`, dan `jobs` tetap tersedia tetapi tidak digunakan dalam modul ini.

## 6. Dokumentasi API

### Konvensi Umum

-   Base URL: `http://{host}/api/v1`
-   Semua respons dalam format JSON.
-   Tidak ada autentikasi.
-   Kesalahan validasi mengembalikan status `422` dengan rincian field.
-   Kesalahan data tidak ditemukan mengembalikan `404`.

### 6.1 List Todos

-   **Method & URL**: `GET /todos`
-   **Query Parameters**
    -   `status` (opsional): `pending|in_progress|completed|archived`
    -   `priority` (opsional): angka 1–5
    -   `search` (opsional): kata kunci untuk `title` atau `description`
    -   `due_before` (opsional): `YYYY-MM-DD`
    -   `due_after` (opsional): `YYYY-MM-DD`
    -   `per_page` (opsional): default 15, maksimal 100
-   **Contoh Respons**
    ```json
    {
        "data": [
            {
                "uuid": "1baf1e8a-9f34-4de9-bd8c-7b3b3295d1b0",
                "title": "Siapkan presentasi",
                "description": "Finalisasi slide untuk pertemuan dosen",
                "status": "pending",
                "priority": 3,
                "due_date": "2025-11-01",
                "completed_at": null,
                "created_at": "2025-10-20T11:12:27+00:00",
                "updated_at": "2025-10-20T11:12:27+00:00"
            }
        ],
        "links": {
            "first": "http://localhost:8000/api/v1/todos?page=1",
            "last": "http://localhost:8000/api/v1/todos?page=1",
            "prev": null,
            "next": null
        },
        "meta": {
            "current_page": 1,
            "per_page": 15,
            "total": 1
        },
        "filters": {
            "status": "pending"
        }
    }
    ```

### 6.2 Create Todo

-   **Method & URL**: `POST /todos`
-   **Body (JSON)**
    ```json
    {
        "title": "Menikah",
        "description": "Persiapkan acara",
        "status": "pending",
        "priority": 3,
        "due_date": "2025-12-31"
    }
    ```
-   **Aturan Validasi**
    -   `title`: wajib, string, maks 150 karakter.
    -   `description`: opsional, string.
    -   `status`: opsional, salah satu dari enum.
    -   `priority`: opsional, integer 1–5 (default 3).
-   `due_date`: opsional, tanggal tidak boleh sebelum hari ini.
    -   `completed_at`: opsional, timestamp (biasanya diisi otomatis).
-   **Respons Sukses (`201 Created`)**
    ```json
    {
        "data": {
            "uuid": "5e9425ca-9573-492c-af27-e83f68f58542",
            "title": "Menikah",
            "description": "Persiapkan acara",
            "status": "pending",
            "priority": 3,
            "due_date": "2025-12-31",
            "completed_at": null,
            "created_at": "2025-10-20T11:12:27+00:00",
            "updated_at": "2025-10-20T11:12:27+00:00"
        }
    }
    ```

### 6.3 Detail Todo

-   **Method & URL**: `GET /todos/{uuid}`
-   **Respons Sukses (`200 OK`)**: format sama dengan objek `data` di atas.
-   **Kesalahan**: `404` jika UUID tidak ditemukan.

### 6.4 Update Todo

-   **Method & URL**: `PUT /todos/{uuid}` atau `PATCH /todos/{uuid}`
-   **Body (JSON)**
    ```json
    {
        "status": "completed",
        "priority": 1,
        "completed_at": "2025-10-20T13:45:00+00:00"
    }
    ```
-   **Validasi**: sama dengan create tetapi seluruh field bersifat `sometimes|required`.
-   **Respons**: `200 OK` dengan objek todo terbaru.

### 6.5 Delete Todo

-   **Method & URL**: `DELETE /todos/{uuid}`
-   **Respons**: `204 No Content` jika berhasil. Data hanya di-soft-delete, dapat dipulihkan via query manual jika diperlukan.

### 6.6 Contoh Permintaan dengan cURL

```bash
curl -X POST http://localhost:8000/api/v1/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"Review materi UTS","priority":2,"due_date":"2025-10-30"}'
```

## 7. Strategi Pengujian

-   Gunakan `php artisan test` untuk memastikan fungsionalitas dasar Laravel berjalan.
-   Disarankan menambahkan pengujian feature khusus API Todo (belum disediakan) guna mengunci perilaku service dan repository.

## 8. Rencana Pengembangan Lanjutan

-   Menambahkan autentikasi (Laravel Sanctum atau Passport) jika API dipublikasikan.
-   Menyusun dokumentasi OpenAPI/Swagger otomatis.
-   Menambah kolom `tags` atau relasi ke tabel `users` untuk multi-user.
-   Membuat queue/notification ketika `due_date` mendekat.

---

Dokumen ini disusun sebagai laporan teknis agar pembaca (dosen/penguji) mudah memahami bagaimana proyek diatur, dijalankan, dan divalidasi.
