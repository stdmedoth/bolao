<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAward extends Model
{
  use HasFactory;

  protected $fillable = [
    'condition_type',
    'minimum_point_value',
    'amount',
    'game_id'
  ];
}
