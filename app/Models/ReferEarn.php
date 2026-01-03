<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferEarn extends Model
{
  use HasFactory;

  protected $fillable = [
    'refer_user_id',
    'invited_user_id',
    'invited_user_bought',
    'earn_paid',
    'amount',
  ];

  protected function casts(): array
  {
    return [
      'invited_user_bought' => 'boolean',
      'earn_paid' => 'boolean',
    ];
  }

  public function referUser()
  {
    return $this->belongsTo(User::class, 'refer_user_id');
  }

  public function invitedUser()
  {
    return $this->belongsTo(User::class, 'invited_user_id');
  }
}
