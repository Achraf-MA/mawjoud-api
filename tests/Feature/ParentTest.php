<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_view_child_attendance()
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $class = SchoolClass::create(['name' => '1A']);

        $student = Student::create([
            'first_name' => 'Ali',
            'last_name' => 'Ahmed',
            'class_id' => $class->id
        ]);

        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $student->id
        ]);

        $this->actingAs($parent);

        $response = $this->getJson('/api/parent/attendances');

        $response->assertStatus(200);
    }

    public function test_parent_can_submit_justification()
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $class = \App\Models\SchoolClass::create(['name' => '1A']);
        $subject = \App\Models\Subject::create(['name' => 'Math']);

        $student = \App\Models\Student::create([
            'first_name' => 'Ali',
            'last_name' => 'Ahmed',
            'class_id' => $class->id
        ]);

        // IMPORTANT: link parent to student
        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $student->id
        ]);

        DB::table('teacher_class_subject')->insert([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id
        ]);

        $attendance = \App\Models\Attendance::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'status' => 'absent'
        ]);

        $this->actingAs($parent);

        $response = $this->postJson('/api/parent/justifications', [
            'attendance_id' => $attendance->id,
            'comment' => 'Reason'
        ]);

        $response->assertStatus(200);
    }
}