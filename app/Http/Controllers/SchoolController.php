<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Holidays\Holidays;

class SchoolController extends Controller
{
    const NEWSLETTER_LIMIT = 3;
    const LOCALE = 'nl_NL.UTF-8';
    const LOCALE_CARBON = 'nl';

    public function index()
    {
        $holidays = Holidays::for('nl')->get(); // Haal Nederlandse vakanties op
        $this->setLocale();
        list($schoolInfo, $newsletters) = $this->getUserRelatedData();

        return view('dashboard.school.index', [
            'newsletter' => $newsletters,
            'schoolInfo' => $schoolInfo,
            'holidays' => $holidays,
        ]);
    }

    private function getUserRelatedData()
    {
        $userId = auth()->user()->id;
        $userSchoolId = $this->getUserSchoolId($userId);
        $schoolInfo = $this->getSchoolInfo($userSchoolId);
        $schoolNewsletters = $this->getSchoolNewsletters($userSchoolId);
        $generalNewsletters = $this->getGeneralNewsletters();

        if (!$userSchoolId) {
            $newsletters = $generalNewsletters;
        } else {
            $newsletters = $schoolNewsletters->merge($generalNewsletters)->sortByDesc('created_at')->take(self::NEWSLETTER_LIMIT);
        }

        return [$schoolInfo, $newsletters];
    }

    private function getUserSchoolId($userId)
    {
        return DB::table('students')
            ->where('user_id', $userId)
            ->value('org_id');
    }

    private function getSchoolInfo($userSchoolId)
    {
        if ($userSchoolId) {
            return DB::table('schools')
                ->where('id', $userSchoolId)
                ->first();
        }
        return null;
    }

    private function getSchoolNewsletters($userSchoolId)
    {
        return DB::table('newsletter')
            ->join('users', 'newsletter.user_id', '=', 'users.id')
            ->where('org_id', $userSchoolId)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->select('newsletter.*', 'users.firstname', 'users.lastname')
            ->get();
    }

    private function getGeneralNewsletters()
    {
        return DB::table('newsletter')
            ->join('users', 'newsletter.user_id', '=', 'users.id')
            ->whereNull('org_id')
            ->orderBy('created_at', 'desc')
            ->limit(self::NEWSLETTER_LIMIT)
            ->select('newsletter.*', 'users.firstname', 'users.lastname')
            ->get();
    }

    private function setLocale()
    {
        setlocale(LC_TIME, self::LOCALE);
        \Carbon\Carbon::setLocale(self::LOCALE_CARBON);
    }
}
