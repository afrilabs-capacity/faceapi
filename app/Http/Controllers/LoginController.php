<?php
namespace App\Http\Controllers;


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

}

