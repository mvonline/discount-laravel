<?php

namespace Coupone\DiscountManager\Enums;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case PERCENTAGE_WITH_CAP = 'percentage_with_cap';
    case SPECIFIC_USER = 'specific_user';
    case SPECIFIC_GROUP = 'specific_group';
    case SPECIFIC_PRODUCT = 'specific_product';
    case BOGO = 'bogo';
    case BUY_X_GET_Y = 'buy_x_get_y';
    case WITH_EXPIRY = 'with_expiry';
    case FIRST_N_USERS = 'first_n_users';
    case MINIMUM_BASKET = 'minimum_basket';
    case BUNDLE = 'bundle';
    case CATEGORY_BASED = 'category_based';
    case SHIPPING = 'shipping';
    case LOYALTY_POINTS = 'loyalty_points';
    case REFERRAL = 'referral';
    case BULK_PURCHASE = 'bulk_purchase';
    case PAYMENT_METHOD = 'payment_method';
    case FIRST_TIME_BUYER = 'first_time_buyer';
    case SEASONAL = 'seasonal';
    case LIMITED_QUANTITY = 'limited_quantity';
    case LOCATION_BASED = 'location_based';
    case CUSTOMER_SEGMENT = 'customer_segment';
    case MEMBERSHIP = 'membership';
    case GIFT_CARD = 'gift_card';
    case TIERED = 'tiered';
    case FLASH = 'flash';
    case USER_ANNIVERSARY = 'user_anniversary';
    case APP_EXCLUSIVE = 'app_exclusive';
    case FREE_GIFT = 'free_gift';
    case UPGRADE = 'upgrade';
    case SUBSCRIPTION = 'subscription';
    case MILESTONE = 'milestone';
    case REFER_A_FRIEND = 'refer_a_friend';
    case PRODUCT_LAUNCH = 'product_launch';
    case DONATION_BASED = 'donation_based';
    case BUY_MORE_SAVE_MORE = 'buy_more_save_more';
    case COMBO = 'combo';
    case EXCHANGE = 'exchange';
} 