<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\ReferEarn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function validate(Request $request)
  {
    // Validação dos dados de entrada
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'document' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
    ]);

    try {
      // Criação do usuário com os dados validados
      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'document' => $validatedData['document'],
        'password' => Hash::make($validatedData['password']),
        'role_user_id' => 3,
      ]);

      if ($request->input('refered_by_id')) {
        ReferEarn::create([
          'refer_user_id' =>  $request->input('refered_by_id'),
          'invited_user_id' => $user->id,
          'invited_user_bought' => FALSE,
          'amount' => 10,
          'earn_paid' => FALSE,
        ]);
      }

      // Redireciona para a página de exibição do usuário criado
      return Redirect::back()
        ->with(['success'=> 'Usuário criado com sucesso! Aguarde confirmação do seu convidante']);
    } catch (\Exception $e) {
      // Retorna um erro em caso de falha na criação do usuário
      return Redirect::back()->withErrors(['error' => 'Falha ao criar usuário: ' . $e->getMessage()]);
    }
  }
}
