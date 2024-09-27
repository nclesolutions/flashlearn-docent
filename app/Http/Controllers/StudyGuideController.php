<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\StudyGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudyGuideController extends Controller
{
    public function index()
    {
        // Haal de teacher_id op van de ingelogde gebruiker
        $teacherId = DB::table('teachers')
            ->where('user_id', Auth::id())
            ->value('id');

        // Haal alle vakken op van de ingelogde docent via teacher_id
        $subjectIds = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->pluck('id');

        // Haal alle studiewijzers op die horen bij deze vakken
        $studyGuides = StudyGuide::whereIn('subject_id', $subjectIds)->get();

        // Haal leerlingenaantallen op voor elke studiewijzer en groepeer ze per vak
        $groupedStudyGuides = $studyGuides->map(function ($guide) {
            // Haal het aantal leerlingen op via availabilities
            $availabilityCount = DB::table('availabilities')
                ->where('study_guide_id', $guide->id)
                ->count();

            if ($availabilityCount > 0) {
                $guide->student_count = $availabilityCount;
            } else {
                $guide->student_count = DB::table('students')
                    ->where('class_id', $guide->class_id)
                    ->count();
            }

            return $guide;
        })->groupBy('subject_id');

        return view('dashboard.studyguide.index', compact('groupedStudyGuides'));
    }
    public function view($id)
    {
        return view('dashboard.studyguide.view', compact('studyGuide'));
    }

    public function edit($id)
    {
        return view('dashboard.studyguide.edit', compact('studyGuide'));
    }

    public function create()
    {

        // Haal de teacher_id op van de ingelogde gebruiker
        $teacherId = DB::table('teachers')
            ->where('user_id', Auth::id())
            ->value('id');

        // Haal alle vakken op van de ingelogde docent via teacher_id
        $subjects = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->get();

        $classes = DB::table('classes')
            ->join('subjects', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.teacher_id', $teacherId)
            ->select('classes.*')
            ->distinct()
            ->get();

        return view('dashboard.studyguide.create', compact('subjects', 'classes'));
    }

    public function getClassesBySubject(Request $request)
    {
        $subjectId = $request->subject_id;

        $classes = DB::table('classes')
            ->join('subjects', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.id', $subjectId)
            ->select('classes.id', 'classes.name')
            ->distinct()
            ->get();

        return response()->json($classes);
    }
    public function getStudentsByClass(Request $request)
    {
        $classId = $request->class_id;

        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('class_id', $classId)
            ->select('students.id as student_id', 'users.firstname as student_name', 'users.lastname as student_lastname', 'students.user_id', 'students.org_id', 'students.class_id', 'students.role', 'students.created_at', 'students.updated_at')
            ->get();

        return response()->json($students);
    }


    public function store(Request $request)
    {
        // Valideer de invoer
        $validated = $request->validate([
            'subject_id' => 'required|integer',
            'class_id' => 'required|integer',
            'name' => 'required|string',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer|exists:students,id', // Controleer of student_ids bestaan in de students tabel
        ]);
        // Haal de teacher record op van de ingelogde gebruiker
        $teacher = DB::table('teachers')
            ->where('user_id', Auth::id())
            ->first();
        // Haal de org_id op van de teacher record
        $orgId = $teacher->org_id;
        // Nieuwe studiewijzer aanmaken en opslaan
        $studyGuide = new StudyGuide();
        $studyGuide->subject_id = $validated['subject_id'];
        $studyGuide->class_id = $validated['class_id'];
        $studyGuide->title = $validated['name'];
        $studyGuide->org_id = $orgId;
        $studyGuide->save();

        // Opslaan van beschikbaarheden als studenten zijn geselecteerd
        if (!empty($validated['student_ids'])) {
            foreach ($validated['student_ids'] as $studentId) {
                Availability::create([
                    'study_guide_id' => $studyGuide->id,
                    'student_id' => $studentId // Gebruik 'student_id' als kolomnaam
                ]);
            }
        }

        return redirect()->route('dashboard.studyguide.index')->with('success', 'Studiewijzer succesvol aangemaakt.');
    }}
