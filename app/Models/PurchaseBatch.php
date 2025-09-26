<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBatch extends Model
{
  use HasFactory;

  protected $table = 'purchase_batches';

  protected $fillable = [
    'description',
    'status',
    'round',
    'user_id',
    'game_id',
    'paid_by_user_id',
  ];

  protected $casts = [];

  // Relacionamento com o usuário que importou
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // Relacionamento com o jogo
  public function game()
  {
    return $this->belongsTo(Game::class);
  }

  public function items()
  {
    return $this->hasMany(PurchaseBatchItems::class, 'purchase_batch_id', 'id');
  }

  public function paid_by_user()
  {
    return $this->belongsTo(User::class, 'paid_by_user_id', 'id');
  }
}
