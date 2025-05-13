<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_Student;

class StudentController extends Controller
{
    private function getGoogleService($user)
    {

        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setAccessToken([
            'access_token' => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => 3600,
            'created' => time() - 60
        ]);

        return new Google_Service_Classroom($client);
    }

    // List semua siswa dalam course
    public function index($courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $students = $classroom->courses_students->listCoursesStudents($courseId);

        return response()->json($students->getStudents());
    }

    // Get detail satu siswa
    public function show($courseId, $userId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $student = $classroom->courses_students->get($courseId, $userId);

        return response()->json($student);
    }

    // Tambah siswa ke course
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'userId' => 'required|string', // bisa email atau userId
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $student = new Google_Service_Classroom_Student([
            'userId' => $request->input('userId')
        ]);

        $added = $classroom->courses_students->create($courseId, $student);

        return response()->json($added);
    }

    // Hapus siswa dari course
    public function destroy($courseId, $userId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_students->delete($courseId, $userId);
            return response()->json(['message' => 'Student removed']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to remove student',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
