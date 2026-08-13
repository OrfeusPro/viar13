@if(isset($recommendationDiscount) && $recommendationDiscount)
<div class="recommendation-discount-notification">
    <div class="recommendation-discount-notification__icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
        </svg>
    </div>
    <div class="recommendation-discount-notification__content">
        <div class="recommendation-discount-notification__title">
            {{ __('cart_new.recommendations_title') }}
        </div>
        <div class="recommendation-discount-notification__text">
            {{ str_replace('{discount}', 30, __('cart_new.recommendation_discount_notification')) }}
        </div>
    </div>
</div>

<style>
.recommendation-discount-notification {
    background: linear-gradient(135deg, #fa7846 0%, #ff9068 100%);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    box-shadow: 0 4px 12px rgba(250, 120, 70, 0.3);
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.recommendation-discount-notification__icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.recommendation-discount-notification__icon svg {
    width: 24px;
    height: 24px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.recommendation-discount-notification__content {
    flex-grow: 1;
    color: #fff;
}

.recommendation-discount-notification__title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #fff;
}

.recommendation-discount-notification__text {
    font-size: 15px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.95);
}

@media (max-width: 768px) {
    .recommendation-discount-notification {
        padding: 15px;
        gap: 12px;
    }

    .recommendation-discount-notification__icon {
        width: 40px;
        height: 40px;
    }

    .recommendation-discount-notification__title {
        font-size: 16px;
    }

    .recommendation-discount-notification__text {
        font-size: 14px;
    }
}
</style>
@endif
