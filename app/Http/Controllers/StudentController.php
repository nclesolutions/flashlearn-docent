<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller {

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

        return view('dashboard.student.index', compact('studentDetails'));
    }

    public function view($id)
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

        // Fetch the student details
        $student = DB::table('students')->where('user_id', $id)->first();
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }

        // Fetch user details for the student
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found');
        }

        // Fetch absence details for the student
        $absences = DB::table('absence')->where('user_id', $id)->get();

        // Fetch homework details for the student
        $homework = DB::table('homework')->where('user_id', $id)->get();

        // Fetch grade details for the student
        $grades = DB::table('grades')->where('user_id', $id)->get();

        return view('dashboard.student.view', compact('user', 'student', 'studentDetails', 'absences', 'homework', 'grades'));
    }

}
