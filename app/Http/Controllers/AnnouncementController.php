<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_Invitation;
use Google_Service_Classroom_Announcement;


class AnnouncementController extends Controller
{
    private function getGoogleService($user)
    {
        $client = new \Google_Client();
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

        return new \Google_Service_Classroom($client);
    }

    public function index($courseId)
    {
        $user = Auth::user(); 
        $service = $this->getGoogleService($user);

        try {
            $announcements = $service->courses_announcements->listCoursesAnnouncements($courseId);
            $data = [];
        foreach ($announcements->getAnnouncements() as $announcement) {
            $data[] = [
                'id' => $announcement->getId(),
                'text' => $announcement->getText()
            ];
        }
            return response()->json($announcements);
        } catch (\Google_Service_Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
    
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $user = Auth::user();
        $service = $this->getGoogleService($user);

        try {
            $announcement = new Google_Service_Classroom_Announcement([
                'text' => $request->text
            ]);

            $created = $service->courses_announcements->create($courseId, $announcement);

            return response()->json([
                'message' => 'Announcement berhasil dibuat.',
                'announcement' => $created
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat announcement.',
                'message' => $e->getMessage()
            ], 500);
        } 
    }
    public function destroy(Request $request, $courseId, $announcementId)
{
    $user = Auth::user();
    $service = $this->getGoogleService($user);

    try {
        $service->courses_announcements->delete($courseId, $announcementId);
        return response()->json(['message' => 'Announcement deleted successfully.']);
    } catch (\Google_Service_Exception $e) {
        return response()->json(['error' => $e->getMessage()], $e->getCode());
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong.'], 500);
    }
}

}
