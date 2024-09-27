<?php

namespace App\Http\Controllers;

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

        // Controleer of er study guides zijn gevonden en wat de resultaten zijn
        if ($studyGuides->isEmpty()) {
            dd("No study guides found for subject ids: " . implode(', ', $subjectIds->toArray()));
        }

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
        return view('dashboard.studyguide.create');
    }
}
