<?php

namespace App\Http\Controllers;

use App\Models\ReferEarn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferEarnController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    $refered_qnt = ReferEarn::where('refer_user_id', Auth::user()->id)->count();
    $refered_qnt_bought = ReferEarn::where('refer_user_id', Auth::user()->id)->where('invited_user_bought', true)->count();
    $refered_amount_earned = ReferEarn::where('refer_user_id', Auth::user()->id)->where('invited_user_bought', true)->sum('amount');
    return view('content.refer_earn.refer_earn', [
      'refered_qnt' => $refered_qnt,
      'refered_qnt_bought' => $refered_qnt_bought,
      'refered_amount_earned' => $refered_amount_earned,
      'code' => base64_encode('refered_by_' . Auth::user()->id)
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request)
  {
    $code = $request->input('code');
    $code = base64_decode($code);
    $refered_by_id = str_replace('refered_by_', "", $code);



    return view("content.authentications.auth-register-basic", ['refered_by_id' => $refered_by_id]);
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
  public function show(ReferEarn $referEarn)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(ReferEarn $referEarn)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, ReferEarn $referEarn)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(ReferEarn $referEarn)
  {
    //
  }
}
