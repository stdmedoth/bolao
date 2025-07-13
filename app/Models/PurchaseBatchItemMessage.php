<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseBatchItemMessage extends Model
{
  use HasFactory;

  protected $table = 'purchase_batch_item_messages';

  protected $fillable = [
    'purchase_batch_item_id',
    'message',
    'type',
  ];

  /**
   * Get the purchase batch item that owns the message.
   */
  public function purchaseBatchItem(): BelongsTo
  {
    return $this->belongsTo(PurchaseBatchItems::class);
  }
}
