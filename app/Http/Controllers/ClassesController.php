<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;

class ClassesController extends Controller
{
    public function index()
    {
        $classes = ClassModel::orderBy('name')->get();
        return view('classes.index', compact('classes'));
    }
}
