<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Service_Classroom;
use Google_Client;
use Google_Service_Classroom_ModifyAttachmentsRequest;
use Google_Service_Classroom_Link;
// use Google_Service_Classroom_ModifyAttachmentsRequest;


class SubmissionController extends Controller
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

    /**
     * Get all student submissions for a given course and courseWork
     */
    public function index($courseId, $courseWorkId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $submissions = $classroom->courses_courseWork_studentSubmissions
                ->listCoursesCourseWorkStudentSubmissions($courseId, $courseWorkId);

            return response()->json($submissions->getStudentSubmissions());
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve submissions',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get single student submission (by submissionId)
     */
    public function show($courseId, $courseWorkId, $submissionId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $submission = $classroom->courses_courseWork_studentSubmissions
                ->get($courseId, $courseWorkId, $submissionId);

            return response()->json($submission);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve submission',
                'error' => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Grade a student's submission (assign score)
     */
    public function grade(Request $request, $courseId, $courseWorkId, $submissionId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        $score = $request->input('assignedGrade');
        if ($score === null) {
            return response()->json(['message' => 'assignedGrade is required'], 400);
        }

        try {
            $submission = new \Google_Service_Classroom_StudentSubmission([
                'assignedGrade' => $score
            ]);

            $updated = $classroom->courses_courseWork_studentSubmissions
                ->patch($courseId, $courseWorkId, $submissionId, $submission, ['updateMask' => 'assignedGrade']);

            return response()->json([
                'message' => 'Submission graded successfully',
                'submission' => $updated,
            ]);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to grade submission',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Return (return submission to student)
     */
    public function returnSubmission($courseId, $courseWorkId, $submissionId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_courseWork_studentSubmissions
                ->classroomCoursesCourseWorkStudentSubmissionsReturn($courseId, $courseWorkId, $submissionId);

            return response()->json(['message' => 'Submission returned to student']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to return submission',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark submission as TURNED_IN
     */
    public function turnIn($courseId, $courseWorkId, $submissionId)
    {
        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $classroom->courses_courseWork_studentSubmissions
                ->turnIn($courseId, $courseWorkId, $submissionId);

            return response()->json(['message' => 'Submission marked as TURNED_IN']);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Failed to turn in submission',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function modifyAttachment(Request $request, $courseId, $courseWorkId, $submissionId)
    {
        $request->validate([
            'url' => 'required|url',
            'title' => 'nullable|string',
        ]);

        $user = Auth::user();
        $classroom = $this->getGoogleService($user);

        try {
            $linkAttachment = new \Google_Service_Classroom_Attachment([
                'link' => [
                    'url' => $request->input('url')
                ]
            ]);

            $modifyRequest = new Google_Service_Classroom_ModifyAttachmentsRequest([
                'addAttachments' => [$linkAttachment]
            ]);

            $response = $classroom->courses_courseWork_studentSubmissions
                ->modifyAttachments($courseId, $courseWorkId, $submissionId, $modifyRequest);

            return response()->json([
                'message' => 'Attachment berhasil ditambahkan ke submission.',
                'response' => $response
            ]);
        } catch (\Google_Service_Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan attachment',
                'error' => $e->getMessage()
            ], 400);
        }
    }


}
