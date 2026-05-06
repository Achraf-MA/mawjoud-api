<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Teacher\AttendanceController;
use App\Http\Controllers\Api\Parent\ParentController;
use App\Http\Controllers\Api\Cpe\JustificationController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\ClassController;
use App\Http\Controllers\Api\Admin\SubjectController;
use App\Http\Controllers\Api\Admin\AssignmentController;
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Controllers\Api\Student\StudentController as StudentApiController;
use App\Http\Controllers\SurveillantController;
use App\Http\Controllers\TeacherController;

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Auth (protected) ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
});

// ── Teacher ───────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:teacher'])->prefix('teacher')->group(function () {
    Route::get('/classes',                      [TeacherController::class,    'classes']);
    Route::get('/classes/{class}/subjects',     [TeacherController::class,    'subjectsByClass']);
    Route::get('/classes/{class}/students',     [TeacherController::class,    'students']);
    Route::get('/schedule',                     [TeacherController::class,    'schedule']);
    Route::post('/attendance',                  [AttendanceController::class, 'store']);
    Route::get('/attendance',                   [AttendanceController::class, 'index']);
});

// ── CPE (Surveillant) ─────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:cpe'])->prefix('cpe')->group(function () {
    Route::get('/justifications',               [JustificationController::class, 'index']);
    Route::post('/justifications/{id}/validate',[JustificationController::class, 'validate']);
    Route::get('/schedule',                     [SurveillantController::class,   'schedule']);
});

// ── Parent ────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:parent'])->prefix('parent')->group(function () {
    Route::get('/attendances',      [ParentController::class, 'attendances']);
    Route::post('/justifications',  [ParentController::class, 'storeJustification']);
    Route::get('/schedule',         [ParentController::class, 'schedule']);
});

// ── Student ───────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::get('/schedule',   [StudentApiController::class, 'schedule']);
    Route::get('/attendance', [StudentApiController::class, 'attendance']);
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    // Users
    Route::get('/users',          [AdminController::class, 'index']);
    Route::post('/users',         [AdminController::class, 'store']);
    Route::put('/users/{user}',   [AdminController::class, 'update']);
    Route::delete('/users/{user}',[AdminController::class, 'destroy']);

    // Classes
    Route::get('/classes',              [ClassController::class, 'index']);
    Route::post('/classes',             [ClassController::class, 'store']);
    Route::put('/classes/{class}',      [ClassController::class, 'update']);
    Route::delete('/classes/{class}',   [ClassController::class, 'destroy']);

    // Subjects
    Route::get('/subjects',             [SubjectController::class, 'index']);
    Route::post('/subjects',            [SubjectController::class, 'store']);
    Route::put('/subjects/{subject}',   [SubjectController::class, 'update']);
    Route::delete('/subjects/{subject}',[SubjectController::class, 'destroy']);

    // Teacher assignments
    Route::get('/assignments',              [AssignmentController::class, 'index']);
    Route::post('/assignments',             [AssignmentController::class, 'store']);
    Route::delete('/assignments/{id}',      [AssignmentController::class, 'destroy']);

    // Parent–student links
    Route::get('/parent-student',           [AdminController::class, 'listOfLinkedParentsAndStudents']);
    Route::post('/parent-student',          [AdminController::class, 'linkParentStudent']);
    Route::delete('/parent-student',        [AdminController::class, 'unlinkParentStudent']);

    // Schedules
    Route::get('/schedules',                [ScheduleController::class, 'index']);
    Route::post('/schedules',               [ScheduleController::class, 'store']);
    Route::delete('/schedules/{schedule}',  [ScheduleController::class, 'destroy']);
});
