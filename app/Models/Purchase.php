<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase extends Model
{
  use HasFactory;
  protected $with = [
    "game"
  ];

  public function game(): HasOne 
  {
  	return $this->HasOne(Game::class);
  }

}
