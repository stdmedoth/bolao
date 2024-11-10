<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAwards extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'purchase_id',
    'user_id',
    'amount',
  ];
}
