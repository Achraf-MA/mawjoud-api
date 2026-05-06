<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Traits\ApiResponse;

class ClassController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(SchoolClass::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name',
        ]);

        $class = SchoolClass::create($request->only('name'));

        return $this->success($class, 'Class created', 201);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,' . $class->id,
        ]);

        $class->update($request->only('name'));

        return $this->success($class, 'Class updated');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return $this->success(null, 'Class deleted');
    }
}
