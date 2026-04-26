<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Traits\ApiResponse;




class AttendanceController extends Controller
{
    use ApiResponse;
    /**
     * Store or update attendance
     */
    public function store(StoreAttendanceRequest $request)
    {        
        $isAssigned = DB::table('teacher_class_subject')
            ->where('teacher_id', $request->user()->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'message' => 'Forbidden: not assigned to this class/subject'
            ], 403);
        }

        $student = \App\Models\Student::findOrFail($request->student_id);

        if ($student->class_id != $request->class_id) {
            return response()->json([
                'message' => 'Student does not belong to this class'
            ], 400);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'subject_id' => $request->subject_id,
                'date' => $request->date
            ],
            [
                'teacher_id' => $request->user()->id,
                'class_id' => $request->class_id,
                'status' => $request->status
            ]
        );

        return $this->success(
            new AttendanceResource($attendance),
            'Attendance saved'
        );
    }

    /**
     * Get teacher attendance history
     */
    public function index(Request $request)
    {
        $attendances = Attendance::where('teacher_id', $request->user()->id)
            ->with(['student', 'subject'])
            ->latest()
            ->paginate(10);

        return $this->success(
            AttendanceResource::collection($attendances),
            'Attendance list'
        );
    }
}