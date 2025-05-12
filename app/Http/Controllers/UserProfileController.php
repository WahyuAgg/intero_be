<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;

class UserProfileController extends Controller
{
    private function getGoogleService($user)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->setAccessToken([
            'access_token' => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => 3600,
            'created' => time() - 60,
        ]);

        return new Google_Service_Classroom($client);
    }

    // Ambil profil pengguna berdasarkan userId (email atau Google ID)
    public function show($userId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $profile = $classroom->userProfiles->get($userId);
            return response()->json($profile);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user profile',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
