<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Str;

class ForgotPasswordBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-forgot-password-basic');
  }

  public function validate(Request $request)
  {
    $request->validate(
      [
        'email' => 'required|email|exists:users,email',
      ],
      [
        'email.required' => 'O campo email é obrigatório.',
        'email.email' => 'O campo email deve ser um endereço de email válido.',
        'email.exists' => 'Este email não está cadastrado.',
      ]
    );

    $user = User::where('email', $request->input('email'))->first();
    $status = Password::sendResetLink(
      $request->only('email')
    );

    if ($status !== Password::RESET_LINK_SENT) {
      return back()->withErrors(['email' => __($status)]);
    }


    return redirect()->back()->with('success', 'Instruções para recuperação de senha foram enviadas para o seu email.');
  }





  public function reset(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    return $status === Password::PASSWORD_RESET
      ? redirect()->route('login')->with('status', __($status))
      : back()->withErrors(['email' => [__($status)]]);
  }
}
