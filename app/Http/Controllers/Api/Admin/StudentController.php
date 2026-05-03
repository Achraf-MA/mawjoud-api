<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;

class StudentController extends Controller
{
    public function index()
    {
        return StudentResource::collection(
            User::where('role', 'student')
                ->with('student.class')
                ->paginate(10)
        ); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'class_id' => 'required|exists:classes,id'
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => 'student'
        ]);

        Student::create([
            'user_id' => $user->id,
            'class_id' => $request->class_id
        ]);

        return $user->load('student')->load('student.class');
    }
}
