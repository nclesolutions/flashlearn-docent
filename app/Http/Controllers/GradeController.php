<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        return view('dashboard.grades.index');
    }
    public function view()
    {
        return view('dashboard.grades.view');
    }
    public function edit()
    {
        return view('dashboard.grades.edit');
    }
    public function create()
    {
        return view('dashboard.grades.create');
    }
}
