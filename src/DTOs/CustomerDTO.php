<?php

namespace Coupone\DiscountManager\DTOs;

class CustomerDTO
{
    public function __construct(
        public readonly mixed $id,
        public readonly string $type,
        public readonly array $groups = [],
        public readonly array $segments = [],
        public readonly ?string $location = null,
        public readonly ?string $paymentMethod = null,
        public readonly bool $isFirstTimeBuyer = false,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'],
            groups: $data['groups'] ?? [],
            segments: $data['segments'] ?? [],
            location: $data['location'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            isFirstTimeBuyer: $data['is_first_time_buyer'] ?? false,
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'groups' => $this->groups,
            'segments' => $this->segments,
            'location' => $this->location,
            'payment_method' => $this->paymentMethod,
            'is_first_time_buyer' => $this->isFirstTimeBuyer,
            'metadata' => $this->metadata,
        ];
    }
} 