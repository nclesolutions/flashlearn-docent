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
        $teacherId = $this->getTeacherId();
        $subjectIds = $this->getTeacherSubjectIds($teacherId);
        $studyGuides = $this->getStudyGuidesBySubjectIds($subjectIds);

        $groupedStudyGuides = $studyGuides->map(function ($guide) {
            $guide->student_count = $this->getStudentCountForStudyGuide($guide);
            return $guide;
        })->groupBy('subject_id');

        return view('dashboard.studyguide.index', compact('groupedStudyGuides'));
    }

    public function view($id)
    {
        $studyGuide = StudyGuide::with(['subject', 'schoolClass'])->findOrFail($id);
        $homeworks = Homework::where('study_guide_id', $id)
            ->where('subject_id', $studyGuide->subject->id)
            ->orderBy('return_date', 'asc')
            ->get();

        return view('dashboard.studyguide.view', compact('studyGuide', 'homeworks'));
    }

    public function verwijder($id)
    {
        $studyGuide = StudyGuide::find($id);
        if (!$studyGuide) {
            return redirect()
                ->route('dashboard.studyguide.index')
                ->with('error', 'Studiewijzer niet gevonden.');
        }

        $studyGuide->delete();

        return redirect()
            ->route('dashboard.studyguide.index')
            ->with('success', 'Studiewijzer succesvol verwijderd.');
    }

    public function edit($id)
    {
        return view('dashboard.studyguide.edit', compact('studyGuide'));
    }

    public function create()
    {
        $teacherId = $this->getTeacherId();
        $subjects = $this->getTeacherSubjects($teacherId);
        $classes = $this->getTeacherClasses($teacherId);

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
            ->select(
                'students.id as student_id',
                'users.firstname as student_name',
                'users.lastname as student_lastname',
                'students.user_id',
                'students.org_id',
                'students.class_id',
                'students.role',
                'students.created_at',
                'students.updated_at'
            )
            ->get();

        return response()->json($students);
    }

    public function createHomework($id)
    {
        $studyGuide = StudyGuide::find($id);
        if (!$studyGuide) {
            return redirect()
                ->route('dashboard.studyguide.index')
                ->with('error', 'Studiewijzer niet gevonden.');
        }

        return view('dashboard.studyguide.create_homework', compact('studyGuide'));
    }

    public function destroy($id)
    {
        $homework = Homework::find($id);
        if (!$homework) {
            return redirect()->back()->with('error', 'Huiswerk niet gevonden.');
        }

        $homework->delete();

        return redirect()
            ->route('dashboard.studyguide.view', ['id' => $homework->study_guide_id])
            ->with('success', 'Huiswerk succesvol verwijderd.');
    }

    public function storeHomework(Request $request)
    {
        $validated = $request->validate([
            'study_guide_id' => 'required|integer|exists:study_guides,id',
            'name' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $studyGuide = StudyGuide::find($validated['study_guide_id']);
        if (!$studyGuide) {
            return redirect()->back()->with('error', 'Ongeldige study guide ID.');
        }

        $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
        if (!$teacher) {
            return redirect()->back()->with('error', 'Docent niet gevonden.');
        }

        $orgId = $teacher->org_id;
        $subjectId = $studyGuide->subject_id;
        $subjectName = DB::table('subjects')->where('id', $subjectId)->value('name');

        $homework = new Homework();
        $homework->study_guide_id = $validated['study_guide_id'];
        $homework->subject_id = $subjectId;
        $homework->subject = $subjectName;
        $homework->title = $validated['name'];
        $homework->unique_id = Str::uuid();
        $homework->return_date = $validated['due_date'];
        $homework->description = $validated['description'];
        $homework->org_id = $orgId;
        $homework->save();

        return redirect()
            ->route('dashboard.studyguide.view', ['id' => $validated['study_guide_id']])
            ->with('success', 'Huiswerk succesvol aangemaakt.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer',
            'class_id' => 'required|integer',
            'name' => 'required|string',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $teacher = DB::table('teachers')
            ->where('user_id', Auth::id())
            ->first();

        // Haal de org_id op van de teacher record en voeg extra logica toe

        return redirect()
            ->route('dashboard.studyguide.index')
            ->with('success', 'Studiewijzer succesvol aangemaakt.');
    }

    private function getTeacherId()
    {
        return DB::table('teachers')
            ->where('user_id', Auth::id())
            ->value('id');
    }

    private function getTeacherSubjectIds($teacherId)
    {
        return DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->pluck('id');
    }

    private function getStudyGuidesBySubjectIds($subjectIds)
    {
        return StudyGuide::whereIn('subject_id', $subjectIds)->get();
    }

    private function getStudentCountForStudyGuide($guide)
    {
        $availabilityCount = DB::table('availabilities')
            ->where('study_guide_id', $guide->id)
            ->count();

        if ($availabilityCount > 0) {
            return $availabilityCount;
        } else {
            return DB::table('students')
                ->where('class_id', $guide->class_id)
                ->count();
        }
    }

    private function getTeacherSubjects($teacherId)
    {
        return DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->get();
    }

    private function getTeacherClasses($teacherId)
    {
        return DB::table('classes')
            ->join('subjects', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.teacher_id', $teacherId)
            ->select('classes.*')
            ->distinct()
            ->get();
    }
}
