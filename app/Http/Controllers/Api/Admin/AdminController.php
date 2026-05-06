<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse;

    /**
     * List all users.
     */
    public function index()
    {
        return $this->success(User::all());
    }

    /**
     * Create a user. When role is 'student', also creates the student record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8',
            'role'       => 'required|in:admin,teacher,parent,student,cpe',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => bcrypt($request->password),
            'role'       => $request->role,
        ]);

        if ($request->role === 'student') {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);

            Student::create([
                'user_id'  => $user->id,
                'class_id' => $request->class_id,
            ]);
        }

        return $this->success($user->load('student'), 'User created', 201);
    }

    /**
     * Update a user's name, email, role, and optionally password.
     * If the user is a student and class_id is provided, update the student record too.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'password'   => 'nullable|min:8',
            'role'       => 'required|in:admin,teacher,parent,student,cpe',
            'class_id'   => 'nullable|exists:classes,id',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'role'       => $request->role,
            ...($request->filled('password') ? ['password' => bcrypt($request->password)] : []),
        ]);

        if ($request->role === 'student' && $request->filled('class_id')) {
            $user->student()->updateOrCreate(
                ['user_id' => $user->id],
                ['class_id' => $request->class_id]
            );
        }

        return $this->success($user->fresh()->load('student'), 'User updated');
    }

    /**
     * Delete a user. Cascades to student record via DB constraint.
     * Prevents self-deletion.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return $this->error('You cannot delete your own account.', null, 403);
        }

        $user->delete();

        return $this->success(null, 'User deleted');
    }

    /**
     * Link a parent user to a student record.
     */
    public function linkParentStudent(Request $request)
    {
        $request->validate([
            'parent_id'  => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $exists = DB::table('parent_student')
            ->where('parent_id', $request->parent_id)
            ->where('student_id', $request->student_id)
            ->exists();

        if ($exists) {
            return $this->error('Link already exists', null, 400);
        }

        DB::table('parent_student')->insert([
            'parent_id'  => $request->parent_id,
            'student_id' => $request->student_id,
        ]);

        return $this->success(null, 'Parent linked to student');
    }

    /**
     * Unlink a parent from a student.
     */
    public function unlinkParentStudent(Request $request)
    {
        $request->validate([
            'parent_id'  => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $deleted = DB::table('parent_student')
            ->where('parent_id', $request->parent_id)
            ->where('student_id', $request->student_id)
            ->delete();

        if (!$deleted) {
            return $this->error('Link not found', null, 404);
        }

        return $this->success(null, 'Link removed');
    }

    /**
     * List all parent–student links with names resolved.
     */
    public function listOfLinkedParentsAndStudents()
    {
        return $this->success(
            DB::table('parent_student')
                ->join('users', 'parent_student.parent_id', '=', 'users.id')
                ->join('students', 'parent_student.student_id', '=', 'students.id')
                ->join('users as student_users', 'students.user_id', '=', 'student_users.id')
                ->select(
                    'parent_student.parent_id',
                    'parent_student.student_id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as parent"),
                    DB::raw("CONCAT(student_users.first_name, ' ', student_users.last_name) as student")
                )
                ->get()
        );
    }
}
