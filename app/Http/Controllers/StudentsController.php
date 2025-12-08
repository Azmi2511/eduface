<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentsController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('full_name')->get();
        return view('students.index', compact('students'));
    }
}
