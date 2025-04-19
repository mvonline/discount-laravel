<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Closure;

class ValidateDiscountCode extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if (!$discountCode->isValid()) {
                $context->setInvalid("Discount code '{$discountCode->code}' is not valid.");
                return $context;
            }
        }

        return $next($context);
    }
} 