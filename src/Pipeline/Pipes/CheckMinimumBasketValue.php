<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Closure;

class CheckMinimumBasketValue extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if ($discountCode->minimum_basket_value && $context->cart->total < $discountCode->minimum_basket_value) {
                $context->setInvalid(
                    "Discount code '{$discountCode->code}' requires a minimum basket value of {$discountCode->minimum_basket_value}."
                );
                return $context;
            }
        }

        return $next($context);
    }
} 