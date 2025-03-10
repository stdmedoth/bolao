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
    "amount",
    'external_id',
    "user_id"
  ];


  protected $with = [
    "user"
  ];


  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
