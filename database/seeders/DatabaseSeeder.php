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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // -------------------------
            // 1. Core Data
            // -------------------------
            $classes  = SchoolClass::factory()->count(3)->create();
            $subjects = Subject::factory()->count(4)->create();
            $teachers = User::factory()->count(5)->teacher()->create();
            $parents  = User::factory()->count(10)->parent()->create();

            User::factory()->create([
                'role'       => 'admin',
                'password'   => bcrypt('password'),
                'email'      => 'sunless@test.com',
                'first_name' => 'Admin',
                'last_name'  => 'Test',
            ]);


            // -------------------------
            // 2. Assign teachers to class + subject
            // -------------------------
            $teachingMatrix = [];

            foreach ($classes as $class) {
                foreach ($subjects as $subject) {

                    $teacher = $teachers->random();

                    DB::table('teacher_class_subject')->insert([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ]);

                    $teachingMatrix[$class->id][$subject->id] = $teacher->id;
                }
            }

            // -------------------------
            // 3. Students per class
            // -------------------------
            $studentsByClass = [];
            foreach ($classes as $class) {
                $students = collect();
                for ($i = 0; $i < 8; $i++) {
                    $user = User::factory()->create(['role' => 'student']);
                    $student = Student::create([
                        'user_id'  => $user->id,
                        'class_id' => $class->id,
                    ]);
                    $students->push($student);
                }
                $studentsByClass[$class->id] = $students;

                foreach ($students as $student) {
                    DB::table('parent_student')->insert([
                        'parent_id'  => $parents->random()->id,
                        'student_id' => $student->id,
                    ]);
                }
            }

            // -------------------------
            // 4. Weekly Schedule (SAFE)
            // -------------------------
            $days = ['monday','tuesday','wednesday','thursday','friday'];

            foreach ($classes as $class) {

                foreach ($days as $day) {

                    $startHour = 8;

                    foreach ($subjects as $subject) {

                        $teacherId = $teachingMatrix[$class->id][$subject->id];

                        Schedule::create([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'day' => $day,
                            'starts_at' => sprintf('%02d:00:00', $startHour),
                            'ends_at' => sprintf('%02d:00:00', $startHour + 1),
                        ]);

                        $startHour++; // next time slot
                    }
                }
            }

            // -------------------------
            // 5. Attendance (NO DUPLICATES)
            // -------------------------
            $startDate = Carbon::now()->subDays(5);

            foreach ($classes as $class) {

                foreach ($studentsByClass[$class->id] as $student) {

                    for ($i = 0; $i < 5; $i++) {

                        $date = $startDate->copy()->addDays($i);

                        foreach ($subjects as $subject) {

                            $teacherId = $teachingMatrix[$class->id][$subject->id];

                            $status = fake()->randomElement(['present','present','present','absent','late']);

                            $attendance = Attendance::create([
                                'student_id' => $student->id,
                                'teacher_id' => $teacherId,
                                'class_id' => $class->id,
                                'subject_id' => $subject->id,
                                'date' => $date->toDateString(),
                                'status' => $status,
                            ]);

                            // -------------------------
                            // 6. Justifications (only if absent/late)
                            // -------------------------
                            if (in_array($status, ['absent','late']) && fake()->boolean(60)) {

                                $parentId = DB::table('parent_student')
                                    ->where('student_id', $student->id)
                                    ->value('parent_id');

                                Justification::create([
                                    'attendance_id' => $attendance->id,
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
        });
    }
}
