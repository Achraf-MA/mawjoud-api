<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Justification;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create base data
        $classes = SchoolClass::factory(5)->create();
        $subjects = Subject::factory(5)->create();

        $teachers = User::factory(5)->teacher()->create();
        $parents = User::factory(10)->parent()->create();

        // Students
        $students = Student::factory(20)->create([
            'class_id' => $classes->random()->id,
        ]);

        // Parent-Student pivot
        foreach ($students as $student) {
            DB::table('parent_student')->insert([
                'parent_id' => $parents->random()->id,
                'student_id' => $student->id,
            ]);
        }

        // Teacher-Class-Subject pivot
        foreach ($teachers as $teacher) {
            foreach ($classes->random(2) as $class) {
                foreach ($subjects->random(2) as $subject) {
                    DB::table('teacher_class_subject')->insertOrIgnore([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ]);
                }
            }
        }

        // Schedules
        Schedule::factory(20)->create();

        // Attendances
        $attendances = Attendance::factory(50)->create([
            'class_id' => fn () => $classes->random()->id,
            'subject_id' => fn () => $subjects->random()->id,
            'teacher_id' => fn () => $teachers->random()->id,
            'student_id' => fn () => $students->random()->id,
        ]);

        // Justifications
        foreach ($attendances->random(20) as $attendance) {
            Justification::factory()->create([
                'attendance_id' => $attendance->id,
                'parent_id' => $parents->random()->id,
            ]);
        }
    }
}
