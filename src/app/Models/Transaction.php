<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'related_user_id',
        'comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Связываем с моделью user (отправитель)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Связываем с моделью user (получатель)
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
