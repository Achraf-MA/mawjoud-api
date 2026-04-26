<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'file_path' => $this->file_path,
            'status' => $this->status,

            'attendance_id' => $this->attendance_id,

            'reviewed_at' => $this->reviewed_at,

            'parent_id' => $this->parent_id,
            'reviewed_by' => $this->reviewed_by,
        ];
    }
}