<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Closure;

class CheckExpiryDate extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        $now = now();

        foreach ($context->discountCodes as $discountCode) {
            // Check if the discount has started
            if ($discountCode->starts_at && $discountCode->starts_at->isFuture()) {
                $context->setInvalid(
                    "Discount code '{$discountCode->code}' is scheduled to start at {$discountCode->starts_at->format('Y-m-d H:i:s')}."
                );
                return $context;
            }

            // Check if the discount has expired
            if ($discountCode->expires_at && $discountCode->expires_at->isPast()) {
                $context->setInvalid(
                    "Discount code '{$discountCode->code}' has expired on {$discountCode->expires_at->format('Y-m-d H:i:s')}."
                );
                return $context;
            }
        }

        return $next($context);
    }
} 