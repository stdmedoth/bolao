<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseImportStaging extends Model
{
  use HasFactory;

  protected $table = 'purchase_imports_staging'; // Nome da tabela

  protected $fillable = [
    'user_id',
    'batch_identifier',
    'gambler_name',
    'gambler_phone',
    'numbers',
    'game_id',
    'seller_id',
    'original_data',
    'validation_errors',
    'is_valid',
    'is_approved',
    'imported_at',
  ];

  protected $casts = [
    'original_data' => 'array',
    'validation_errors' => 'array',
    'is_valid' => 'boolean',
    'is_approved' => 'boolean',
    'imported_at' => 'datetime',
  ];

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
}
