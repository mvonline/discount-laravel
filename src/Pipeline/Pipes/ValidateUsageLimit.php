<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Closure;

class ValidateUsageLimit extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if ($discountCode->usage_limit && $discountCode->usage_count >= $discountCode->usage_limit) {
                $context->setInvalid(
                    "Discount code '{$discountCode->code}' has reached its usage limit of {$discountCode->usage_limit}."
                );
                return $context;
            }
        }

        return $next($context);
    }
} 