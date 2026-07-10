<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderNotification extends Model
{
    use HasFactory;

    protected $table = 'production_order_notifications';

    protected $fillable = [
        'production_order_id',
        'user_id',
        'notification_type',
        'title',
        'message',
        'data',
        'is_sent',
        'sent_at',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the production order.
     */
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id', 'order_id');
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unsent notifications.
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope by notification type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }
}
