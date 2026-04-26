<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * List users
     */
    public function index()
    {
        return User::latest()->get();
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
}