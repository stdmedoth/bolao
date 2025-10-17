<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\ReferEarn;
use App\Models\User;
use App\Rules\Cpf;
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
    $validatedData = $request->validate(
      [
        'name' => 'required|string|max:255',
        'document' => ['required', 'string', new Cpf, 'max:14', 'unique:users'],
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'required|string|max:255',
        'password' => 'required|string|min:8',
        'refered_by_id' => 'nullable|exists:users,id'
      ],
      [
        'name.required' => 'O campo nome é obrigatório.',
        'document.required' => 'O campo CPF é obrigatório.',
        'document.unique' => 'Este CPF já está em uso.',
        'email.required' => 'O campo email é obrigatório.',
        'email.email' => 'O campo email deve ser um endereço de email válido.',
        'email.unique' => 'Este email já está em uso.',
        'phone.required' => 'O campo telefone é obrigatório.',
        'password.required' => 'O campo senha é obrigatório.',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        'refered_by_id.exists' => 'O usuário referenciador não existe.'
      ]
    );

    $refered_by_user = $request->input('refered_by_id') ? User::find($request->input('refered_by_id')) : null;
    $seller = $refered_by_user && $refered_by_user->role->level_id == 'seller' ? $refered_by_user : null;

    try {
      // Criação do usuário com os dados validados
      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'document' => $validatedData['document'],
        'phone' => $validatedData['phone'],
        'password' => Hash::make($validatedData['password']),
        'invited_by_id' => $request->input('refered_by_id'),
        'seller_id' => $seller ? $seller->id : null,
        'role_user_id' => 3,
      ]);

      if ($request->input('refered_by_id')) {
        ReferEarn::create([
          'refer_user_id' =>  $request->input('refered_by_id'),
          'invited_user_id' => $user->id,
          'invited_user_bought' => FALSE,

          // TODO: Criar uma configuração para o valor da indicação
          'amount' => 10,
          'earn_paid' => FALSE,
        ]);
      }

      // Redireciona para a página de exibição do usuário criado
      return Redirect::back()
        ->with(['success' => 'Usuário criado com sucesso! Aguarde confirmação do seu convidante']);
    } catch (\Exception $e) {
      // Retorna um erro em caso de falha na criação do usuário
      return Redirect::back()->withErrors(['error' => 'Falha ao criar usuário: ' . $e->getMessage()]);
    }
  }
}
