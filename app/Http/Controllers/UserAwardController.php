<?php

namespace App\Http\Controllers;

use App\Models\Awards;
use App\Models\UserAwards;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAwardController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //

    $user_awards = UserAwards::where('user_id', Auth::user()->id)->get();

    return view('content.awards.my-awards', ['user_awards' => $user_awards]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(UserAwards $awards)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function editMyAwards(UserAwards $awards)
  {
    // 
    $user_awards = UserAwards::get();
    return view('content.awards.update-my-awards', ['user_awards' => $user_awards]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
      $user_awards = UserAward::findOrFail($id);
  
      // Valida os dados de entrada
      $request->validate([
          'purchase_id' => 'required|string|max:255',
          'amount' => 'required|numeric|min:0',
          'status' => 'required|in:active,pending,closed',
      ]);
  
      // Atualiza os campos do prêmio
      $user_awards->purchase_id = $request->input('purchase_id');
      $user_awards->amount = $request->input('amount');
      $user_awards->status = $request->input('status');
  
      $user_awards->save();
  
      // Redireciona de volta para a lista de prêmios com uma mensagem de sucesso
      return redirect()->route('awards.index')->with('success', 'Prêmio atualizado com sucesso!');
  }
  
  

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(UserAwards $awards)
  {
    //
  }
}
