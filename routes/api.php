<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Teacher\AttendanceController;
use App\Http\Controllers\Api\Parent\ParentController;
use App\Http\Controllers\Api\Cpe\JustificationController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\ClassController;
use App\Http\Controllers\Api\Admin\SubjectController;
use App\Http\Controllers\Api\Admin\StudentController;
use App\Http\Controllers\Api\Admin\AssignmentController;
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Controllers\SurveillantController;
use App\Http\Controllers\TeacherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

    // Teacher routes
    Route::middleware(['auth:sanctum','role:teacher'])
        ->prefix('teacher')
        ->group(function () {
            Route::get('/classes', [TeacherController::class, 'classes']);
            Route::get('/classes/{class}/subjects', [TeacherController::class, 'subjectsByClass']);
            Route::get('/classes/{class}/students', [TeacherController::class, 'students']);
            Route::post('/attendance', [AttendanceController::class, 'store']);
            Route::get('/attendance', [AttendanceController::class, 'index']);

        });
    
    // CPE routes
    Route::middleware(['auth:sanctum','role:cpe'])
    ->prefix('cpe')
    ->group(function () {
        Route::get('/justifications', [JustificationController::class, 'index']);
        Route::post('/justifications/{id}/validate', [JustificationController::class, 'validate']);
        Route::get('/schedule', [SurveillantController::class, 'schedule']);
    });

    // Parent routes
    Route::middleware(['auth:sanctum','role:parent'])
    ->prefix('parent')
    ->group(function () {
        Route::get('/attendances', [ParentController::class, 'attendances']);
        Route::post('/justifications', [ParentController::class, 'storeJustification']);
        Route::get('/schedule', [ParentController::class, 'schedule']);
    });

    // Admin routes
    Route::middleware(['auth:sanctum','role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::post('/users', [AdminController::class, 'store']);
        Route::get('/users', [AdminController::class, 'index']);

        // Classes
        Route::get('/classes', [ClassController::class, 'index']);
        Route::post('/classes', [ClassController::class, 'store']);

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);

        // Students
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);

        // Assign teacher
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::get('/assignments', [AssignmentController::class, 'index']);

        // Link parent to student
        Route::post('/parent-student', [AdminController::class, 'linkParentStudent']);
        Route::get('/parent-student', [AdminController::class, 'listOfLinkedParentsAndStudents']);

        // Schedule
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);

    });


    // Student routes
    Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
        Route::get('/schedule', [StudentController::class, 'schedule']);
        Route::get('/attendance', [StudentController::class, 'attendance']);
    });

    Route::get('/schedules/class/{classId}', function ($classId) {
    $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
        ->where('class_id', $classId)
        ->get();
    return response()->json(['success' => true, 'data' => $schedules]);
});