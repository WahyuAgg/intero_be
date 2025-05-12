<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_Invitation;

class InvitationController extends Controller
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

    // Buat undangan
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string',
            'user_id' => 'required|email',
            'role' => 'required|in:STUDENT,TEACHER'
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $invitation = new Google_Service_Classroom_Invitation([
            'courseId' => $request->input('course_id'),
            'userId' => $request->input('user_id'),
            'role' => $request->input('role'),
        ]);

        $created = $classroom->invitations->create($invitation);

        return response()->json($created);
    }

    // Tampilkan semua undangan yang dimiliki user login
    public function index()
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $invitations = $classroom->invitations->listInvitations();

        return response()->json($invitations->getInvitations());
    }

    // Tampilkan undangan tertentu
    public function show($invitationId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $invitation = $classroom->invitations->get($invitationId);

        return response()->json($invitation);
    }

    // Hapus undangan
    public function destroy($invitationId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->invitations->delete($invitationId);
            return response()->json(['message' => 'Invitation deleted']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to delete invitation',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
