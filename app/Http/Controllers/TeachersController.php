<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;

class TeachersController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('full_name')->get();
        return view('teachers.index', compact('teachers'));
    }
}
