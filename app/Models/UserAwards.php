<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAwards extends Model
{
  use HasFactory;

  protected $fillable = [
    'game_id',
    'user_id',
    'game_award_id',
    'amount',
    'round',
    'status'
  ];


  public function game()
  {
    return $this->belongsTo(Purchase::class, 'game_id', 'id');
  }

  public function game_award()
  {
    return $this->belongsTo(GameAward::class, 'game_award_id', 'id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }
}
