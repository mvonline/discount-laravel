<?php

namespace Coupone\DiscountManager\Contracts;

use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationResultDTO;

interface DiscountCalculatorInterface
{
    /**
     * Calculate the discount for the given request.
     *
     * @param DiscountCalculationRequestDTO $request
     * @return DiscountCalculationResultDTO
     */
    public function calculate(DiscountCalculationRequestDTO $request): DiscountCalculationResultDTO;

    /**
     * Validate if the given discount codes can be applied together.
     *
     * @param array $discountCodes
     * @return bool
     */
    public function validateCombination(array $discountCodes): bool;

    /**
     * Get the maximum possible discount for the given request.
     *
     * @param DiscountCalculationRequestDTO $request
     * @return DiscountCalculationResultDTO
     */
    public function getMaximumDiscount(DiscountCalculationRequestDTO $request): DiscountCalculationResultDTO;
} 