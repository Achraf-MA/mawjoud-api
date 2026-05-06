<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(
            DB::table('teacher_class_subject')
                ->join('users', 'teacher_class_subject.teacher_id', '=', 'users.id')
                ->join('classes', 'teacher_class_subject.class_id', '=', 'classes.id')
                ->join('subjects', 'teacher_class_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'teacher_class_subject.id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as teacher"),
                    'classes.name as class',
                    'subjects.name as subject',
                    'teacher_class_subject.teacher_id',
                    'teacher_class_subject.class_id',
                    'teacher_class_subject.subject_id'
                )
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $exists = DB::table('teacher_class_subject')
            ->where($request->only('teacher_id', 'class_id', 'subject_id'))
            ->exists();

        if ($exists) {
            return $this->error('Assignment already exists', null, 400);
        }

        $id = DB::table('teacher_class_subject')->insertGetId(
            $request->only('teacher_id', 'class_id', 'subject_id')
        );

        return $this->success(['id' => $id], 'Assignment created', 201);
    }

    public function destroy($id)
    {
        $deleted = DB::table('teacher_class_subject')->where('id', $id)->delete();

        if (!$deleted) {
            return $this->error('Assignment not found', null, 404);
        }

        return $this->success(null, 'Assignment deleted');
    }
}
