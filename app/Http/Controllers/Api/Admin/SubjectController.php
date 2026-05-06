<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Traits\ApiResponse;

class SubjectController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Subject::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:subjects,name',
        ]);

        $subject = Subject::create($request->only('name'));

        return $this->success($subject, 'Subject created', 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:subjects,name,' . $subject->id,
        ]);

        $subject->update($request->only('name'));

        return $this->success($subject, 'Subject updated');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return $this->success(null, 'Subject deleted');
    }
}
