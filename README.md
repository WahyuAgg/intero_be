# Back End - Google Classroom API Integration

Proyek ini adalah **API backend** berbasis **Laravel** yang berfungsi sebagai **wrapper untuk Google Classroom API**, menyederhanakan proses integrasi dan komunikasi antara aplikasi frontend dengan layanan Google Classroom.

## 📌 Fitur Utama

- Autentikasi menggunakan **Google OAuth 2.0**
- Manajemen **Courses**
- Manajemen **CourseWork**
- Manajemen **Student Submissions**
- Manajemen **Penilaian**


## 📦 Teknologi yang Digunakan

- Laravel Framework 11.44.7
- PHP 8.2.12
- Google API Client for PHP 2.15.4
- MySQL / MariaDB 8.0.3.0
- Laravel Sanctum 4.0

## ⚙️ Instalasi

1. **Clone Repository**

   ```bash
   git clone https://github.com/WahyuAgg/intero_be.git
   cd intero_be
   ```

2. **Instalasi Dependency**

   ```bash
   composer install
   ```

3. **Download file `.env`**


   Download file .env dari link berikut:


    [DOWNLOAD .env](https://drive.google.com/file/d/1X77bEQ9moUVcshoPAGFjtpTTdpApM_R0/view?usp=sharing)
    

   masukkan file .env ke folder project Back End di `intero_be/`

   Jalankan perintah berikut:
   ```bash
   php artisan key:generate
   ```

4. **Konfigurasi Database**
   Atur file `.env` seperti berikut:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE= <your_database>
   DB_USERNAME= <your_username>
   DB_PASSWORD= <your_password>
   ```

5. **Konfigurasi .env APP_URL**

    ```bash
    APP_URL=http://127.0.0.1:8000    
    ```

    APP_URL bisa dikonfigurasikan sesuai dengan domain / IP dimana project dijalankan


6. **Migrasi Database**

   ```bash
   php artisan migrate:fresh
   ```

7. **Jalankan Server**

   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

## 🤵 Sampel akun (untuk testing)

### Akun pemilik proyek / Sample Teacher

- **email** : [`theprocrastinatorman@gmail.com`](theprocrastinatorman@gmail.com)
- **password google account** :   `andromeda445`
- **default password lms**   :   `lavachicken`


### Akun Sample Student

- **email** : [`ibnumknd@gmail.com`](ibnumknd@gmail.com)
- **password google account** :   `exagon.enter`
- **default password lms**   :   `lavachicken`

### Keterangan Password

- **password lms** adalah password untuk akun LMS yang dibuat ketika register, bisa disesuaikan ketika melakukan regiter melalui LMS dan berlaku untuk login melalui LMS.

- **password google account** adalah password akun google, yaitu akun yang terdaftar di dengan google bukan LMS.

## 🔑 Autentikasi Google OAuth dan Koneksi ke Google Classsroom

> ⚠️ Register dan Autentikasi hanya bisa menggunakan **Sampel akun** yang sudah terdaftar di proyek **google cloud console**

> ℹ️ Untuk melakukan testing menggunakan akun lain, daftarkan akun kedalam proyek Classroom API di **Google cloud console**

> 🌤️ Masuk ke google cloud console bisa menggunakan **Akun pemilik proyek**


1. 📝 Register (Jika belum punya akun)
    **endpoint:** `/api/auth/register`
    *membuat password lms*
    **return** Bearer Token.
    <br>

2. ✔️ Inisiasi koneksi ke akun google
    **endpoint:** `/api/google/initiate`
    **parameter**: Bearer Token.
    **return** *google_login_url* : https://accounts.google.com/....
    <br>

3. 📶 Sambungkan akun LMS dengan akun google<br>
    a. Buka *google_login_url* ayng sudah didapatkan sebelumnya
    *google_login_url* : https://accounts.google.com/....<br>
    b. Sambungkan dengan akun google berikut
        Autentikasi OAuth menggunakan google account terkait:
    - Untuk Teacher (guru)

        - email : [`theprocrastinatorman@gmail.com`](theprocrastinatorman@gmail.com)

        - password google account :   `andromeda445`

    - Untuk student (siswa)

        - email : [`ibnumknd@gmail.com`](ibnumknd@gmail.com)

        - password google account :   `exagon.enter`
    ,

4. Akses API
    - **Bearer Token** harus digunakan ntuk setiap kali mengakses endpoint API.

Token yang diperoleh digunakan untuk mengakses endpoint Google Classroom melalui API wrapper ini.

## 📁 Struktur Endpoint

### 🔐 Auth

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/auth/register` | POST | Register user baru |
| `/api/auth/login` | POST | Login user dan dapatkan token |
| `/api/auth/logout` | POST | Logout dan cabut token |

### 👤 User (LMS)

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/user` | GET | Data user yang sedang login |
| `/api/user` | POST | Tambah user baru (testing only) |
| `/api/user/{id}` | GET | Lihat detail user (testing only) |
| `/api/user/{id}` | PUT | Update user (testing only) |
| `/api/user/{id}` | DELETE | Hapus user (testing only) |
| `/api/user/email/{email}` | GET | Cari user berdasarkan email (testing only) |

### 👤 User (Google Classroom)

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/user-profiles/{userId}` | GET | Lihat profil user berdasarkan ID |

### 🔑 Google Auth

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/google/initiate` | GET | Redirect ke halaman login Google |
| `/api/google/callback` | GET | Callback dari Google |
| `/api/google/refresh-token/{userId}` | GET | Refresh token Google user |

### 📚 Courses

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/courses` | GET | Ambil semua course |
| `/api/courses` | POST | Tambah course baru |
| `/api/courses/{id}` | GET | Lihat detail course |
| `/api/courses/{id}` | PUT | Update course |
| `/api/courses/{id}` | DELETE | Hapus course |

### 📢 Announcements

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/courses/{courseId}/announcements` | GET | Ambil pengumuman dari course |
| `/api/courses/{courseId}/announcements` | POST | Tambah pengumuman ke course |

### 📝 Course Work

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/courses/{courseId}/coursework` | GET | Ambil daftar tugas dari course |
| `/api/courses/{courseId}/coursework` | POST | Tambah tugas baru |
| `/api/courses/{courseId}/coursework/{courseWorkId}` | GET | Lihat detail tugas |
| `/api/courses/{courseId}/coursework/{courseWorkId}` | DELETE | Hapus tugas |

### 📤 Submissions

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions` | GET | Ambil semua submission dari tugas |
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions/{submissionId}` | GET | Lihat detail submission |
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions/{submissionId}/grade` | POST | Beri nilai pada submission |
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions/{submissionId}/return` | GET | Kembalikan submission ke siswa |
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions/{submissionId}/turnin` | POST | Siswa menyerahkan tugas |
| `/api/courses/{courseId}/coursework/{courseWorkId}/submissions/{submissionId}/attachments` | POST | Tambah/ubah lampiran submission |

### 📦 Materials

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/classroom/courses/{courseId}/materials` | GET | Ambil semua materi course |
| `/api/classroom/courses/{courseId}/materials` | POST | Tambah materi baru |
| `/api/classroom/courses/{courseId}/materials/{materialId}` | GET | Lihat detail materi |
| `/api/classroom/courses/{courseId}/materials/{materialId}` | PUT | Update materi |
| `/api/classroom/courses/{courseId}/materials/{materialId}` | DELETE | Hapus materi |

### 🎯 Topics

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/topics/{courseId}` | GET | Ambil semua topik dalam course |
| `/api/topics/{courseId}` | POST | Tambah topik baru |
| `/api/topics/{courseId}/{topicId}` | GET | Lihat detail topik |
| `/api/topics/{courseId}/{topicId}` | PUT | Update topik |
| `/api/topics/{courseId}/{topicId}` | DELETE | Hapus topik |

### 👨‍🎓 Students

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/students/{courseId}` | GET | Ambil semua siswa dalam course |
| `/api/students/{courseId}` | POST | Tambah siswa ke course |
| `/api/students/{courseId}/{userId}` | GET | Lihat detail siswa |
| `/api/students/{courseId}/{userId}` | DELETE | Hapus siswa dari course |

### 👩‍🏫 Teachers

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/teachers/{courseId}` | GET | Ambil semua guru dalam course |
| `/api/teachers/{courseId}` | POST | Tambah guru ke course |
| `/api/teachers/{courseId}/{userId}` | GET | Lihat detail guru |
| `/api/teachers/{courseId}/{userId}` | DELETE | Hapus guru dari course |

### ✉️ Invitations

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/invitations` | POST | Kirim undangan ke user |

> ⚠️ Beberapa endpoint memerlukan token Google OAuth yang valid.

## ✅ Status

🚧 **Selesai Pengembangan**
Pull Request dan kontribusi sangat terbuka.

## 🤝 Kontribusi

1. Fork repo ini
2. Buat branch fitur: `git checkout -b fitur-baru`
3. Commit perubahan: `git commit -m 'Tambah fitur baru'`
4. Push ke branch: `git push origin fitur-baru`
5. Buat Pull Request
