<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Coupone\DiscountManager\Enums\DiscountType;
use Closure;

class ApplyDiscountCalculation extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            $discountAmount = $this->calculateDiscountAmount($discountCode, $context);
            $context->addAppliedDiscount($discountCode, $discountAmount);
        }

        return $next($context);
    }

    protected function calculateDiscountAmount($discountCode, DiscountCalculationContext $context): float
    {
        return match ($discountCode->type) {
            DiscountType::PERCENTAGE => $this->calculatePercentageDiscount($discountCode, $context),
            DiscountType::FIXED_AMOUNT => $this->calculateFixedAmountDiscount($discountCode, $context),
            DiscountType::PERCENTAGE_WITH_CAP => $this->calculatePercentageWithCapDiscount($discountCode, $context),
            default => 0.0,
        };
    }

    protected function calculatePercentageDiscount($discountCode, DiscountCalculationContext $context): float
    {
        $discountAmount = $context->remainingAmount * ($discountCode->value / 100);
        return min($discountAmount, $context->remainingAmount);
    }

    protected function calculateFixedAmountDiscount($discountCode, DiscountCalculationContext $context): float
    {
        return min($discountCode->value, $context->remainingAmount);
    }

    protected function calculatePercentageWithCapDiscount($discountCode, DiscountCalculationContext $context): float
    {
        $percentageDiscount = $this->calculatePercentageDiscount($discountCode, $context);
        return min($percentageDiscount, $discountCode->value);
    }
} 