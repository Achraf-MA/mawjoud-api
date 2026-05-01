<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function students($classId)
    {
        $students = Student::where('class_id', $classId)->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
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
}
