<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Rules\MatchOldPassword;

class UserController extends Controller
{   

    public function getLogin()
    {
        return view('auth/login')->with([
            'title' => 'login'
        ]);
    }

    public function getLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function updateUserProfile(Request $request)
    {   

        $user = User::where('nickname',$request -> input('nickname')) -> first();

        $user -> update([
            'name'      => $request -> input('name'),
            'surname'   => $request -> input('surname'),
            'email'     => $request -> input('email'),
            'tel'       => $request -> input('tel')
        ]);

        return redirect()->back();
    }

    public function postUpdatePassword(Request $request)
    {
        $user = User::where('nickname', Auth::user()-> nickname ) -> first();
        if($user -> password === md5($request -> input('old_password')))
        {
            $user -> update([
                'password'      => md5($request -> input('new_password'))
            ]);
        }

        return redirect()->back();
    }
    

    public function postUpdateUserSettings(Request $request)
    {
        $user = User::where('nickname', Auth::user()-> nickname ) -> first();

        $user -> update([
            'language' => $request -> input('language'),
        ]);

        session()->put('langauge_resource', $request -> input('language'));
        
        return redirect()->back();
    }
}
