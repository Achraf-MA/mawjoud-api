<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $schedules = Schedule::with(['class', 'subject', 'teacher'])->get();
        return $this->success($schedules);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day'        => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'starts_at'  => 'required|date_format:H:i',
            'ends_at'    => 'required|date_format:H:i|after:starts_at',
        ]);

        $schedule = Schedule::create($data);
        return $this->success($schedule->load(['class', 'subject', 'teacher']), 201);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return $this->success(null);
    }
}