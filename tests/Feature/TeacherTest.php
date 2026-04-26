<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_attendance()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $class = SchoolClass::create(['name' => '1A']);
        $subject = Subject::create(['name' => 'Math']);

        $student = Student::create([
            'first_name' => 'Ali',
            'last_name' => 'Ahmed',
            'class_id' => $class->id
        ]);

        DB::table('teacher_class_subject')->insert([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id
        ]);

        $this->actingAs($teacher);

        $response = $this->postJson('/api/teacher/attendance', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response->assertStatus(200);
    }

    public function test_non_teacher_cannot_create_attendance()
    {
        $user = User::factory()->create(['role' => 'parent']);

        $this->actingAs($user);

        $response = $this->postJson('/api/teacher/attendance', []);

        $response->assertStatus(403);
    }
}