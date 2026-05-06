<?php

namespace App\Http\Controllers\Api\Cpe;

use App\Http\Controllers\Controller;
use App\Http\Resources\JustificationResource;
use Illuminate\Http\Request;
use App\Models\Justification;

class JustificationController extends Controller
{
    /**
     * List all justifications
     */
    public function index()
    {
        return JustificationResource::collection(
            Justification::with(['attendance.student.user'])->latest()->paginate(10)
        );
    }

    /**
     * Validate justification
     */
    public function validate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $justification = Justification::findOrFail($id);

        if ($justification->status !== 'pending') {
            return response()->json([
                'message' => 'Already processed'
            ], 400);
        }

        $justification->update([
            'status' => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now()
        ]);

        return response()->json([
            'message' => 'Justification updated',
            'data' => new JustificationResource($justification)
        ]);
    }
}