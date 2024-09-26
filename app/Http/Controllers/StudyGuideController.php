<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudyGuideController extends Controller
{
    public function index()
    {
        return view('dashboard.student.index');
    }
    public function view()
    {
        return view('dashboard.student.view');
    }
    public function edit()
    {
        return view('dashboard.student.edit');
    }
    public function create()
    {
        return view('dashboard.student.create');
    }
}
