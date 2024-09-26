<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomeworkController extends Controller {

    public function index()
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Fetch the class_id for the authenticated teacher
        $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        $class_id = $teacher->class_id;

        // Fetch students with the same class_id
        $students = DB::table('students')->where('class_id', $class_id)->get();

        // Fetch user details for each student
        $studentDetails = [];
        foreach ($students as $student) {
            $user = DB::table('users')->where('id', $student->user_id)->first();
            if ($user) {
                $studentDetails[] = $user;
            }
        }

        return view('dashboard.students.index', compact('studentDetails'));
    }
}
