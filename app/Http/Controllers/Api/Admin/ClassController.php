<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Traits\ApiResponse;
use App\Http\Requests\StoreClassRequest;


class ClassController extends Controller
{
    use ApiResponse;
    public function index()
    {
        return SchoolClass::paginate(10);
    }

    public function store(StoreClassRequest $request)
    {
        
        $class = SchoolClass::create($request->only('name'));

        return response()->json($class, 201);
    }
}