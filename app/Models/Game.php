<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'game_id',
    'price',
    'open_at',
    'close_at',
    'status',
    'active'
  ];

  protected $with = "awards";

  public function awards(): HasMany
  {
    return $this->hasMany(GameAward::class);
  }
}
