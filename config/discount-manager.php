<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discount Manager Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the discount manager package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Here you can specify the table names used by the package.
    |
    */
    'tables' => [
        'discount_codes' => 'discount_codes',
        'discount_code_usage' => 'discount_code_usage',
        'discount_code_conditions' => 'discount_code_conditions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Here you can specify the model classes used by the package.
    | You can extend these classes in your application to add custom functionality.
    |
    */
    'models' => [
        'discount_code' => \Coupone\DiscountManager\Models\DiscountCode::class,
        'discount_code_usage' => \Coupone\DiscountManager\Models\DiscountCodeUsage::class,
        'discount_code_condition' => \Coupone\DiscountManager\Models\DiscountCodeCondition::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pipeline Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the order of pipes in the discount calculation pipeline.
    |
    */
    'pipeline' => [
        'pipes' => [
            \Coupone\DiscountManager\Pipes\ValidateDiscountCode::class,
            \Coupone\DiscountManager\Pipes\CheckExpiryDate::class,
            \Coupone\DiscountManager\Pipes\ValidateUsageLimit::class,
            \Coupone\DiscountManager\Pipes\CheckMinimumBasketValue::class,
            \Coupone\DiscountManager\Pipes\ApplyDiscountCalculation::class,
        ],
    ],
]; 