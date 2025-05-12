<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_Teacher;

class TeacherController extends Controller
{
    private function getGoogleService($user)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->setAccessToken([
            'access_token' => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => 3600,
            'created' => time() - 60
        ]);

        return new Google_Service_Classroom($client);
    }

    // List semua guru di dalam course
    public function index($courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $teachers = $classroom->courses_teachers->listCoursesTeachers($courseId);

        return response()->json($teachers->getTeachers());
    }

    // Get detail guru tertentu
    public function show($courseId, $userId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $teacher = $classroom->courses_teachers->get($courseId, $userId);

        return response()->json($teacher);
    }

    // Tambahkan guru ke course
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'userId' => 'required|string', // email atau userId
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $teacher = new Google_Service_Classroom_Teacher([
            'userId' => $request->input('userId'),
        ]);

        $created = $classroom->courses_teachers->create($courseId, $teacher);

        return response()->json($created);
    }

    // Hapus guru dari course
    public function destroy($courseId, $userId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_teachers->delete($courseId, $userId);
            return response()->json(['message' => 'Teacher removed']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to remove teacher',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
