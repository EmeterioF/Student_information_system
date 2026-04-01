<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index');
    }

    public function data()
    {
        return DataTables::of(Student::query())
            ->addColumn('action', fn($row) => '
                <button class="btn btn-sm btn-primary" onclick="editData(' . $row->id . ')">Edit</button>
                <button class="btn btn-sm btn-danger"  onclick="deleteData(' . $row->id . ')">Delete</button>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        return response()->json([
            'status'  => true,
            'message' => 'Student created successfully!',
            'data'    => Student::create($validated),
        ]);
    }

    public function edit(string $id)
    {
        return response()->json(Student::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $student->update($request->validate($this->rules($id)));

        return response()->json([
            'status'  => true,
            'message' => 'Student updated successfully!',
            'data'    => $student,
        ]);
    }

    public function destroy(string $id)
    {
        Student::findOrFail($id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Student deleted successfully!',
        ]);
    }

    private function rules(?string $id = null): array
    {
        return [
            'student_id' => ['required', 'string', 'max:20',  Rule::unique('students', 'student_id')->ignore($id)],
            'name'       => ['required', 'string', 'max:255'],
            'course'     => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'email'      => ['required', 'email',   'max:255', Rule::unique('students', 'email')->ignore($id)],
            'grade'      => ['nullable', 'numeric', 'between:0,100'],
        ];
    }


    // Select2 AJAX search
    public function search(Request $request)
    {
        $search = $request->search;

        $students = Student::where(function ($query) use ($search) {
                if ($search) {
                    $query->where('student_id', 'like', "%{$search}%")
                          ->orWhere('name',       'like', "%{$search}%")
                          ->orWhere('course',     'like', "%{$search}%")
                          ->orWhere('email',      'like', "%{$search}%");
                }
            })
            ->paginate(20);

        $results = [];

        foreach ($students as $student) {
            $results[] = [
                'id'         => $student->id,
                'text'       => $student->student_id . ' — ' . $student->name . ' [' . $student->course . ']',
                'student_id' => $student->student_id,
                'name'       => $student->name,
                'course'     => $student->course,
                'year_level' => $student->year_level,
                'email'      => $student->email,
                'grade'      => $student->grade,
            ];
        }

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => $students->hasMorePages()],
        ]);
    }

}