# Intero Backend - Google Classroom API Wrapper

Proyek ini adalah **API backend** berbasis **Laravel** yang berfungsi sebagai **wrapper untuk Google Classroom API**, menyederhanakan proses integrasi dan komunikasi antara aplikasi frontend dengan layanan Google Classroom.

## 📌 Fitur Utama

- 🔐 Autentikasi menggunakan **Google OAuth 2.0**
- 📚 Manajemen **Courses**
- 📢 Manajemen **Announcements**
- 📝 Manajemen **CourseWork**
- 📄 Manajemen **Student Submissions**
- ✍️ Manajemen **Penilaian**

## 📦 Teknologi yang Digunakan

- Laravel Framework 11.44.7
- PHP 8.2.12 (cli)
- Google API Client for PHP
- MySQL / MariaDB
- Laravel Sanctum

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

3. **Salin File `.env` dan Konfigurasi**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database dan Google API**
   Atur file `.env` Anda seperti berikut:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/api/google/callback
   ```

5. **Migrasi Database**
   ```bash
   php artisan migrate
   ```

6. **Jalankan Server**
   ```bash
   php artisan serve
   ```

## 🔑 Autentikasi Google OAuth

Frontend perlu mengarahkan pengguna ke:
```
GET /api/google/login
```

Setelah login, pengguna akan diarahkan kembali melalui callback:
```
GET /api/google/callback
```

Token yang diperoleh digunakan untuk mengakses endpoint Google Classroom melalui API wrapper ini.

## 📁 Struktur Endpoint

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/google/login` | GET | Redirect ke halaman login Google |
| `/api/google/callback` | GET | Callback dari Google |
| `/api/courses` | GET | Ambil semua course milik user |
| `/api/courses/{id}/announcements` | GET | Ambil pengumuman dari course |
| `/api/courses/{id}/courseWork` | GET | Ambil daftar tugas dari course |
| `/api/courses/{courseId}/courseWork/{workId}/submissions` | GET | Ambil submissions dari suatu tugas |

> ⚠️ Beberapa endpoint memerlukan token Google OAuth yang valid.

## ✅ Status

🚧 **Sedang Dalam Pengembangan**
Pull Request dan kontribusi sangat terbuka.

## 🤝 Kontribusi

1. Fork repo ini
2. Buat branch fitur: `git checkout -b fitur-baru`
3. Commit perubahan: `git commit -m 'Tambah fitur baru'`
4. Push ke branch: `git push origin fitur-baru`
5. Buat Pull Request

## 📄 Lisensi

Proyek ini berada di bawah lisensi [MIT](LICENSE).




## akun pemilik proyek

### pemilik proyek

email : theprocrastinatorman@gmail.com

password google :   andromeda445

### Sample Teacher

email : theprocrastinatorman@gmail.com

password google :   andromeda445
password lms    :   lavachicken

## Sample Student

email : ibnumknd@gmail.com

password google :   exagon.enter
password lms    :   lavachicken



## Tutorial install API Backend

- git clone
- composer install



## Tutorial Coonect ke Google Classsroom API

- register
    /api/auth/register

- login
    /api/auth/login -- pake username, password
    -- dapat bearer token dari BE

- connect ke google
    /api/google/initiate -- pake bearer token

    -- dapat google_login_url dari BE
    -- google_login_url : https://accounts.google.com/..........

- login ke google
    buka/redirect ke link google_login_url yg didapatkan dari BE

    -- cek perijinan, harus setuju

    -- re-open FE page / redirect ke FE

- API bisa digunakan
    -- setiap request harus menggunakan bearer token


## alur request

- api_token (bearer token) =
user api token untuk autentikasi ke sistem lms menggunakan auth sanctum bearer token


- google_token =
meliputi (google_access_token, google_refrsh_token, google_token_expires_at)
token yang diperoleh dari google, untuk otentikasi dan login ke user's google_account.

	  api_token		   google_token
fe >------------> be >--------------> google


