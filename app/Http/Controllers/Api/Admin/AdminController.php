<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse;
    /**
     * List users
     */
    public function index()
    {
        return $this->success(User::all());
    }

    /**
     * Create user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:teacher,cpe,parent,admin,direction'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json($user);
    }

    

    public function linkParentStudent(Request $request)
        {
            $request->validate([
                'parent_id' => 'required|exists:users,id',
                'student_id' => 'required|exists:students,id',
            ]);

            $exists = DB::table('parent_student')
                ->where('parent_id', $request->parent_id)
                ->where('student_id', $request->student_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Link already exists'
                ], 400);
            }

            DB::table('parent_student')->insert([
                'parent_id' => $request->parent_id,
                'student_id' => $request->student_id,
            ]);

            return response()->json([
                'message' => 'Parent linked to student'
            ]);
        }


        public function listOfLinkedParentsAndStudents()
        {
            return $this->success(
                DB::table('parent_student')
                    ->join('users', 'parent_student.parent_id', '=', 'users.id')
                    ->join('students', 'parent_student.student_id', '=', 'students.id')
                    ->select(
                        'users.name as parent',
                        DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student")
                    )
                    ->get()
            );
        }
}