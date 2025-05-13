<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_Topic;

class TopicController extends Controller
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

    // List semua topic di dalam course
    public function index($courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $topics = $classroom->courses_topics->listCoursesTopics($courseId);

        return response()->json($topics->getTopic());
    }

    // Tampilkan satu topik
    public function show($courseId, $topicId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $topic = $classroom->courses_topics->get($courseId, $topicId);

        return response()->json($topic);
    }

    // Tambahkan topic
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $topic = new Google_Service_Classroom_Topic([
            'name' => $request->input('name'),
        ]);

        $created = $classroom->courses_topics->create($courseId, $topic);

        return response()->json($created);
    }

    // Update topic
    public function update(Request $request, $courseId, $topicId)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $topic = new Google_Service_Classroom_Topic([
            'name' => $request->input('name'),
        ]);

        $updated = $classroom->courses_topics->patch($courseId, $topicId, $topic, [
            'updateMask' => 'name',
        ]);

        return response()->json($updated);
    }

    // Hapus topic
    public function destroy($courseId, $topicId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_topics->delete($courseId, $topicId);
            return response()->json(['message' => 'Topic deleted']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to delete topic',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
