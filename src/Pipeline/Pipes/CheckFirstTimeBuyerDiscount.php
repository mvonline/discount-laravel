<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Coupone\DiscountManager\Enums\DiscountType;
use Closure;

class CheckFirstTimeBuyerDiscount extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if ($discountCode->type === DiscountType::FIRST_TIME_BUYER && !$context->customer->isFirstTimeBuyer) {
                $context->setInvalid(
                    "Discount code '{$discountCode->code}' is only valid for first-time buyers."
                );
                return $context;
            }
        }

        return $next($context);
    }
} 