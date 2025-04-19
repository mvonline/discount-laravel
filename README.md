# Laravel Discount Manager

A flexible and efficient Laravel package for managing and calculating discount codes using Laravel's Pipeline pattern.

## Features

- Comprehensive discount code management
- Flexible discount calculation using Laravel's Pipeline pattern
- Support for multiple discount types
- Extensible architecture
- Type-safe using PHP 8.2 features
- Built with Laravel 12

## Installation

You can install the package via composer:

```bash
composer require coupone/discount-manager
```

After installing the package, publish the configuration and migrations:

```bash
php artisan vendor:publish --tag="discount-manager-config"
php artisan vendor:publish --tag="discount-manager-migrations"
```

Then run the migrations:

```bash
php artisan migrate
```

## Usage

### Basic Usage

```php
use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\Services\DiscountCalculator;

// Create a cart DTO
$cartDTO = new CartDTO(
    items: collect([/* your cart items */]),
    subtotal: 100.00,
    tax: 10.00,
    shipping: 5.00,
    total: 115.00
);

// Create a customer DTO
$customerDTO = new CustomerDTO(
    id: 1,
    type: 'user',
    groups: ['vip'],
    segments: ['high-value']
);

// Create a calculation request
$request = new DiscountCalculationRequestDTO(
    cart: $cartDTO,
    customer: $customerDTO,
    discountCodes: ['SUMMER2024']
);

// Calculate the discount
$calculator = app(DiscountCalculator::class);
$result = $calculator->calculate($request);

// Check if the discount is valid
if ($result->isValid) {
    echo "Total discount: {$result->totalDiscount}";
    echo "Final total: {$result->finalTotal}";
} else {
    echo "Error: {$result->errorMessage}";
}
```

### Supported Discount Types

The package supports various discount types:

- Percentage Discount
- Fixed Amount Discount
- Percentage-to-Amount Discount
- Specific User Discount
- Specific Group Discount
- Specific Product Discount
- Buy One, Take One (BOGO)
- Buy X, Get Y Free
- With Expiry Date Discount
- First N Users Discount
- Minimum Basket Value Discount
- Bundle Discount
- Category-Based Discount
- Shipping Discount
- Loyalty Points Discount
- Referral Discount
- Bulk Purchase Discount
- Specific Payment Method Discount
- First-Time Buyer Discount
- Seasonal/Occasional Discount
- Limited Quantity Discount
- Location-Based Discount
- Customer Segment Discount
- Membership Discount
- Gift Card Discount
- Tiered Discounts
- Time-Limited Flash Discount
- User Anniversary Discount
- App-Exclusive Discount
- Free Gift with Purchase
- Upgrade Discount
- Subscription Discount
- Milestone Achievement Discount
- Refer-a-Friend Discount
- Product Launch Discount
- Donation-Based Discount
- Buy More, Save More Discount
- Combo Discount
- Exchange/Trade-In Discount

### Extending the Package

You can extend the package by:

1. Creating custom pipes for the discount calculation pipeline
2. Extending the base models
3. Adding custom discount types
4. Implementing custom validation rules

Example of creating a custom pipe:

```php
namespace App\Pipes;

use Coupone\DiscountManager\Pipeline\Pipe;
use Coupone\DiscountManager\Pipeline\DiscountCalculationContext;
use Closure;

class CustomValidationPipe extends Pipe
{
    public function handle(DiscountCalculationContext $context, Closure $next): DiscountCalculationContext
    {
        if ($this->shouldSkip($context)) {
            return $next($context);
        }

        // Your custom validation logic here

        return $next($context);
    }
}
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Your Name](https://github.com/yourusername)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## API Documentation

### Discount Codes

#### List Discount Codes
```http
GET /api/discount-codes
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "code": "SUMMER2024",
            "name": "Summer Sale",
            "type": "percentage",
            "value": 20,
            "is_active": true,
            "starts_at": "2024-06-01T00:00:00.000000Z",
            "expires_at": "2024-08-31T23:59:59.000000Z",
            "usage_limit": 100,
            "usage_count": 0,
            "minimum_basket_value": 50,
            "is_exclusive": false,
            "can_be_combined": true,
            "conditions": {
                "customer_groups": ["vip"],
                "product_categories": ["summer-collection"]
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

#### Create Discount Code
```http
POST /api/discount-codes
```

Request body:
```json
{
    "code": "SUMMER2024",
    "name": "Summer Sale",
    "description": "Summer collection discount",
    "type": "percentage",
    "value": 20,
    "is_active": true,
    "starts_at": "2024-06-01",
    "expires_at": "2024-08-31",
    "usage_limit": 100,
    "minimum_basket_value": 50,
    "is_exclusive": false,
    "can_be_combined": true,
    "conditions": {
        "customer_groups": ["vip"],
        "product_categories": ["summer-collection"]
    }
}
```

#### Get Discount Code
```http
GET /api/discount-codes/{id}
```

#### Update Discount Code
```http
PUT /api/discount-codes/{id}
```

#### Delete Discount Code
```http
DELETE /api/discount-codes/{id}
```

### Discount Validation

#### Validate Cart with Discount Codes
```http
POST /api/discount-codes/validate
```

Request body:
```json
{
    "cart": {
        "items": [
            {
                "id": 1,
                "name": "Product 1",
                "price": 100,
                "quantity": 2
            },
            {
                "id": 2,
                "name": "Product 2",
                "price": 50,
                "quantity": 1
            }
        ],
        "subtotal": 250,
        "tax": 25,
        "shipping": 10,
        "total": 285
    },
    "customer": {
        "id": 1,
        "type": "retail",
        "groups": ["vip"],
        "is_first_time_buyer": false
    },
    "discount_codes": ["SUMMER2024"]
}
```

Response:
```json
{
    "is_valid": true,
    "final_total": 228,
    "discount_amount": 57,
    "applied_discounts": [
        {
            "code": "SUMMER2024",
            "name": "Summer Sale",
            "type": "percentage",
            "value": 20,
            "amount": 57
        }
    ]
}
```

#### Get Maximum Discount
```http
POST /api/discount-codes/maximum-discount
```

Request body: Same as validate endpoint
Response: Same format as validate endpoint

#### Track Discount Usage
```http
POST /api/discount-codes/{id}/track-usage
```

Response:
```json
{
    "message": "Usage tracked successfully"
}
``` 