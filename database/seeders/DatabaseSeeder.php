<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Justification;
use App\Models\Schedule;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // -------------------------
            // 0. CLEAN (optional but recommended for demo DB)
            // -------------------------
            DB::table('justifications')->delete();
            DB::table('attendances')->delete();
            DB::table('schedules')->delete();
            DB::table('teacher_class_subject')->delete();
            DB::table('parent_student')->delete();

            Student::query()->delete();
            User::where('role', '!=', 'admin')->delete();
            SchoolClass::query()->delete();
            Subject::query()->delete();

            // -------------------------
            // 1. Core Data
            // -------------------------
            $classes = SchoolClass::factory()->count(3)->create();
            $subjects = Subject::factory()->count(4)->create();

            $teachers = User::factory()->count(5)->create([
                'role' => 'teacher',
            ]);

            $parents = User::factory()->count(10)->create([
                'role' => 'parent',
            ]);

            User::firstOrCreate(
                ['email' => 'sunless@test.com'],
                [
                    'role' => 'admin',
                    'first_name' => 'Admin',
                    'last_name' => 'Test',
                    'password' => Hash::make('password'),
                ]
            );

            // -------------------------
            // 2. Teacher-Class-Subject Matrix
            // -------------------------
            $matrix = [];

            foreach ($classes as $class) {
                foreach ($subjects as $subject) {

                    $teacher = $teachers->random();

                    DB::table('teacher_class_subject')->updateOrInsert([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ]);

                    $matrix[$class->id][$subject->id] = $teacher->id;
                }
            }

            // -------------------------
            // 3. Students + Parent link
            // -------------------------
            $studentsByClass = [];

            foreach ($classes as $class) {

                $students = collect();

                for ($i = 0; $i < 8; $i++) {

                    $user = User::factory()->create([
                        'role' => 'student',
                    ]);

                    $student = Student::create([
                        'user_id' => $user->id,
                        'class_id' => $class->id,
                    ]);

                    $students->push($student);

                    DB::table('parent_student')->insert([
                        'parent_id' => $parents->random()->id,
                        'student_id' => $student->id,
                    ]);
                }

                $studentsByClass[$class->id] = $students;
            }

            // -------------------------
            // 4. Schedule
            // -------------------------
            $days = ['monday','tuesday','wednesday','thursday','friday'];

            foreach ($classes as $class) {
                foreach ($days as $day) {

                    $hour = 8;

                    foreach ($subjects as $subject) {

                        $teacherId = $matrix[$class->id][$subject->id];

                        Schedule::firstOrCreate([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'day' => $day,
                            'starts_at' => sprintf('%02d:00:00', $hour),
                        ], [
                            'ends_at' => sprintf('%02d:00:00', $hour + 1),
                        ]);

                        $hour++;
                    }
                }
            }

            // -------------------------
            // 5. Attendance (deterministic)
            // -------------------------
            $startDate = Carbon::now()->subDays(5);

            foreach ($classes as $class) {

                foreach ($studentsByClass[$class->id] as $student) {

                    for ($i = 0; $i < 5; $i++) {

                        $date = $startDate->copy()->addDays($i);

                        foreach ($subjects as $subject) {

                            $teacherId = $matrix[$class->id][$subject->id];

                            $statusOptions = ['present','present','present','absent','late'];
                            $status = $statusOptions[array_rand($statusOptions)];

                            $attendance = Attendance::firstOrCreate([
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'date' => $date->toDateString(),
                            ], [
                                'teacher_id' => $teacherId,
                                'class_id' => $class->id,
                                'status' => $status,
                            ]);

                            // -------------------------
                            // 6. Justifications
                            // -------------------------
                            if (in_array($status, ['absent','late'])) {

                                $parentId = DB::table('parent_student')
                                    ->where('student_id', $student->id)
                                    ->value('parent_id');

                                if ($parentId) {
                                    Justification::firstOrCreate([
                                        'attendance_id' => $attendance->id,
                                    ], [
                                        'parent_id' => $parentId,
                                        'comment' => fake()->sentence(),
                                        'status' => fake()->randomElement(['pending','accepted','rejected']),
                                        'reviewed_by' => $teachers->random()->id,
                                        'reviewed_at' => now(),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}