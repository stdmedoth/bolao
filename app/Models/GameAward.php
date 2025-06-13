<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAward extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'condition_type',
    'exact_point_value',
    'winner_point_value',
    'only_when_finish_round',
    'only_on_first_round',
    'amount',
    'game_id'
  ];


  public function game(): BelongsTo
  {
    return $this->belongsTo(Game::class);
  }
}
