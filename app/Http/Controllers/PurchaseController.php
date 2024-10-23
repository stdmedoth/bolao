<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    $purchases = Purchase::where('user_id', Auth::user()->id)->get();
    return view('content.purchase.my-purchases', ['purchases' => $purchases]);
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
  public function show(Purchase $purchase)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Purchase $purchase)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Purchase $purchase)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Purchase $purchase)
  {
    //
  }
}
