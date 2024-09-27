<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private function getUserData()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        return Auth::user();
    }

    private function getAdditionalUserData($userId)
    {
        $biography = DB::table('users')
            ->where('id', $userId)
            ->value('biography');

        $userSchoolId = DB::table('students')
            ->where('user_id', $userId)
            ->value('org_id');

        $schoolInfo = null;
        if ($userSchoolId) {
            $schoolInfo = DB::table('schools')
                ->where('id', $userSchoolId)
                ->first();
        }

        return compact('biography', 'schoolInfo');
    }

    public function index()
    {
        $account = $this->getUserData();
        if ($account instanceof \Illuminate\Http\RedirectResponse) return $account;

        $additionalData = $this->getAdditionalUserData($account->id);

        return view('dashboard.account.index', array_merge(['account' => $account], $additionalData));
    }

    public function security()
    {
        $account = $this->getUserData();
        if ($account instanceof \Illuminate\Http\RedirectResponse) return $account;

        $additionalData = $this->getAdditionalUserData($account->id);

        return view('dashboard.account.security', array_merge(['account' => $account], $additionalData));
    }

    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required',
            'new_password' => 'nullable|min:8',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Het huidige wachtwoord is onjuist.']);
        }

        if ($user->email !== $request->input('email')) {
            $user->email = $request->input('email');
        }

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->save();

        return redirect()->route('profile.security')->with('status', [
            'title' => 'Gelukt!',
            'message' => 'Je beveiligingsinstellingen zijn bijgewerkt!',
            'type' => 'success',
        ]);
    }

    public function updateBio(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'biography' => 'required|max:1000',
        ]);

        $user->biography = $request->input('biography');
        $user->save();

        return redirect()->route('profile.settings')->with('status', [
            'title' => 'Gelukt!',
            'message' => 'Je biografie is bijgewerkt!',
            'type' => 'success',
        ]);
    }

    public function settings()
    {
        $account = $this->getUserData();
        if ($account instanceof \Illuminate\Http\RedirectResponse) return $account;

        $additionalData = $this->getAdditionalUserData($account->id);

        return view('dashboard.account.settings', array_merge(['account' => $account], $additionalData));
    }
}
