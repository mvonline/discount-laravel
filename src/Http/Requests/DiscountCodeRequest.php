<?php

namespace Coupone\DiscountManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Coupone\DiscountManager\Enums\DiscountType;

/**
 * @OA\Schema(
 *     schema="DiscountCodeRequest",
 *     title="Discount Code Request",
 *     description="Request data for creating or updating a discount code",
 *     required={"code", "name", "type", "value"},
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         maxLength=50,
 *         description="Unique code for the discount",
 *         example="SUMMER2024"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=100,
 *         description="Name of the discount",
 *         example="Summer Sale 2024"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Description of the discount",
 *         example="Get 20% off on all summer items"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"percentage", "fixed"},
 *         description="Type of discount",
 *         example="percentage"
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="number",
 *         format="float",
 *         minimum=0,
 *         description="Discount value (percentage or fixed amount)",
 *         example=20
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Whether the discount is active",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="starts_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Start date of the discount",
 *         example="2024-06-01T00:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="expires_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Expiration date of the discount",
 *         example="2024-08-31T23:59:59Z"
 *     ),
 *     @OA\Property(
 *         property="usage_limit",
 *         type="integer",
 *         minimum=1,
 *         nullable=true,
 *         description="Maximum number of times the discount can be used",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="minimum_basket_value",
 *         type="number",
 *         format="float",
 *         minimum=0,
 *         nullable=true,
 *         description="Minimum basket value required to use the discount",
 *         example=50
 *     ),
 *     @OA\Property(
 *         property="is_exclusive",
 *         type="boolean",
 *         description="Whether the discount cannot be combined with other discounts",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="can_be_combined",
 *         type="boolean",
 *         description="Whether the discount can be combined with other discounts",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="conditions",
 *         type="object",
 *         nullable=true,
 *         description="Additional conditions for the discount",
 *         @OA\Property(
 *             property="categories",
 *             type="array",
 *             @OA\Items(type="string"),
 *             description="Product categories the discount applies to"
 *         ),
 *         @OA\Property(
 *             property="products",
 *             type="array",
 *             @OA\Items(type="integer"),
 *             description="Product IDs the discount applies to"
 *         )
 *     )
 * )
 */
class DiscountCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'code' => 'required|string|max:50|unique:discount_codes,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', array_column(DiscountType::cases(), 'value')),
            'value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'minimum_basket_value' => 'nullable|numeric|min:0',
            'is_exclusive' => 'boolean',
            'can_be_combined' => 'boolean',
            'conditions' => 'nullable|array',
            'conditions.categories' => 'nullable|array',
            'conditions.categories.*' => 'string',
            'conditions.products' => 'nullable|array',
            'conditions.products.*' => 'integer',
        ];

        // For update requests, make the code unique rule ignore the current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = 'string|max:50|unique:discount_codes,code,' . $this->route('discount_code');
        }

        return $rules;
    }
} 