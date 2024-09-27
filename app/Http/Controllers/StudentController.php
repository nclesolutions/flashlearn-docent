<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('login');
        }

        $teacher = $this->getAuthenticatedTeacher();
        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        $classId = $teacher->class_id;
        $teacherName = $this->getTeacherName($teacher->user_id);
        $studentDetails = $this->getStudentDetailsByClassId($classId);

        return view('dashboard.student.index', compact('studentDetails', 'teacherName'));
    }

    public function view($id)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('login');
        }

        $teacher = $this->getAuthenticatedTeacher();
        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        $classId = $teacher->class_id;
        $studyGuideIds = $this->getStudyGuideIdsByClassId($classId);
        $teacherName = $this->getFullTeacherName($teacher->user_id);
        $studentDetails = $this->getStudentDetailsByClassId($classId);

        $student = DB::table('students')->where('user_id', $id)->first();
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }

        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found');
        }

        $absences = DB::table('absence')->where('user_id', $id)->get();
        $homework = DB::table('homework')
            ->whereIn('study_guide_id', $studyGuideIds)
            ->get();
        $grades = DB::table('grades')
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->where('grades.user_id', $id)
            ->select('grades.*', 'subjects.name as subject_name')
            ->get();

        return view('dashboard.student.view', compact('user', 'student', 'studentDetails', 'absences', 'homework', 'grades', 'teacherName'));
    }

    private function isAuthenticated()
    {
        return Auth::check();
    }

    private function getAuthenticatedTeacher()
    {
        return DB::table('teachers')->where('user_id', Auth::id())->first();
    }

    private function getTeacherName($userId)
    {
        return DB::table('users')->where('id', $userId)->value('firstname');
    }

    private function getFullTeacherName($userId)
    {
        $user = DB::table('users')->select('firstname', 'lastname')->where('id', $userId)->first();
        return "{$user->firstname} {$user->lastname}";
    }

    private function getStudyGuideIdsByClassId($classId)
    {
        return DB::table('study_guides')->where('class_id', $classId)->pluck('id');
    }

    private function getStudentDetailsByClassId($classId)
    {
        $students = DB::table('students')->where('class_id', $classId)->get();
        $studentDetails = [];
        foreach ($students as $student) {
            $user = DB::table('users')->where('id', $student->user_id)->first();
            if ($user) {
                $studentDetails[] = $user;
            }
        }
        return $studentDetails;
    }
}
