<?php

namespace Coupone\DiscountManager\Tests;

use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\Enums\DiscountType;
use Coupone\DiscountManager\Models\DiscountCode;
use Coupone\DiscountManager\Services\DiscountCalculator;
use Illuminate\Support\Collection;

class DiscountCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_percentage_discount_calculation()
    {
        // Create a discount code
        $discountCode = DiscountCode::create([
            'code' => 'PERCENT20',
            'name' => '20% Off',
            'description' => '20% off your entire order',
            'type' => DiscountType::PERCENTAGE,
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
            discountCodes: ['PERCENT20']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertTrue($result->isValid);
        $this->assertEquals(30.60, $result->totalDiscount); // 20% of 153.00
        $this->assertEquals(122.40, $result->finalTotal); // 153.00 - 30.60
        $this->assertCount(1, $result->appliedDiscounts);
    }

    public function test_fixed_amount_discount_calculation()
    {
        // Create a discount code
        $discountCode = DiscountCode::create([
            'code' => 'FIXED10',
            'name' => '$10 Off',
            'description' => '$10 off your entire order',
            'type' => DiscountType::FIXED_AMOUNT,
            'value' => 10,
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
            discountCodes: ['FIXED10']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertTrue($result->isValid);
        $this->assertEquals(10.00, $result->totalDiscount);
        $this->assertEquals(143.00, $result->finalTotal); // 153.00 - 10.00
        $this->assertCount(1, $result->appliedDiscounts);
    }

    public function test_minimum_basket_value_validation()
    {
        // Create a discount code with minimum basket value
        $discountCode = DiscountCode::create([
            'code' => 'MIN200',
            'name' => '20% Off on $200+',
            'description' => '20% off when you spend $200 or more',
            'type' => DiscountType::PERCENTAGE,
            'value' => 20,
            'minimum_basket_value' => 200,
            'is_active' => true,
        ]);

        // Create a cart DTO with total less than minimum
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
            discountCodes: ['MIN200']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('minimum basket value', $result->errorMessage);
    }

    public function test_expired_discount_validation()
    {
        // Create a discount code that has expired
        $discountCode = DiscountCode::create([
            'code' => 'EXPIRED',
            'name' => 'Expired Discount',
            'description' => 'This discount has expired',
            'type' => DiscountType::PERCENTAGE,
            'value' => 20,
            'is_active' => true,
            'expires_at' => now()->subDay(),
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
            discountCodes: ['EXPIRED']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('expired', $result->errorMessage);
    }

    public function test_specific_user_discount_validation()
    {
        // Create a discount code for a specific user
        $discountCode = DiscountCode::create([
            'code' => 'USER123',
            'name' => 'User Specific Discount',
            'description' => 'Discount for user 123 only',
            'type' => DiscountType::SPECIFIC_USER,
            'value' => 20,
            'is_active' => true,
            'conditions' => [
                'allowed_users' => [123],
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

        // Create a customer DTO with a different ID
        $customerDTO = new CustomerDTO(
            id: 456,
            type: 'user',
            groups: ['standard']
        );

        // Create a calculation request
        $request = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: ['USER123']
        );

        // Calculate the discount
        $calculator = app(DiscountCalculator::class);
        $result = $calculator->calculate($request);

        // Assert the result
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not valid for this user', $result->errorMessage);
    }
} 