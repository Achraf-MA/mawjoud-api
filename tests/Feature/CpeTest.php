<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CpeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cpe_can_view_justifications()
    {
        $cpe = User::factory()->create(['role' => 'cpe']);

        $this->actingAs($cpe);

        $response = $this->getJson('/api/cpe/justifications');

        $response->assertStatus(200);
    }

    public function test_cpe_can_validate_justification()
    {
        $cpe = User::factory()->create(['role' => 'cpe']);
        $parent = User::factory()->create(['role' => 'parent']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $class = \App\Models\SchoolClass::create(['name' => '1A']);
        $subject = \App\Models\Subject::create(['name' => 'Math']);

        $student = \App\Models\Student::create([
            'first_name' => 'Ali',
            'last_name' => 'Ahmed',
            'class_id' => $class->id
        ]);

        DB::table('teacher_class_subject')->insert([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id
        ]);

        // Create valid attendance
        $attendance = \App\Models\Attendance::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'status' => 'absent'
        ]);

        // Create justification
        $justification = \App\Models\Justification::create([
            'attendance_id' => $attendance->id,
            'parent_id' => $parent->id,
            'status' => 'pending'
        ]);

        $this->actingAs($cpe);

        $response = $this->postJson("/api/cpe/justifications/{$justification->id}/validate", [
            'status' => 'accepted'
        ]);

        $response->assertStatus(200);
    }
}