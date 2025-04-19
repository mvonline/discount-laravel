<?php

namespace Coupone\DiscountManager\DTOs;

use Illuminate\Support\Collection;

class CartDTO
{
    public function __construct(
        public readonly Collection $items,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total,
        public readonly ?string $currency = 'USD',
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: collect($data['items'] ?? []),
            subtotal: $data['subtotal'] ?? 0.0,
            tax: $data['tax'] ?? 0.0,
            shipping: $data['shipping'] ?? 0.0,
            total: $data['total'] ?? 0.0,
            currency: $data['currency'] ?? 'USD',
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items->toArray(),
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping' => $this->shipping,
            'total' => $this->total,
            'currency' => $this->currency,
            'metadata' => $this->metadata,
        ];
    }
} 