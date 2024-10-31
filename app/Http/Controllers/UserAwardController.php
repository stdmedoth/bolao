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
  public function edit(UserAwards $awards)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, UserAwards $awards)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(UserAwards $awards)
  {
    //
  }
}
