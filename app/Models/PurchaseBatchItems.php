<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBatchItems extends Model
{
  use HasFactory;

  protected $table = 'purchase_batch_items'; // Nome da tabela

  protected $fillable = [
    'gambler_name',
    'gambler_phone',
    'numbers',
    'quantity',
    'price',
    'status',
    'game_id',
    'identifier',
    'round',
    'paid_by_user_id',
    'user_id',
    'seller_id',

    'purchase_batch_id', // Chave estrangeira para purchase_batches
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

  // Relacionamento com o vendedor
  public function seller()
  {
    return $this->belongsTo(User::class, 'seller_id');
  }

  public function messages()
  {
    return $this->hasMany(PurchaseBatchItemMessage::class, 'purchase_batch_item_id');
  }
}
