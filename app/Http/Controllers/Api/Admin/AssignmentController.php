<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $exists = DB::table('teacher_class_subject')
            ->where($request->only('teacher_id','class_id','subject_id'))
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Assignment already exists'
            ], 400);
        }

        DB::table('teacher_class_subject')->insert($request->only(
            'teacher_id','class_id','subject_id'
        ));

        return response()->json([
            'message' => 'Assignment created'
        ]);
    }
}
