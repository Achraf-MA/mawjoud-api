<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    use ApiResponse;
    public function students($classId)
    {
        $students = Student::select('students.*', 'users.first_name', 'users.last_name')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.class_id', $classId)
            ->get();

        return $this->success($students);
    }
    
    public function classes()
    {
        $teacherId = auth()->id();

        $classIds = DB::table('teacher_class_subject')
            ->where('teacher_id', $teacherId)
            ->pluck('class_id');

        return response()->json([
            'success' => true,
            'data' => SchoolClass::whereIn('id', $classIds)->get()
        ]);
    }

    public function subjectsByClass($classId)
    {
        $teacherId = auth()->id();

        $subjectIds = DB::table('teacher_class_subject')
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->pluck('subject_id');

        return response()->json([
            'success' => true,
            'data' => Subject::whereIn('id', $subjectIds)->get()
        ]);
    }

    public function schedule()
    {
        $schedules = Schedule::with(['class', 'subject'])
            ->where('teacher_id', auth()->id())
            ->get()
            ->groupBy('day');

        return $this->success($schedules);
    }
}
