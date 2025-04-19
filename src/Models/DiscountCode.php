<?php

namespace Coupone\DiscountManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Coupone\DiscountManager\Enums\DiscountType;
use Coupone\DiscountManager\Enums\DiscountStatus;

/**
 * @OA\Schema(
 *     schema="DiscountCode",
 *     title="Discount Code",
 *     description="Discount code model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="SUMMER2024"),
 *     @OA\Property(property="name", type="string", example="Summer Sale"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Summer collection discount"),
 *     @OA\Property(property="type", type="string", enum=DiscountType::class, example="percentage"),
 *     @OA\Property(property="value", type="number", format="float", example=20),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="starts_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="usage_limit", type="integer", nullable=true, example=100),
 *     @OA\Property(property="usage_count", type="integer", example=0),
 *     @OA\Property(property="minimum_basket_value", type="number", format="float", nullable=true, example=50),
 *     @OA\Property(property="is_exclusive", type="boolean", example=false),
 *     @OA\Property(property="can_be_combined", type="boolean", example=true),
 *     @OA\Property(property="conditions", type="object", nullable=true,
 *         @OA\Property(property="customer_groups", type="array", @OA\Items(type="string"), example=["vip"]),
 *         @OA\Property(property="product_categories", type="array", @OA\Items(type="string"), example=["summer-collection"])
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class DiscountCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_basket_value',
        'usage_limit',
        'usage_count',
        'starts_at',
        'expires_at',
        'is_active',
        'is_exclusive',
        'can_be_combined',
        'conditions',
        'metadata',
    ];

    protected $casts = [
        'type' => DiscountType::class,
        'value' => 'decimal:2',
        'minimum_basket_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_exclusive' => 'boolean',
        'can_be_combined' => 'boolean',
        'conditions' => 'array',
        'metadata' => 'array',
    ];

    public function getStatusAttribute(): DiscountStatus
    {
        if (!$this->is_active) {
            return DiscountStatus::INACTIVE;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return DiscountStatus::EXPIRED;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return DiscountStatus::DEPLETED;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return DiscountStatus::SCHEDULED;
        }

        return DiscountStatus::ACTIVE;
    }

    public function isValid(): bool
    {
        return $this->status === DiscountStatus::ACTIVE;
    }

    public function incrementUsage(): bool
    {
        return $this->increment('usage_count');
    }
} 