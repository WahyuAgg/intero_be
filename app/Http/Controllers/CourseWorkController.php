<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_CourseWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseWorkController extends Controller
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

    public function index($courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $courseWork = $classroom->courses_courseWork->listCoursesCourseWork($courseId);
            return response()->json($courseWork->getCourseWork());
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch coursework',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request, $courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $data = new Google_Service_Classroom_CourseWork([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'workType' => 'ASSIGNMENT',
            'state' => 'PUBLISHED',
            'maxPoints' => $request->input('max_points', 100),
            'dueDate' => [
                'year' => $request->input('due_year'),
                'month' => $request->input('due_month'),
                'day' => $request->input('due_day'),
            ],
            'dueTime' => [
                'hours' => $request->input('due_hour', 23),
                'minutes' => $request->input('due_minute', 59),
            ],
            'submissionModificationMode' => 'MODIFIABLE_UNTIL_TURNED_IN'
        ]);

        try {
            $courseWork = $classroom->courses_courseWork->create($courseId, $data);
            return response()->json($courseWork);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to create coursework',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show($courseId, $courseWorkId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $courseWork = $classroom->courses_courseWork->get($courseId, $courseWorkId);
            return response()->json($courseWork);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch coursework detail',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy($courseId, $courseWorkId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_courseWork->delete($courseId, $courseWorkId);
            return response()->json(['message' => 'CourseWork deleted']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to delete coursework',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
