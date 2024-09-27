<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Homework;
use App\Models\StudyGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        // Haal het juiste record op uit de database
        $studyGuide = StudyGuide::with(['subject', 'schoolClass'])
            ->where('id', $id)
            ->firstOrFail();

        // Haal huiswerk op dat gerelateerd is aan het vak van deze studiewijzer
        $homeworks = Homework::where('study_guide_id', $id)
            ->where('subject_id', $studyGuide->subject->id)
            ->orderBy('return_date', 'asc')
            ->get();

        return view('dashboard.studyguide.view', compact('studyGuide', 'homeworks'));
    }

    // Methode voor het verwijderen van studiewijzer
    public function verwijder($id)
    {
        // Haal de studiewijzer op die moet worden verwijderd
        $studyGuide = StudyGuide::find($id);

        // Controleer of de studiewijzer bestaat
        if (!$studyGuide) {
            return redirect()->route('dashboard.studyguide.index')->with('error', 'Studiewijzer niet gevonden.');
        }

        // Verwijder de studiewijzer
        $studyGuide->delete();

        return redirect()->route('dashboard.studyguide.index')->with('success', 'Studiewijzer succesvol verwijderd.');
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

    public function createHomework($id)
    {
        $studyGuide = StudyGuide::find($id);

        if (!$studyGuide) {
            return redirect()->route('dashboard.studyguide.index')->with('error', 'Studiewijzer niet gevonden.');
        }

        return view('dashboard.studyguide.create_homework', compact('studyGuide'));
    }
    public function destroy($id)
    {
        // Haal het huiswerk op dat moet worden verwijderd
        $homework = Homework::find($id);

        // Controleer of het huiswerk bestaat
        if (!$homework) {
            return redirect()->back()->with('error', 'Huiswerk niet gevonden.');
        }

        // Verwijder het huiswerk
        $homework->delete();

        return redirect()->route('dashboard.studyguide.view', ['id' => $homework->study_guide_id])->with('success', 'Huiswerk succesvol verwijderd.');
    }
    public function storeHomework(Request $request)
    {
        // Valideer de invoer
        $validated = $request->validate([
            'study_guide_id' => 'required|integer|exists:study_guides,id',
            'name' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'required|string',
        ]);

        // Haal het juiste study guide record op
        $studyGuide = StudyGuide::find($validated['study_guide_id']);

        // Controleer of de study guide bestaat
        if (!$studyGuide) {
            return redirect()->back()->with('error', 'Ongeldige study guide ID.');
        }
        $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
        if (!$teacher) {
            return redirect()->back()->with('error', 'Docent niet gevonden.');
        }
        $orgId = $teacher->org_id;
        // Haal subject_id en subject_name op
        $subjectId = $studyGuide->subject_id;
        $subjectName = DB::table('subjects')->where('id', $subjectId)->value('name');
        // Opslaan van nieuw huiswerk
        $homework = new Homework();
        $homework->study_guide_id = $validated['study_guide_id'];
        $homework->subject_id = $subjectId;
        $homework->subject = $subjectName;
        $homework->title = $validated['name'];
        $homework->unique_id = Str::uuid();
        $homework->return_date = $validated['due_date'];
        $homework->description = $validated['description'];
        $homework->org_id = $orgId;  // Toevoegen van org_id
        $homework->save();
        return redirect()->route('dashboard.studyguide.view', ['id' => $validated['study_guide_id']])->with('success', 'Huiswerk succesvol aangemaakt.');
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
