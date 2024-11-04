<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferEarn extends Model
{
  use HasFactory;

  protected $fillable = [
    'refer_user_id',
    'invited_user_id',
    'invited_user_bought',
    'earn_paid',
  ];
}
