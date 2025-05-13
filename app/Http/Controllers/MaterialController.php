<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Classroom;
use Google_Service_Classroom_CourseWorkMaterial;

class MaterialController extends Controller
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

        $materials = $classroom->courses_courseWorkMaterials->listCoursesCourseWorkMaterials($courseId);

        return response()->json($materials->getCourseWorkMaterial());
    }

    public function store(Request $request, $courseId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $material = new Google_Service_Classroom_CourseWorkMaterial([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'materials' => [] // kosong dulu, attachment kita pending
        ]);

        $created = $classroom->courses_courseWorkMaterials->create($courseId, $material);

        return response()->json($created);
    }

    public function show($courseId, $materialId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $material = $classroom->courses_courseWorkMaterials->get($courseId, $materialId);

        return response()->json($material);
    }

    public function update(Request $request, $courseId, $materialId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        // Ambil data yang diupdate
        $data = [];
        if ($request->has('title')) {
            $data['title'] = $request->input('title');
        }
        if ($request->has('description')) {
            $data['description'] = $request->input('description');
        }

        // Tidak ada attachment dulu
        if (empty($data)) {
            return response()->json(['message' => 'No fields provided to update'], 400);
        }

        // Lakukan update
        $materialPatch = new Google_Service_Classroom_CourseWorkMaterial($data);
        $updatedMaterial = $classroom->courses_courseWorkMaterials->patch($courseId, $materialId, $materialPatch);

        return response()->json($updatedMaterial);
    }

    public function destroy($courseId, $materialId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_courseWorkMaterials->delete($courseId, $materialId);
            return response()->json(['message' => 'Material deleted']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to delete material',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
