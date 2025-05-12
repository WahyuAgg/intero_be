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



