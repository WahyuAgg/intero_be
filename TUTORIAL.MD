## akun pemilik proyek

### pemilik proyek
theprocrastinatorman@gmail.com

password google :   andromeda445

### Teacher
theprocrastinatorman@gmail.com

password google :   andromeda445
password lms    :   lavachicken

## Student
ibnumknd@gmail.com

password google :   exagon.enter
password lms    :   lavachicken



## Tutorial install BE
- git clone
- composer install
-


## Tutorial Coonect ke API

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


