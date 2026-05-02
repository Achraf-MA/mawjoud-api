<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SurveillantController extends Controller
{
    use ApiResponse;

     /**
     * Get schedule for surveillant
     */
     public function schedule()
     {
         $schedules = Schedule::with(['class', 'subject', 'teacher'])
             ->get()
             ->groupBy('day');
 
         return $this->success($schedules);
     }
}
