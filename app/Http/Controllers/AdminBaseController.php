<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class AdminBaseController extends Controller
{
    /**
     * @var string
     */
    protected $viewPrefix = 'admin.';

    protected function view($view, $data = [])
    {
        return view($this->viewPrefix . $view, $data);
    }
}