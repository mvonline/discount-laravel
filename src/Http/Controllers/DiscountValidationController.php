<?php

namespace Coupone\DiscountManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Coupone\DiscountManager\Models\DiscountCode;
use Coupone\DiscountManager\Services\DiscountCalculator;
use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;

class DiscountValidationController extends Controller
{
    protected $discountCalculator;

    public function __construct(DiscountCalculator $discountCalculator)
    {
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * @OA\Post(
     *     path="/api/discount-codes/validate",
     *     summary="Validate discount codes for a cart",
     *     tags={"Discount Codes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart", "customer", "discount_codes"},
     *             @OA\Property(property="cart", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="subtotal", type="number"),
     *                 @OA\Property(property="tax", type="number"),
     *                 @OA\Property(property="shipping", type="number"),
     *                 @OA\Property(property="total", type="number")
     *             ),
     *             @OA\Property(property="customer", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="groups", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="is_first_time_buyer", type="boolean")
     *             ),
     *             @OA\Property(property="discount_codes", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount validation result",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_valid", type="boolean"),
     *             @OA\Property(property="final_total", type="number"),
     *             @OA\Property(property="discount_amount", type="number"),
     *             @OA\Property(property="applied_discounts", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.items' => 'required|array',
            'cart.items.*.id' => 'required|integer',
            'cart.items.*.name' => 'required|string',
            'cart.items.*.price' => 'required|numeric|min:0',
            'cart.items.*.quantity' => 'required|integer|min:1',
            'cart.subtotal' => 'required|numeric|min:0',
            'cart.tax' => 'required|numeric|min:0',
            'cart.shipping' => 'required|numeric|min:0',
            'cart.total' => 'required|numeric|min:0',
            'customer.id' => 'required|integer',
            'customer.type' => 'required|string',
            'customer.groups' => 'required|array',
            'customer.is_first_time_buyer' => 'boolean',
            'discount_codes' => 'required|array',
            'discount_codes.*' => 'string',
        ]);

        $cartDTO = new CartDTO(
            items: collect($request->input('cart.items')),
            subtotal: $request->input('cart.subtotal'),
            tax: $request->input('cart.tax'),
            shipping: $request->input('cart.shipping'),
            total: $request->input('cart.total')
        );

        $customerDTO = new CustomerDTO(
            id: $request->input('customer.id'),
            type: $request->input('customer.type'),
            groups: $request->input('customer.groups'),
            isFirstTimeBuyer: $request->input('customer.is_first_time_buyer', false)
        );

        $calculationRequest = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: $request->input('discount_codes')
        );

        $result = $this->discountCalculator->calculate($calculationRequest);

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/discount-codes/maximum-discount",
     *     summary="Calculate maximum possible discount for a cart",
     *     tags={"Discount Codes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart", "customer"},
     *             @OA\Property(property="cart", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="subtotal", type="number"),
     *                 @OA\Property(property="tax", type="number"),
     *                 @OA\Property(property="shipping", type="number"),
     *                 @OA\Property(property="total", type="number")
     *             ),
     *             @OA\Property(property="customer", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="groups", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="is_first_time_buyer", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maximum discount calculation result",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_valid", type="boolean"),
     *             @OA\Property(property="final_total", type="number"),
     *             @OA\Property(property="discount_amount", type="number"),
     *             @OA\Property(property="applied_discounts", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getMaximumDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.items' => 'required|array',
            'cart.items.*.id' => 'required|integer',
            'cart.items.*.name' => 'required|string',
            'cart.items.*.price' => 'required|numeric|min:0',
            'cart.items.*.quantity' => 'required|integer|min:1',
            'cart.subtotal' => 'required|numeric|min:0',
            'cart.tax' => 'required|numeric|min:0',
            'cart.shipping' => 'required|numeric|min:0',
            'cart.total' => 'required|numeric|min:0',
            'customer.id' => 'required|integer',
            'customer.type' => 'required|string',
            'customer.groups' => 'required|array',
            'customer.is_first_time_buyer' => 'boolean',
        ]);

        $cartDTO = new CartDTO(
            items: collect($request->input('cart.items')),
            subtotal: $request->input('cart.subtotal'),
            tax: $request->input('cart.tax'),
            shipping: $request->input('cart.shipping'),
            total: $request->input('cart.total')
        );

        $customerDTO = new CustomerDTO(
            id: $request->input('customer.id'),
            type: $request->input('customer.type'),
            groups: $request->input('customer.groups'),
            isFirstTimeBuyer: $request->input('customer.is_first_time_buyer', false)
        );

        $calculationRequest = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: []
        );

        $result = $this->discountCalculator->getMaximumDiscount($calculationRequest);

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/discount-codes/{id}/track-usage",
     *     summary="Track usage of a discount code",
     *     tags={"Discount Codes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usage tracked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function trackUsage(DiscountCode $discountCode): JsonResponse
    {
        $discountCode->increment('usage_count');
        return response()->json(['message' => 'Usage tracked successfully']);
    }
} 