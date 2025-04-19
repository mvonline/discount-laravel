<?php

namespace Coupone\DiscountManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Coupone\DiscountManager\Models\DiscountCode;
use Coupone\DiscountManager\Enums\DiscountType;
use Coupone\DiscountManager\Services\DiscountCalculator;
use Coupone\DiscountManager\DTOs\CartDTO;
use Coupone\DiscountManager\DTOs\CustomerDTO;
use Coupone\DiscountManager\DTOs\DiscountCalculationRequestDTO;
use Coupone\DiscountManager\Http\Requests\DiscountCodeRequest;

/**
 * @OA\Tag(
 *     name="Discount Codes",
 *     description="API Endpoints for managing discount codes"
 * )
 */
class DiscountCodeController extends ApiController
{
    protected $discountCalculator;

    public function __construct(DiscountCalculator $discountCalculator)
    {
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * @OA\Get(
     *     path="/api/discount-codes",
     *     summary="List all discount codes",
     *     tags={"Discount Codes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of discount codes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DiscountCode")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $discountCodes = DiscountCode::paginate(10);
        return response()->json($discountCodes);
    }

    /**
     * @OA\Post(
     *     path="/api/discount-codes",
     *     summary="Create a new discount code",
     *     tags={"Discount Codes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DiscountCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Discount code created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/DiscountCode")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(DiscountCodeRequest $request): JsonResponse
    {
        $discountCode = DiscountCode::create($request->validated());
        return response()->json($discountCode, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/discount-codes/{id}",
     *     summary="Get a specific discount code",
     *     tags={"Discount Codes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount code details",
     *         @OA\JsonContent(ref="#/components/schemas/DiscountCode")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discount code not found"
     *     )
     * )
     */
    public function show(DiscountCode $discountCode): JsonResponse
    {
        return response()->json($discountCode);
    }

    /**
     * @OA\Put(
     *     path="/api/discount-codes/{id}",
     *     summary="Update a discount code",
     *     tags={"Discount Codes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DiscountCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount code updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/DiscountCode")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discount code not found"
     *     )
     * )
     */
    public function update(DiscountCodeRequest $request, DiscountCode $discountCode): JsonResponse
    {
        $discountCode->update($request->validated());
        return response()->json($discountCode);
    }

    /**
     * @OA\Delete(
     *     path="/api/discount-codes/{id}",
     *     summary="Delete a discount code",
     *     tags={"Discount Codes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Discount code deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discount code not found"
     *     )
     * )
     */
    public function destroy(DiscountCode $discountCode): JsonResponse
    {
        $discountCode->delete();
        return response()->json(null, 204);
    }

    /**
     * Validate and calculate discount for a cart.
     */
    public function validateDiscount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create cart DTO
        $cartDTO = new CartDTO(
            items: collect($request->input('cart.items')),
            subtotal: $request->input('cart.subtotal'),
            tax: $request->input('cart.tax'),
            shipping: $request->input('cart.shipping'),
            total: $request->input('cart.total')
        );

        // Create customer DTO
        $customerDTO = new CustomerDTO(
            id: $request->input('customer.id'),
            type: $request->input('customer.type'),
            groups: $request->input('customer.groups'),
            isFirstTimeBuyer: $request->input('customer.is_first_time_buyer', false)
        );

        // Create calculation request
        $calculationRequest = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: $request->input('discount_codes')
        );

        // Calculate discount
        $result = $this->discountCalculator->calculate($calculationRequest);

        return response()->json($result);
    }

    /**
     * Get the maximum possible discount for a cart.
     */
    public function getMaximumDiscount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create cart DTO
        $cartDTO = new CartDTO(
            items: collect($request->input('cart.items')),
            subtotal: $request->input('cart.subtotal'),
            tax: $request->input('cart.tax'),
            shipping: $request->input('cart.shipping'),
            total: $request->input('cart.total')
        );

        // Create customer DTO
        $customerDTO = new CustomerDTO(
            id: $request->input('customer.id'),
            type: $request->input('customer.type'),
            groups: $request->input('customer.groups'),
            isFirstTimeBuyer: $request->input('customer.is_first_time_buyer', false)
        );

        // Create calculation request
        $calculationRequest = new DiscountCalculationRequestDTO(
            cart: $cartDTO,
            customer: $customerDTO,
            discountCodes: []
        );

        // Get maximum discount
        $result = $this->discountCalculator->getMaximumDiscount($calculationRequest);

        return response()->json($result);
    }

    /**
     * Track usage of a discount code.
     */
    public function trackUsage(DiscountCode $discountCode): JsonResponse
    {
        $discountCode->increment('usage_count');
        return response()->json(['message' => 'Usage tracked successfully']);
    }
} 