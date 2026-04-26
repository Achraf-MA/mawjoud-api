<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_absence_workflow()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $parent = User::factory()->create(['role' => 'parent']);
        $cpe = User::factory()->create(['role' => 'cpe']);

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

        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $student->id
        ]);

        // Teacher creates attendance
        $this->actingAs($teacher)->postJson('/api/teacher/attendance', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'status' => 'absent'
        ]);

        // Parent submits justification
        $this->actingAs($parent)->postJson('/api/parent/justifications', [
            'attendance_id' => 1,
            'comment' => 'Medical'
        ]);

        // CPE validates
        $response = $this->actingAs($cpe)->postJson('/api/cpe/justifications/1/validate', [
            'status' => 'accepted'
        ]);

        $response->assertStatus(200);
    }
}