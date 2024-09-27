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
        $teacherName = $this->getFullTeacherName($teacher->user_id);
        $studentDetails = $this->getStudentDetailsByClassId($classId);

        // Let op: hier gebruiken we 'id' in plaats van 'user_id' om de student op te halen
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }

        // Let op: Gebruikersgegevens worden opgehaald met 'user_id' van de student
        $user = DB::table('users')->where('id', $student->user_id)->first();
        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found');
        }

        // Haal afwezigheden op van de student
        $absences = DB::table('absence')->where('user_id', $student->user_id)->get();

        // Haal alle studiewijzers op voor de klas van de student
        $classStudyGuideIds = DB::table('study_guides')
            ->where('class_id', $classId)
            ->pluck('id')
            ->toArray();

        // Haal huiswerk op voor de studiewijzers van de klas
        $allHomework = DB::table('homework')
            ->whereIn('study_guide_id', $classStudyGuideIds)
            ->get();

        // Haal expliciete beschikbaarheden op voor de student
        $explicitAvailabilities = DB::table('availabilities')
            ->where('student_id', $id)
            ->pluck('study_guide_id')
            ->toArray();

        // Create an array of all study guide IDs with explicit assignments
        $explicitStudyGuideIds = DB::table('availabilities')
            ->pluck('study_guide_id')
            ->toArray();

        // Filter het huiswerk: expliciet toewijzen of publiek voor de klas
        $filteredHomework = $allHomework->filter(function($homeworkItem) use ($explicitAvailabilities, $explicitStudyGuideIds) {
            $studyGuideId = $homeworkItem->study_guide_id;

            // Check if the study guide has any explicit assignments in availabilities
            if (in_array($studyGuideId, $explicitStudyGuideIds)) {
                // Only show if the student is explicitly assigned
                return in_array($studyGuideId, $explicitAvailabilities);
            }

            // If no explicit assignments, show to the entire class
            return true;
        });

        // Haal de cijfers op van de student
        $grades = DB::table('grades')
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->where('grades.user_id', $student->user_id)
            ->select('grades.*', 'subjects.name as subject_name')
            ->get();

        return view('dashboard.student.view', compact('user', 'student', 'studentDetails', 'absences', 'filteredHomework', 'grades', 'teacherName'));
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

    public function getStudentDetailsByClassId($classId)
    {
        $students = DB::table('students')->where('class_id', $classId)->get();
        $studentDetails = [];
        foreach ($students as $student) {
            $user = DB::table('users')->where('id', $student->user_id)->first();
            if ($user) {
                // Voeg de studentgegevens toe aan user object om in Blade template te gebruiken
                $user->student_id = $student->id;
                $studentDetails[] = $user;
            }
        }
        return $studentDetails;
    }
}
