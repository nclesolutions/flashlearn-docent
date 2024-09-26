<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Holidays\Holidays;

class StudentController extends Controller
{
    public function index()
    {
        return view('dashboard.student.index');
    }
    public function view()
    {
        return view('dashboard.student.view');
    }
}
