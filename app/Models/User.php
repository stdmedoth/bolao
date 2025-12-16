<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $with = [
    'role',
    //'invited_by'
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'phone',
    'document',
    'external_financial_id',
    'password',

    'game_credit',
    'comission_percent',
    'game_credit_limit',

    'role_user_id',
    'invited_by_id',
    'seller_id',
    'active_refer_earn',

    'cc_name',
    'cc_number',
    'cc_expiry_month',
    'cc_expiry_year',
    'cc_ccv',

    'pix_key',
    'pix_key_type',

    'postal_code',
    'address_number',

  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function purchases()
  {
    return $this->hasMany(Purchase::class);
  }

  public function role()
  {
    return $this->belongsTo(RoleUser::class, 'role_user_id', 'id');
  }

  public function invited_by()
  {
    return $this->belongsTo(User::class, 'invited_by_id', 'id');
  }

  public function seller()
  {
    return $this->belongsTo(User::class, 'seller_id', 'id');
  }

  /**
   * Calcula o saldo devedor (crédito usado)
   * Retorna 0 se não deve nada
   */
  public function getCreditDebtAttribute()
  {
    return max(0, $this->game_credit_limit - $this->game_credit);
  }

  /**
   * Calcula o saldo disponível para saque
   * Saldo disponível = apenas o que está acima do limite inicial (game_credit_limit)
   * O limite é crédito dado pelo vendedor e não pode ser sacado
   * Exemplo: game_credit_limit=100, game_credit=150 → pode sacar apenas R$ 50
   */
  public function getAvailableBalanceAttribute()
  {
    // Só pode sacar o que está acima do limite inicial
    // Se game_credit < game_credit_limit, não pode sacar nada (está devendo)
    return max(0, $this->game_credit - $this->game_credit_limit);
  }

  /**
   * Verifica se o usuário tem saldo disponível para saque
   */
  public function hasAvailableBalance($amount)
  {
    return $this->available_balance >= $amount;
  }
}
