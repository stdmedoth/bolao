<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase extends Model
{
  use HasFactory;

  protected $fillable = [
    "gambler_name",
    "gambler_phone",
    "numbers",
    "quantity",
    "price",
    "status",
    "game_id",
    "user_id",
  ];

  protected $with = [
    "game",
    "user"
  ];

  public function game(): BelongsTo
  {
    return $this->belongsTo(Game::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
