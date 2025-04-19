<?php

namespace Coupone\DiscountManager\Tests;

use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\Enums\DiscountType;
use Coupone\DiscountManager\Models\DiscountCode;
use Coupone\DiscountManager\Services\DiscountCalculator;
use Illuminate\Support\Collection;

class DiscountValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_usage_limit_validation()
    {
        // Create a discount code with usage limit
        $discountCode = DiscountCode::create([
            'code' => 'LIMITED',
            'name' => 'Limited Usage Discount',
            'description' => 'Discount with usage limit',
            'type' => DiscountType::PERCENTAGE,
            'value' => 20,
            'is_active' => true,
            'usage_limit' => 5,
            'usage_count' => 5, // Already reached the limit
        ]);

        // Create a cart DTO
        $cartDTO = new CartDTO(
            items: collect([
                ['id' => 1, 'name' => 'Product 1', 'price' => 50.00, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 30.00, 'quantity' => 1],
            ]),
            subtotal: 130.00,
            tax: 13.00,
            shipping: 10.00,
            total: 153.00
        );

        // Create a customer DTO
        $customerDTO = new CustomerDTO(
            id: 1,
            type: 'user',
            groups: ['standard']
        );

        // Create a calculation request
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['LIMITED']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('usage limit', $result->errorMessage);
    }

    public function test_specific_group_discount_validation()
    {
        // Create a discount code for a specific group
        $discountCode = DiscountCode::create([
            'code' => 'GROUP123',
            'name' => 'Group Specific Discount',
            'description' => 'Discount for group 123 only',
            'type' => DiscountType::SPECIFIC_GROUP,
            'value' => 20,
            'is_active' => true,
            'conditions' => [
                'allowed_groups' => ['premium'],
            ],
        ]);

        // Create a cart DTO
        $cartDTO = new CartDTO(
            items: collect([
                ['id' => 1, 'name' => 'Product 1', 'price' => 50.00, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 30.00, 'quantity' => 1],
            ]),
            subtotal: 130.00,
            tax: 13.00,
            shipping: 10.00,
            total: 153.00
        );

        // Create a customer DTO with a different group
        $customerDTO = new CustomerDTO(
            id: 1,
            type: 'user',
            groups: ['standard']
        );

        // Create a calculation request
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['GROUP123']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not valid for your group', $result->errorMessage);
    }

    public function test_first_time_buyer_discount_validation()
    {
        // Create a discount code for first-time buyers
        $discountCode = DiscountCode::create([
            'code' => 'FIRSTBUY',
            'name' => 'First Time Buyer Discount',
            'description' => 'Discount for first-time buyers',
            'type' => DiscountType::FIRST_TIME_BUYER,
            'value' => 20,
            'is_active' => true,
        ]);

        // Create a cart DTO
        $cartDTO = new CartDTO(
            items: collect([
                ['id' => 1, 'name' => 'Product 1', 'price' => 50.00, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 30.00, 'quantity' => 1],
            ]),
            subtotal: 130.00,
            tax: 13.00,
            shipping: 10.00,
            total: 153.00
        );

        // Create a customer DTO that is not a first-time buyer
        $customerDTO = new CustomerDTO(
            id: 1,
            type: 'user',
            groups: ['standard'],
            isFirstTimeBuyer: false
        );

        // Create a calculation request
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['FIRSTBUY']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('first-time buyer', $result->errorMessage);
    }

    public function test_inactive_discount_validation()
    {
        // Create an inactive discount code
        $discountCode = DiscountCode::create([
            'code' => 'INACTIVE',
            'name' => 'Inactive Discount',
            'description' => 'This discount is inactive',
            'type' => DiscountType::PERCENTAGE,
            'value' => 20,
            'is_active' => false,
        ]);

        // Create a cart DTO
        $cartDTO = new CartDTO(
            items: collect([
                ['id' => 1, 'name' => 'Product 1', 'price' => 50.00, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 30.00, 'quantity' => 1],
            ]),
            subtotal: 130.00,
            tax: 13.00,
            shipping: 10.00,
            total: 153.00
        );

        // Create a customer DTO
        $customerDTO = new CustomerDTO(
            id: 1,
            type: 'user',
            groups: ['standard']
        );

        // Create a calculation request
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['INACTIVE']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('inactive', $result->errorMessage);
    }

    public function test_invalid_discount_code()
    {
        // Create a cart DTO
        $cartDTO = new CartDTO(
            items: collect([
                ['id' => 1, 'name' => 'Product 1', 'price' => 50.00, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 30.00, 'quantity' => 1],
            ]),
            subtotal: 130.00,
            tax: 13.00,
            shipping: 10.00,
            total: 153.00
        );

        // Create a customer DTO
        $customerDTO = new CustomerDTO(
            id: 1,
            type: 'user',
            groups: ['standard']
        );

        // Create a calculation request with non-existent discount code
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['NONEXISTENT']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not found', $result->errorMessage);
    }
} 