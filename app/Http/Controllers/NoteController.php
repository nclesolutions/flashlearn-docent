<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        return view('dashboard.note.index');
    }
    public function view()
    {
        return view('dashboard.note.view');
    }
    public function edit()
    {
        return view('dashboard.note.edit');
    }
    public function create()
    {
        return view('dashboard.note.create');
    }
}
