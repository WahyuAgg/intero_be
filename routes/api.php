<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CourseWorkController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| Sanctum default Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| Course Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'store']);
    Route::get('{id}', [CourseController::class, 'show']);
    Route::put('{id}', [CourseController::class, 'update']);
    Route::delete('{id}', [CourseController::class, 'destroy']);
});






/*
|--------------------------------------------------------------------------
| Cource Works Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('courses/{courseId}/coursework')->group(function () {
    Route::get('/', [CourseWorkController::class, 'index']);
    Route::post('/', [CourseWorkController::class, 'store']);
    Route::get('{courseWorkId}', [CourseWorkController::class, 'show']);
    Route::delete('{courseWorkId}', [CourseWorkController::class, 'destroy']);
});



/*
|--------------------------------------------------------------------------
| Submissions Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('courses/{courseId}/coursework/{courseWorkId}/submissions')->group(function () {
    Route::get('/', [SubmissionController::class, 'index']);
    Route::get('/{submissionId}', [SubmissionController::class, 'show']);
    Route::post('/{submissionId}/grade', [SubmissionController::class, 'grade']);
    Route::get('/{submissionId}/return', [SubmissionController::class, 'returnSubmission']);
    Route::post('/{submissionId}/turnin', [SubmissionController::class, 'turnIn']);
    Route::post('/{submissionId}/attachments', [SubmissionController::class, 'modifyAttachment']);
});



/*
|--------------------------------------------------------------------------
| Material Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('classroom/courses/{courseId}/materials')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
    Route::post('/', [MaterialController::class, 'store']);
    Route::get('/{materialId}', [MaterialController::class, 'show']);
    Route::put('/{materialId}', [MaterialController::class, 'update']);
    Route::delete('/{materialId}', [MaterialController::class, 'destroy']);
});





/*
|--------------------------------------------------------------------------
| Students Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('students')->group(function () {
    Route::get('{courseId}', [StudentController::class, 'index']);
    Route::get('{courseId}/{userId}', [StudentController::class, 'show']);
    Route::post('{courseId}', [StudentController::class, 'store']);
    Route::delete('{courseId}/{userId}', [StudentController::class, 'destroy']);
});



/*
|--------------------------------------------------------------------------
| Teachers Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('teachers')->group(function () {
    Route::get('{courseId}', [TeacherController::class, 'index']);
    Route::get('{courseId}/{userId}', [TeacherController::class, 'show']);
    Route::post('{courseId}', [TeacherController::class, 'store']);
    Route::delete('{courseId}/{userId}', [TeacherController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Invitation Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('invitations')->group(function () {
    Route::post('/', [InvitationController::class, 'store']);
});




/*
|--------------------------------------------------------------------------
| User Profiles Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user-profiles/{userId}', [UserProfileController::class, 'show']);



/*
|--------------------------------------------------------------------------
| LMS Auth Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

});



/*
|--------------------------------------------------------------------------
| Google Auth Routes
|--------------------------------------------------------------------------
*/

Route::prefix('google')->group(function () {
    Route::middleware('auth:sanctum')->get('/initiate', [GoogleAuthController::class, 'initiate']);
    Route::get('/callback', [GoogleAuthController::class,'callback']);
    Route::get('/refresh-token/{userId}', [GoogleAuthController::class,'refreshToken']);
});

/*
|--------------------------------------------------------------------------
| Topics Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('topics')->group(function () {
    Route::get('{courseId}', [TopicController::class, 'index']);
    Route::get('{courseId}/{topicId}', [TopicController::class, 'show']);
    Route::post('{courseId}', [TopicController::class, 'store']);
    Route::put('{courseId}/{topicId}', [TopicController::class, 'update']);
    Route::delete('{courseId}/{topicId}', [TopicController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Announcement Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('courses/{courseId}/announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index']);
    Route::post('/', [AnnouncementController::class, 'store']);
});


/*
|--------------------------------------------------------------------------
| User Management Routes For testing only
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\UserController;

Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);

    // Tambahan: Get user by email
    Route::get('email/{email}', [UserController::class, 'findByEmail']);
});


// /*
// |--------------------------------------------------------------------------
// | LMS Testing Routes
// |--------------------------------------------------------------------------
// */

// Route::prefix('test')->group(function () {
//     Route::middleware('auth:sanctum')->post('/initiate', [GoogleAuthController::class, 'initiate']);
//     Route::post('/callback', [GoogleAuthController::class, 'callback']);
// });

// Route::middleware('auth:sanctum')->get('/test_sanctum', function () {
//     return response()->json(['message' => 'API jalan!']);
// });

// Route::middleware('auth:sanctum')->get('/test/get-refresh-token', function () {
//     $user = Auth::user();

//     return response()->json([
//         'refresh_token' => $user->google_refresh_token
//     ]);
// });

// Route::get('/', function () {
//     return view('welcome');
// });


