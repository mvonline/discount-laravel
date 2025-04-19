<?php

namespace Coupone\DiscountManager\Pipeline;

use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\Models\DiscountCode;
use Illuminate\Support\Collection;

class DiscountCalculationContext
{
    public function __construct(
        public readonly CartDTO $cart,
        public readonly CustomerDTO $customer,
        public readonly Collection $discountCodes,
        public float $totalDiscount = 0.0,
        public float $remainingAmount = 0.0,
        public Collection $appliedDiscounts,
        public bool $isValid = true,
        public ?string $errorMessage = null,
        public array $metadata = []
    ) {
        $this->remainingAmount = $cart->total;
        $this->appliedDiscounts = collect();
    }

    public function addAppliedDiscount(DiscountCode $discountCode, float $amount): void
    {
        $this->appliedDiscounts->push([
            'discount_code' => $discountCode,
            'amount' => $amount,
            'applied_at' => now(),
        ]);

        $this->totalDiscount += $amount;
        $this->remainingAmount -= $amount;
    }

    public function setInvalid(string $message): void
    {
        $this->isValid = false;
        $this->errorMessage = $message;
    }

    public function getFinalTotal(): float
    {
        return $this->cart->total - $this->totalDiscount;
    }
} 