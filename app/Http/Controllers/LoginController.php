<?php
namespace App\Http\Controllers;


use App\Events\UserOfflineEvent;
use App\Models\RTAccount;
use Illuminate\Http\Request;


class LoginController extends \App\Http\Controllers\Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $credentials = $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);


        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => true]);
        }

        return response()->json(['success' => true]);
    }



    public function logout(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
            auth()->logout();
        }
        return response()->json(['logout' => true]);
        // return view('web.auth.login');
    }

}

