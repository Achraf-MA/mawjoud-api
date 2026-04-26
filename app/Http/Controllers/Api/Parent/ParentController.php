<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Justification;

class ParentController extends Controller
{
    /**
     * Get attendances of children
     */
    public function attendances(Request $request)
    {
        $user = $request->user();

        $studentIds = $user->students()->pluck('students.id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->with(['student','subject'])
            ->latest()
            ->paginate(10);

        return response()->json($attendances);
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
            return response()->json([
                'message' => 'Forbidden: not your child'
            ], 403);
        }

        // 🔥 Business rule: only absent/late can be justified
        if ($attendance->status === 'present') {
            return response()->json([
                'message' => 'Cannot justify a present attendance'
            ], 400);
        }

        // 🔥 Prevent duplicate justification
        if ($attendance->justification) {
            return response()->json([
                'message' => 'Justification already exists'
            ], 400);
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

        return response()->json([
            'message' => 'Justification submitted',
            'data' => $justification
        ]);
    }
}