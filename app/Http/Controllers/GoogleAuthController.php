<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Classroom;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;




class GoogleAuthController extends Controller
{
    /**
     * Initiate Google OAuth login and return the redirect URL.
     */
    public function initiate(Request $request)
    {
        $user = Auth::user(); // Sudah di-auth melalui Sanctum middleware

        // Inisialisasi Google Client
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline'); // Agar bisa dapat refresh_token
        $client->setPrompt('consent');     // Penting agar refresh_token dikirim ulang
        $client->setScopes([
            // Google Classroom Scopes
            'https://www.googleapis.com/auth/classroom.courses',                                // Untuk mengelola kursus
            'https://www.googleapis.com/auth/classroom.rosters',                            // Untuk mengelola siswa dan guru
            'https://www.googleapis.com/auth/classroom.coursework.students',                    // Untuk mengelola tugas siswa
            'https://www.googleapis.com/auth/classroom.coursework.me',                          // Untuk mengelola tugas pengguna yang login
            'https://www.googleapis.com/auth/classroom.student-submissions.students.readonly',       // Untuk mengelola pengumpulan tugas siswa
            'https://www.googleapis.com/auth/classroom.student-submissions.me.readonly',        // Untuk melihat pengumpulan tugas pengguna
            'https://www.googleapis.com/auth/classroom.topics',                             // Untuk mengelola topik kursus
            'https://www.googleapis.com/auth/classroom.announcements',                      // Untuk mengelola pengumuman
            'https://www.googleapis.com/auth/classroom.guardianlinks.students',             // Untuk mengelola wali siswa
            'https://www.googleapis.com/auth/classroom.courseworkmaterials',                // Untuk mengelola file materi tugas
            'https://www.googleapis.com/auth/classroom.push-notifications',                 // Untuk menerima notifikasi push

            // Google Drive Scope
            // 'https://www.googleapis.com/auth/drive',                                        // Untuk mengakses Google Drive

            // Google UserInfo Scopes
            'https://www.googleapis.com/auth/userinfo.profile',                             // Untuk mengakses profil pengguna
            'https://www.googleapis.com/auth/userinfo.email',                               // Untuk mengakses email pengguna
            'openid',                                                                       // Untuk mengakses OpenID Connect
        ]);

        // Tambahkan state unik (opsional, untuk keamanan)
        $state = base64_encode(json_encode([
            'user_id' => $user->id,
            'timestamp' => now()->timestamp
        ]));
        $client->setState($state);

        // Dapatkan URL login Google
        $authUrl = $client->createAuthUrl();

        return response()->json([
            'google_login_url' => $authUrl
        ]);
    }



    public function callback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state'); // jika kamu pakai state

        if (!$code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        try {
            // Tukarkan authorization code dengan access token
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return response()->json(['error' => $token['error']], 400);
            }

            $accessToken = $token['access_token'];
            $refreshToken = $token['refresh_token'] ?? null;
            $expiresIn = $token['expires_in'] ?? 3600;

            // Jika kamu pakai `state` dan menyimpan user_id di sana:
            $user = null;
            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                $user = User::find($decoded['user_id']);
            } else {
                // Kalau tidak pakai `state`, ambil user dari session/login
                $user = Auth::user();
            }

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Simpan token ke database
            $user->google_access_token = $accessToken;
            if ($refreshToken) {
                $user->google_refresh_token = $refreshToken;
            }
            $user->google_token_expires_at = now()->addSeconds($expiresIn);
            $user->save();

            return response()->json(['message' => 'Google account connected successfully']);
        } catch (\Exception $e) {
            Log::error('Google OAuth Callback Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to connect Google account'], 500);
        }
    }


    public function refreshToken($userId)
    {
        $user = User::find($userId);
        $refreshToken = $user->google_refresh_token;

        // Cek jika refresh token kosong atau tidak valid
        if (empty($refreshToken)) {
            return response()->json([
                'message' => 'Refresh token tidak valid atau tidak ditemukan',
            ], 400);
        }

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->setAccessType('offline');
        $client->setScopes([
            // Google Classroom Scopes
            'https://www.googleapis.com/auth/classroom.courses',
            'https://www.googleapis.com/auth/classroom.rosters',
            'https://www.googleapis.com/auth/classroom.coursework.students',
            'https://www.googleapis.com/auth/classroom.coursework.me',
            'https://www.googleapis.com/auth/classroom.student-submissions.students.readonly',
            'https://www.googleapis.com/auth/classroom.student-submissions.me.readonly',
            'https://www.googleapis.com/auth/classroom.topics',
            'https://www.googleapis.com/auth/classroom.announcements',
            'https://www.googleapis.com/auth/classroom.guardianlinks.students',
            'https://www.googleapis.com/auth/classroom.courseworkmaterials',
            'https://www.googleapis.com/auth/classroom.push-notifications',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email',
            'openid',
        ]);

        // Set access_token semestara dengan refresh token
        $client->setAccessToken(['access_token' => $refreshToken]);

        try {
            // Periksa apakah token sudah expired
            if ($client->isAccessTokenExpired()) {
                // Gunakan refresh token untuk mendapatkan access token baru
                $newAccessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (isset($newAccessToken['error'])) {
                    return response()->json([
                        'message' => 'Unable to refresh token',
                        'error' => $newAccessToken['error']
                    ], 400);
                }

                // Simpan token baru ke database
                $accessToken = $newAccessToken['access_token'];
                $expiresIn = $newAccessToken['expires_in'] ?? 3600;  // default 1 jam jika tidak ada

                $user->google_access_token = $accessToken;
                $user->google_token_expires_at = now()->addSeconds($expiresIn);
                $user->save();

                return response()->json([
                    'access_token' => $accessToken,
                    'expires_in' => $expiresIn,
                    'message' => 'Access token refreshed successfully'
                ]);
            }

            return response()->json([
                'access_token' => $client->getAccessToken(),
                'message' => 'Token masih valid, tidak perlu refresh'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while refreshing token',
                'error' => $e->getMessage()
            ], 500);
        }
    }




}

