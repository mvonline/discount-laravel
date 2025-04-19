<?php

namespace Coupone\DiscountManager\DTOs;

use Illuminate\Support\Collection;

class DiscountCalculationResultDTO
{
    public function __construct(
        public readonly CartDTO $originalCart,
        public readonly Collection $appliedDiscounts,
        public readonly float $totalDiscount,
        public readonly float $finalTotal,
        public readonly bool $isValid,
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            originalCart: CartDTO::fromArray($data['original_cart']),
            appliedDiscounts: collect($data['applied_discounts'] ?? []),
            totalDiscount: $data['total_discount'] ?? 0.0,
            finalTotal: $data['final_total'] ?? 0.0,
            isValid: $data['is_valid'] ?? false,
            errorMessage: $data['error_message'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'original_cart' => $this->originalCart->toArray(),
            'applied_discounts' => $this->appliedDiscounts->toArray(),
            'total_discount' => $this->totalDiscount,
            'final_total' => $this->finalTotal,
            'is_valid' => $this->isValid,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
        ];
    }
} 