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
    'external_finnancial_id',
    'password',

    'balance',
    'game_credit',
    'comission_percent',
    'game_credit_limit',

    'role_user_id',
    'invited_by_id',
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
}
