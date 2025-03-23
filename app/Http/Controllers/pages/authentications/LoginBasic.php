<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LoginBasic extends Controller
{
  public function index()
  {
    $email = "";
    return view('content.authentications.auth-login-basic', ['email' => $email]);
  }

  public function validate(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      return redirect()->intended('/');
    }

    return Redirect::back()->withErrors(
      [
        'email' => 'Login ou senha invalídos'
      ]
    );
  }

  public function logout()
  {
    Auth::logout();
    return redirect(route('login'));
  }
}
