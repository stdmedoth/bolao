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
    "identifier",
    "round",
    "paid_by_user_id",
    "user_id",
    "seller_id",
  ];

  protected $with = [
    "game",
    "user",
    "seller",
    "paid_by_user"
  ];

  public function game(): BelongsTo
  {
    return $this->belongsTo(Game::class);
  }

  public function seller(): BelongsTo
  {
    return $this->belongsTo(User::class, 'seller_id', 'id');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function paid_by_user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'paid_by_user_id', 'id');
  }
}
