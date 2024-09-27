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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $teacher = Teacher::where('user_id', $userId)->first();

        $orgId = $teacher->org_id;
        $schedules = Schedule::where('school_id', $orgId)->get();
        $filteredLessons = $this->getFilteredLessons($schedules, $teacher);

        return view('dashboard.index', compact('filteredLessons'));
    }

    private function getFilteredLessons($schedules, $teacher) {
        $filteredLessons = [];

        foreach ($schedules as $schedule) {
            $className = DB::table('classes')->where('id', $schedule->class_id)->value('name');
            $roosters = json_decode($schedule->data, true);

            foreach ($roosters['weeks'] as $week) {
                foreach ($week['days'] as $day) {
                    foreach ($day['schedule'] as $lesson) {
                        if ($lesson['teacher'] == $teacher->id) {
                            $lesson = $this->augmentLessonData($lesson, $teacher, $className);
                            $key = $this->generateLessonKey($lesson);

                            $this->groupLessons($filteredLessons, $week['week_number'], $day['day_of_week'], $key, $lesson, $schedule->class_id);
                        }
                    }
                }
            }
        }

        return $filteredLessons;
    }

    private function augmentLessonData($lesson, $teacher, $className) {
        $lesson['class'] = $className;
        $lesson['teacher'] = strtoupper(substr($teacher->user->firstname, 0, 1)) . '. ' . $teacher->user->lastname;

        return $lesson;
    }

    private function generateLessonKey($lesson) {
        return $lesson['time'] . '-' . $lesson['location'] . '-' . $lesson['teacher'];
    }

    private function groupLessons(&$filteredLessons, $weekNumber, $dayOfWeek, $key, $lesson, $classId) {
        if (!isset($filteredLessons[$weekNumber][$dayOfWeek][$key])) {
            $filteredLessons[$weekNumber][$dayOfWeek][$key] = [
                'time' => $lesson['time'],
                'lesson' => [],
                'location' => $lesson['location'],
                'teacher' => $lesson['teacher'],
                'classes' => [],
                'students' => []
            ];
        }

        if (!in_array($lesson['lesson'], $filteredLessons[$weekNumber][$dayOfWeek][$key]['lesson'])) {
            $filteredLessons[$weekNumber][$dayOfWeek][$key]['lesson'][] = $lesson['lesson'];
        }

        if (!in_array($lesson['class'], $filteredLessons[$weekNumber][$dayOfWeek][$key]['classes'])) {
            $filteredLessons[$weekNumber][$dayOfWeek][$key]['classes'][] = $lesson['class'];
        }

        $studentNames = $this->getStudentNamesForClass($classId);
        $filteredLessons[$weekNumber][$dayOfWeek][$key]['students'] = array_merge($filteredLessons[$weekNumber][$dayOfWeek][$key]['students'], $studentNames);
    }

    private function getStudentNamesForClass($classId) {
        $studentIds = Student::where('class_id', $classId)->pluck('user_id')->toArray();
        return DB::table('users')
            ->whereIn('id', $studentIds)
            ->get(['firstname', 'lastname'])
            ->map(function ($student) {
                return $student->firstname . ' ' . $student->lastname;
            })
            ->toArray();
    }
}
