<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Justification;
use App\Models\Schedule;
use App\Traits\ApiResponse;

class ParentController extends Controller
{
    use ApiResponse;
    /**
     * Get attendances of children
     */
    public function attendances(Request $request)
    {
        $user = $request->user();

        $studentIds = $user->students()->pluck('students.id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereIn('status', ['absent', 'late'])
            ->with(['student', 'subject', 'justification'])
            ->latest()
            ->paginate(10);

        return $this->success($attendances);
    }

    /**
     * Submit justification
     */
    public function storeJustification(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'comment' => 'nullable|string'
        ]);

        $user = $request->user();

        $attendance = \App\Models\Attendance::with('student')->findOrFail($request->attendance_id);

        // 🔥 CRITICAL CHECK
        $isOwner = $user->students()
            ->where('students.id', $attendance->student_id)
            ->exists();

        if (!$isOwner) {
            return $this->error('Forbidden: not your child', 403);
        }

        // 🔥 Business rule: only absent/late can be justified
        if ($attendance->status === 'present') {
            return $this->error('Cannot justify a present attendance', 400);
        }

        // 🔥 Prevent duplicate justification
        if ($attendance->justification) {
            return $this->error('Justification already exists', 400);
        }

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('justifications', 'public');
        }

        $justification = \App\Models\Justification::create([
            'attendance_id' => $attendance->id,
            'parent_id' => $user->id,
            'file_path' => $filePath,
            'comment' => $request->comment,
            'status' => 'pending'
        ]);

        return $this->success($justification, 'Justification submitted');
    }


    public function schedule()
    {
        $children = auth()->user()->students;

        $schedules = Schedule::with(['subject', 'teacher'])
            ->whereIn('class_id', $children->pluck('class_id'))
            ->get()
            ->groupBy('day');

        return $this->success($schedules);
    }
}