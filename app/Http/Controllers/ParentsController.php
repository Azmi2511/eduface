<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParentModel;

class ParentsController extends Controller
{
    public function index()
    {
        $parents = ParentModel::orderBy('full_name')->get();
        return view('parents.index', compact('parents'));
    }
}
