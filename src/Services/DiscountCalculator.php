<?php

namespace Coupone\DiscountManager\Services;

use Illuminate\Pipeline\Pipeline;
use Coupone\DiscountManager\Contracts\DiscountCalculatorInterface;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationResultDTO;
use Coupone\DiscountManager\Models\DiscountCode;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Coupone\DiscountManager\Pipeline\Pipes\ValidateDiscountCode;
use Coupone\DiscountManager\Pipeline\Pipes\CheckExpiryDate;
use Coupone\DiscountManager\Pipeline\Pipes\ValidateUsageLimit;
use Coupone\DiscountManager\Pipeline\Pipes\CheckMinimumBasketValue;
use Coupone\DiscountManager\Pipeline\Pipes\CheckSpecificUserDiscount;
use Coupone\DiscountManager\Pipeline\Pipes\CheckSpecificGroupDiscount;
use Coupone\DiscountManager\Pipeline\Pipes\CheckFirstTimeBuyerDiscount;
use Coupone\DiscountManager\Pipeline\Pipes\ApplyDiscountCalculation;

class DiscountCalculator implements DiscountCalculatorInterface
{
    protected array $pipes = [
        ValidateDiscountCode::class,
        CheckExpiryDate::class,
        ValidateUsageLimit::class,
        CheckMinimumBasketValue::class,
        CheckSpecificUserDiscount::class,
        CheckSpecificGroupDiscount::class,
        CheckFirstTimeBuyerDiscount::class,
        ApplyDiscountCalculation::class,
    ];

    public function calculate(DiscountCalculationRequestDTO $request): DiscountCalculationResultDTO
    {
        $discountCodes = DiscountCode::whereIn('code', $request->discountCodes)->get();
        
        $context = new DiscountCalculationContext(
            cart: $request->cart,
            customer: $request->customer,
            discountCodes: $discountCodes
        );

        $context = app(Pipeline::class)
            ->send($context)
            ->through($this->pipes)
            ->thenReturn();

        return new DiscountCalculationResultDTO(
            originalCart: $request->cart,
            appliedDiscounts: $context->appliedDiscounts,
            totalDiscount: $context->totalDiscount,
            finalTotal: $context->getFinalTotal(),
            isValid: $context->isValid,
            errorMessage: $context->errorMessage,
            metadata: $context->metadata
        );
    }

    public function validateCombination(array $discountCodes): bool
    {
        $codes = DiscountCode::whereIn('code', $discountCodes)->get();

        // Check if any code is exclusive
        if ($codes->contains('is_exclusive', true)) {
            return false;
        }

        // Check if any code cannot be combined
        if ($codes->contains('can_be_combined', false)) {
            return false;
        }

        return true;
    }

    public function getMaximumDiscount(DiscountCalculationRequestDTO $request): DiscountCalculationResultDTO
    {
        // Get all valid discount codes
        $discountCodes = DiscountCode::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->get();

        // Create a new request with all valid discount codes
        $maxRequest = new DiscountCalculationRequestDTO(
            cart: $request->cart,
            customer: $request->customer,
            discountCodes: $discountCodes->pluck('code')->toArray()
        );

        return $this->calculate($maxRequest);
    }
} 