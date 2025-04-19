<?php

namespace Coupone\DiscountManager\Pipeline;

use Closure;

abstract class Pipe
{
    /**
     * Handle the incoming request.
     *
     * @param DiscountCalculationContext $context
     * @param Closure $next
     * @return DiscountCalculationContext
     */
    abstract public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext;

    /**
     * Check if the pipe should be skipped.
     *
     * @param DiscountCalculationContext $context
     * @return bool
     */
    protected function shouldSkip(DiscountCalculationContext $context): bool
    {
        return !$context->isValid;
    }
} 