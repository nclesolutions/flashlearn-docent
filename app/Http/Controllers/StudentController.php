<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        // Zorg ervoor dat de gebruiker is geauthenticeerd
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Haal class_id op voor de geauthenticeerde leraar
        $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        $class_id = $teacher->class_id;

        // Haal alle study_guide_ids op die bij de klas horen
        $studyGuideIds = DB::table('study_guides')
            ->where('class_id', $class_id)
            ->pluck('id');

        // Haal de naam van de leraar op
        $teacherName = DB::table('users')->where('id', $teacher->user_id)->value('firstname');

        // Haal studenten met dezelfde class_id op
        $students = DB::table('students')->where('class_id', $class_id)->get();

        // Haal gebruikersdetails op voor elke student
        $studentDetails = [];
        foreach ($students as $student) {
            $user = DB::table('users')->where('id', $student->user_id)->first();
            if ($user) {
                $studentDetails[] = $user;
            }
        }

        return view('dashboard.student.index', compact('studentDetails', 'teacherName'));
    }

    public function view($id)
    {
        // Zorg ervoor dat de gebruiker is geauthenticeerd
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Haal class_id op voor de geauthenticeerde leraar
        $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        $class_id = $teacher->class_id;

        // Haal alle study_guide_ids op die bij de klas horen
        $studyGuideIds = DB::table('study_guides')
            ->where('class_id', $class_id)
            ->pluck('id');

        // Haal de voornaam en achternaam van de leraar op
        $teacherUser = DB::table('users')->select('firstname', 'lastname')->where('id', $teacher->user_id)->first();
        $teacherName = "{$teacherUser->firstname} {$teacherUser->lastname}";

        // Haal studenten met dezelfde class_id op
        $students = DB::table('students')->where('class_id', $class_id)->get();

        // Haal gebruikersdetails op voor elke student
        $studentDetails = [];
        foreach ($students as $student) {
            $user = DB::table('users')->where('id', $student->user_id)->first();
            if ($user) {
                $studentDetails[] = $user;
            }
        }

        // Haal studentdetails op
        $student = DB::table('students')->where('user_id', $id)->first();
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }

        // Haal gebruikersdetails op voor de student
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found');
        }

        // Haal absencedetails op voor de student
        $absences = DB::table('absence')->where('user_id', $id)->get();

        // Haal huiswerkdetails op voor de student, gebaseerd op study_guide_id
        $homework = DB::table('homework')
            ->whereIn('study_guide_id', $studyGuideIds)
            ->get();

        // Haal gradedetails op voor de student inclusief vakdetails
        $grades = DB::table('grades')
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->where('grades.user_id', $id)
            ->select('grades.*', 'subjects.name as subject_name')
            ->get();

        return view('dashboard.student.view', compact('user', 'student', 'studentDetails', 'absences', 'homework', 'grades', 'teacherName'));
    }
}
