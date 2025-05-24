<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameHistory extends Model
{
  use HasFactory;
  protected $fillable = [
    "game_id",
    "description",
    "type",
    "round",
    "result_numbers",
    "numbers",
  ];
}
