<?php

namespace Coupone\DiscountManager\Pipeline\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Coupone\DiscountManager\Enums\DiscountType;
use Closure;

class CheckSpecificGroupDiscount extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        foreach ($context->discountCodes as $discountCode) {
            if ($discountCode->type === DiscountType::SPECIFIC_GROUP) {
                $allowedGroups = $discountCode->conditions['allowed_groups'] ?? [];
                
                // Check if the customer belongs to any of the allowed groups
                $hasMatchingGroup = false;
                foreach ($context->customer->groups as $group) {
                    if (in_array($group, $allowedGroups)) {
                        $hasMatchingGroup = true;
                        break;
                    }
                }
                
                if (!$hasMatchingGroup) {
                    $context->setInvalid(
                        "Discount code '{$discountCode->code}' is not valid for your user group."
                    );
                    return $context;
                }
            }
        }

        return $next($context);
    }
} 