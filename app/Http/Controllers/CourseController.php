<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Google_Client;
use Google_Service_Classroom;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
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

    public function index()
    {
        $user = Auth::user();

        $classroom = $this->getGoogleService($user);

        $courses = $classroom->courses->listCourses();

        return response()->json($courses->getCourses());
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $courseData = new \Google_Service_Classroom_Course([
            'name' => $request->input('name'),
            'section' => $request->input('section'),
            'descriptionHeading' => $request->input('description_heading'),
            'description' => $request->input('description'),
            'room' => $request->input('room'),
            'ownerId' =>  $user->email
        ]);

        $course = $classroom->courses->create($courseData);

        return response()->json($course);
    }

    public function show($id)
    {
        $user = Auth::user();

        $classroom = $this->getGoogleService($user);

        $course = $classroom->courses->get($id);

        return response()->json($course);
    }

    public function update($id, Request $request)
    {
        $user = Auth::user();

        $classroom = $this->getGoogleService($user);

        $fields = ['name', 'section', 'descriptionHeading', 'description', 'room'];
        $data = [];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $data[$field] = $request->input($field);
            }
        }

        if (empty($data)) {
            return response()->json(['message' => 'No fields provided to update'], 400);
        }

        $coursePatch = new \Google_Service_Classroom_Course($data);
        $updateMask = implode(',', array_keys($data));

        $updatedCourse = $classroom->courses->patch($id, $coursePatch, ['updateMask' => $updateMask]);

        return response()->json($updatedCourse);
    }


    public function destroy($id)
    {
        $user = Auth::user();

        $classroom = $this->getGoogleService($user);

        try {
            // 1. Archive course terlebih dahulu
            $archiveRequest = new \Google_Service_Classroom_Course(['courseState' => 'ARCHIVED']);
            $classroom->courses->patch($id, $archiveRequest, ['updateMask' => 'courseState']);

            // 2. Delete course
            $classroom->courses->delete($id);

            return response()->json(['message' => 'Course archived and deleted successfully']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to delete course',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

}
