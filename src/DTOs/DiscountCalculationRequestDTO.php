<?php

namespace Coupone\DiscountManager\DTOs;

use Illuminate\Support\Collection;

/**
 * @OA\Schema(
 *     schema="CartDTO",
 *     title="Cart",
 *     description="Cart information for discount calculation",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/CartItem")),
 *     @OA\Property(property="subtotal", type="number", format="float", example=250.00),
 *     @OA\Property(property="tax", type="number", format="float", example=25.00),
 *     @OA\Property(property="shipping", type="number", format="float", example=10.00),
 *     @OA\Property(property="total", type="number", format="float", example=285.00)
 * )
 */
class CartDTO
{
    public function __construct(
        public readonly Collection $items,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total
    ) {}
}

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     title="Cart Item",
 *     description="Cart item information",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Product 1"),
 *     @OA\Property(property="price", type="number", format="float", example=100.00),
 *     @OA\Property(property="quantity", type="integer", example=2)
 * )
 */
class CartItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
        public readonly int $quantity
    ) {}
}

/**
 * @OA\Schema(
 *     schema="CustomerDTO",
 *     title="Customer",
 *     description="Customer information for discount calculation",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="retail"),
 *     @OA\Property(property="groups", type="array", @OA\Items(type="string"), example=["vip"]),
 *     @OA\Property(property="is_first_time_buyer", type="boolean", example=false)
 * )
 */
class CustomerDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly array $groups,
        public readonly bool $isFirstTimeBuyer = false
    ) {}
}

/**
 * @OA\Schema(
 *     schema="DiscountCalculationRequestDTO",
 *     title="Discount Calculation Request",
 *     description="Request for discount calculation",
 *     @OA\Property(property="cart", ref="#/components/schemas/CartDTO"),
 *     @OA\Property(property="customer", ref="#/components/schemas/CustomerDTO"),
 *     @OA\Property(property="discount_codes", type="array", @OA\Items(type="string"), example=["SUMMER2024"])
 * )
 */
class DiscountCalculationRequestDTO
{
    public function __construct(
        public readonly CartDTO $cart,
        public readonly CustomerDTO $customer,
        public readonly array $discountCodes,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cart: CartDTO::fromArray($data['cart']),
            customer: CustomerDTO::fromArray($data['customer']),
            discountCodes: $data['discount_codes'] ?? [],
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'cart' => $this->cart->toArray(),
            'customer' => $this->customer->toArray(),
            'discount_codes' => $this->discountCodes,
            'metadata' => $this->metadata,
        ];
    }
} 