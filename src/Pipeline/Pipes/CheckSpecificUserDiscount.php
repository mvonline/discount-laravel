<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Coupone\DiscountManager\Enums\DiscountType;
use Closure;

class CheckSpecificUserDiscount extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if ($discountCode->type === DiscountType::SPECIFIC_USER) {
                $allowedUsers = $discountCode->conditions['allowed_users'] ?? [];
                
                if (!in_array($context->customer->id, $allowedUsers)) {
                    $context->setInvalid(
                        "Discount code '{$discountCode->code}' is not valid for this user."
                    );
                    return $context;
                }
            }
        }

        return $next($context);
    }
} 