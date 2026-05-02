<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Traits\ApiResponse;

class StudentController extends Controller
{
    use ApiResponse;

    public function schedule()
    {
        $student = auth()->user()->student;

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->get()
            ->groupBy('day');

        return $this->success($schedules);
    }

    public function attendance()
    {
        $student = auth()->user()->student;

        $attendance = Attendance::with(['subject'])
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return $this->success($attendance);
    }
}