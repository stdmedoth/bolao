<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'price',
    'open_at',
    'close_at',
    'status',
    'active'
  ];

  protected $with = "awards";

  public function awards()
  {
    return $this->hasMany(GameAward::class);
  }
}
