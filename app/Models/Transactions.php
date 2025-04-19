<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transactions extends Model
{
  use HasFactory;

  protected $fillable = [
    "type",
    'external_id',
    "amount",

    "user_id",
    "game_id",
    "purchase_id",
  ];


  protected $with = [
    "user"
  ];


  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function game(): BelongsTo
  {
    return $this->belongsTo(Game::class);
  }

  public function purchase(): BelongsTo
  {
    return $this->belongsTo(Purchase::class);
  }
}
