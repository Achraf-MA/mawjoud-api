<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    public function teacher() {
    return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject() {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
