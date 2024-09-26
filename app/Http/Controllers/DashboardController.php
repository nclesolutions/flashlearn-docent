<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Controleer of de gebruiker is ingelogd
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Haal het ID van de huidige geauthenticeerde gebruiker op
        $userId = Auth::id();

        // Haal de docentgegevens van de ingelogde gebruiker op
        $teacher = Teacher::where('user_id', $userId)->first();

        if (!$teacher) {
            return redirect()->route('dashboard')->withErrors('Docent niet gevonden.');
        }

        $orgId = $teacher->org_id;
        $schedules = Schedule::where('school_id', $orgId)->get(); // Haal alleen roosters op voor de school van de docent

        $filteredLessons = [];

        foreach ($schedules as $schedule) {
            // Haal de class_id op uit de Schedule-tabel
            $className = \DB::table('classes')->where('id', $schedule->class_id)->value('name');

            // Decodeer de JSON-data in een array
            $roosters = json_decode($schedule->data, true);

            // Doorzoek alle weken in het rooster
            foreach ($roosters['weeks'] as $week) {
                foreach ($week['days'] as $day) {
                    foreach ($day['schedule'] as $lesson) {
                        if ($lesson['teacher'] == $teacher->id) {
                            // Voeg class_id toe aan de lesdata
                            $lesson['class'] = $className;

                            // Vervang de 'teacher' ID door de bijbehorende naam
                            $lesson['teacher'] = strtoupper(substr($teacher->user->firstname, 0, 1)) . '. ' . $teacher->user->lastname;

                            // Groepeer de lessen op basis van tijd, lokaal, docent en klas
                            $key = $lesson['time'] . '-' . $lesson['location'] . '-' . $lesson['teacher'];
                            if (!isset($filteredLessons[$week['week_number']][$day['day_of_week']][$key])) {
                                $filteredLessons[$week['week_number']][$day['day_of_week']][$key] = [
                                    'time' => $lesson['time'],
                                    'lesson' => [],
                                    'location' => $lesson['location'],
                                    'teacher' => $lesson['teacher'],
                                    'classes' => [],
                                    'students' => []
                                ];
                            }

                            // Voeg de les toe aan de lijst als deze nog niet bestaat
                            if (!in_array($lesson['lesson'], $filteredLessons[$week['week_number']][$day['day_of_week']][$key]['lesson'])) {
                                $filteredLessons[$week['week_number']][$day['day_of_week']][$key]['lesson'][] = $lesson['lesson'];
                            }

                            // Voeg de klas toe aan de lijst als deze nog niet bestaat
                            if (!in_array($lesson['class'], $filteredLessons[$week['week_number']][$day['day_of_week']][$key]['classes'])) {
                                $filteredLessons[$week['week_number']][$day['day_of_week']][$key]['classes'][] = $lesson['class'];
                            }

                            // Haal de leerlingen op voor de klas
                            $students = Student::where('class_id', $schedule->class_id)->pluck('user_id')->toArray();
                            $studentNames = DB::table('users')
                                ->whereIn('id', $students)
                                ->get(['firstname', 'lastname'])
                                ->map(function ($student) {
                                    return $student->firstname . ' ' . $student->lastname;
                                })
                                ->toArray();
                            $filteredLessons[$week['week_number']][$day['day_of_week']][$key]['students'] = array_merge($filteredLessons[$week['week_number']][$day['day_of_week']][$key]['students'], $studentNames);
                        }
                    }
                }
            }
        }

        return view('dashboard.index', compact('filteredLessons'));
    }
}
