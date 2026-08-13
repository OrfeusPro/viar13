@if(isset($recommendationData) && !empty($recommendationData))
<div class="vz-art popup-frame popup-frame-recommendation">
    <div class="vz-art js-popup target-box popup-recommendation">
        <div class="popup-recommendation--wrapper">
            <i class="vz-art fa-close popup-close"></i>

            <div class="popup-recommendation--header">
                <h2 class="h2_old">@lang('cart_new.recommendation_modal_title')</h2>
                <p class="popup-recommendation--subtitle">@lang('cart_new.recommendation_modal_subtitle')</p>
            </div>

            <div class="popup-recommendation--body">
                @php
                    $itemType = \App\Models\GalleryType::where('id', $recommendationData['id_type'])->first();
                    $typeUrl = $itemType ? $itemType->url : 'canvas';
                    $imageData = $recommendationData['image'] ?? null;
                    $images = $imageData ? json_decode($imageData, true) : null;
                    $imageUrl = is_array($images) && !empty($images) ? $images[0] : $imageData;
                    $discountPercent = 30;
                @endphp

                <div class="popup-recommendation--image">
                    @if($imageUrl)
                        <img src="{{ asset('storage/'.$imageUrl) }}" alt="{{ $recommendationData['name'] }}">
                    @else
                        <div class="no-image">@lang('cart_new.no_image')</div>
                    @endif
                    <div class="popup-recommendation__discount-badge">
                        -{{ $discountPercent }}%
                    </div>
                </div>

                <div class="popup-recommendation__info">
                    <div class="popup-recommendation__product-name">
                        {{ $recommendationData['name'] }}
                    </div>

                    <div class="popup-recommendation__pricing">
                        <div class="pricing-row">
                            <span class="price-label">@lang('cart_new.original_price'):</span>
                            <span class="price-value price-value--old">{{ $recommendationData['original_price'] }}€</span>
                        </div>
                        <div class="pricing-row pricing-row--highlight">
                            <span class="price-label">@lang('cart_new.with_discount'):</span>
                            <span class="price-value price-value--new">{{ $recommendationData['discounted_price'] }}€</span>
                        </div>
                    </div>

                    @if(isset($recommendationData['original_price']) && isset($recommendationData['discounted_price']))
                        @php
                            $savings = $recommendationData['original_price'] - $recommendationData['discounted_price'];
                        @endphp
                        <div class="popup-recommendation__savings">
                            @lang('cart_new.you_save'): <strong>{{ number_format($savings, 2) }}€</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div class="popup-recommendation--actions">
                <button type="button" class="top-btn btn-add-recommendation"
                        data-item-id="{{ $recommendationData['id'] }}"
                        data-price="{{ $recommendationData['discounted_price'] }}">
                    @lang('cart_new.add_to_cart_with_discount')
                </button>
                <button type="button" class="btn-cancel popup-close">
                    @lang('cart_new.maybe_later')
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.popup-frame-recommendation {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999 !important;
    justify-content: center;
    align-items: center;
    overflow-y: auto;
    padding: 20px;
}

.popup-frame-recommendation .popup-recommendation.vz-art.js-popup {
    display: block !important;
    visibility: visible !important;
}

.popup-frame-recommendation .popup-recommendation {
    width: 600px !important;
    max-width: 90vw !important;
    position: relative !important;
    z-index: 1000 !important;
    margin: 0 auto !important;
}

.popup-frame-recommendation .popup-recommendation--wrapper {
    width: 100% !important;
    padding: 50px 40px !important;
    background: #FFF !important;
    border-radius: 10px !important;
    position: relative !important;
    box-sizing: border-box !important;
    box-shadow: 0px 10px 50px rgba(0, 0, 0, 0.3) !important;
    display: block !important;
    visibility: visible !important;
}

.popup-recommendation .fa-close.popup-close {
    position: absolute !important;
    top: 20px !important;
    right: 20px !important;
    font-size: 24px !important;
    color: #9CA5B5 !important;
    cursor: pointer !important;
    transition: 0.3s !important;
    z-index: 10 !important;
}

.popup-recommendation .fa-close.popup-close:hover {
    color: #FA7846 !important;
    transform: rotate(90deg) !important;
}

.popup-frame-recommendation .popup-recommendation--header {
    text-align: center !important;
    margin-bottom: 30px !important;
}

.popup-frame-recommendation .popup-recommendation--header .h2_old {
    font-size: 28px !important;
    font-weight: bold !important;
    color: #2D3748 !important;
    margin: 0 0 10px 0 !important;
}

.popup-frame-recommendation .popup-recommendation--subtitle {
    font-size: 14px !important;
    color: #666 !important;
    margin: 0 !important;
}

.popup-frame-recommendation .popup-recommendation--body {
    display: flex !important;
    flex-direction: row !important;
    gap: 25px !important;
    margin-bottom: 30px !important;
    align-items: flex-start !important;
}

.popup-frame-recommendation .popup-recommendation--image {
    position: relative !important;
    width: 200px !important;
    height: 200px !important;
    flex-shrink: 0 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    background: #f5f5f5 !important;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1) !important;
}

.popup-frame-recommendation .popup-recommendation--image img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.popup-frame-recommendation .popup-recommendation--image .no-image {
    width: 100% !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 14px !important;
    color: #999 !important;
}

.popup-frame-recommendation .popup-recommendation__discount-badge {
    position: absolute !important;
    top: 12px !important;
    right: 12px !important;
    background: linear-gradient(135deg, #FA7846 0%, #e66835 100%) !important;
    color: #fff !important;
    padding: 8px 14px !important;
    border-radius: 6px !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
}

.popup-frame-recommendation .popup-recommendation__info {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
}

.popup-frame-recommendation .popup-recommendation__product-name {
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #333 !important;
    margin-bottom: 15px !important;
    line-height: 1.4 !important;
}

.popup-frame-recommendation .popup-recommendation__pricing {
    background: #f9f9f9 !important;
    border-radius: 8px !important;
    padding: 15px !important;
    margin-bottom: 12px !important;
}

.popup-frame-recommendation .pricing-row {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 8px !important;
}

.popup-frame-recommendation .pricing-row:last-child {
    margin-bottom: 0 !important;
}

.popup-frame-recommendation .pricing-row--highlight {
    padding-top: 8px !important;
    border-top: 1px solid #e0e0e0 !important;
}

.popup-frame-recommendation .price-label {
    font-size: 14px !important;
    color: #666 !important;
}

.popup-frame-recommendation .price-value {
    font-size: 16px !important;
    font-weight: 600 !important;
}

.popup-frame-recommendation .price-value--old {
    color: #999 !important;
    text-decoration: line-through !important;
    font-weight: 400 !important;
}

.popup-frame-recommendation .price-value--new {
    font-size: 20px !important;
    color: #FA7846 !important;
    font-weight: 700 !important;
}

.popup-frame-recommendation .popup-recommendation__savings {
    background: linear-gradient(135deg, #FA7846 0%, #e66835 100%) !important;
    color: #fff !important;
    padding: 12px 16px !important;
    border-radius: 6px !important;
    text-align: center !important;
    font-size: 14px !important;
    box-shadow: 0px 4px 15px rgba(250, 120, 70, 0.3) !important;
}

.popup-frame-recommendation .popup-recommendation__savings strong {
    font-size: 18px !important;
    font-weight: 700 !important;
}

.popup-frame-recommendation .popup-recommendation--actions {
    width: 100% !important;
    text-align: center !important;
    display: flex !important;
    flex-direction: row !important;
    gap: 12px !important;
}

.popup-frame-recommendation .popup-recommendation--actions a,
.popup-frame-recommendation .popup-recommendation--actions button {
    flex: 1 !important;
    height: 60px !important;
    padding: 0 40px !important;
    border: none !important;
    border-radius: 8px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.4s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
}

.popup-frame-recommendation .popup-recommendation--actions .btn-add-recommendation {
    background: linear-gradient(90deg, #FC8C5F 0%, #FA7846 100%) !important;
    color: #fff !important;
    box-shadow: 0px 18px 46px -4px rgba(250, 120, 70, 0.25), 0px 2px 0 #E87145 !important;
}

.popup-frame-recommendation .popup-recommendation--actions .btn-add-recommendation:hover {
    transform: scale(1.04) !important;
    color: #fff !important;
}

.popup-frame-recommendation .popup-recommendation--actions .btn-add-recommendation:disabled {
    opacity: 0.7 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

.popup-frame-recommendation .popup-recommendation--actions .btn-cancel {
    background: #f5f5f5 !important;
    color: #666 !important;
}

.popup-frame-recommendation .popup-recommendation--actions .btn-cancel:hover {
    background: #e0e0e0 !important;
    color: #333 !important;
}

@media (max-width: 768px) {
    .popup-frame-recommendation .popup-recommendation {
        width: 100% !important;
    }

    .popup-frame-recommendation .popup-recommendation--wrapper {
        padding: 40px 30px !important;
    }

    .popup-frame-recommendation .popup-recommendation--header .h2_old {
        font-size: 24px !important;
    }

    .popup-frame-recommendation .popup-recommendation--body {
        flex-direction: column !important;
        gap: 20px !important;
    }

    .popup-frame-recommendation .popup-recommendation--image {
        width: 100% !important;
        height: 250px !important;
        margin: 0 auto !important;
    }
}

@media (max-width: 480px) {
    .popup-frame-recommendation .popup-recommendation--wrapper {
        padding: 30px 20px !important;
    }

    .popup-frame-recommendation .popup-recommendation--header .h2_old {
        font-size: 20px !important;
    }

    .popup-frame-recommendation .popup-recommendation--actions {
        flex-direction: column !important;
    }

    .popup-frame-recommendation .popup-recommendation--actions a,
    .popup-frame-recommendation .popup-recommendation--actions button {
        width: 100% !important;
        font-size: 15px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    window.openRecommendationModal = function() {
        const modal = document.querySelector('.popup-frame-recommendation');
        if (modal) {
            modal.style.display = 'flex';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeRecommendationModal = function() {
        const modal = document.querySelector('.popup-frame-recommendation');
        if (modal) {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
            document.body.style.overflow = '';
        }
    }

    const popupFrame = document.querySelector('.popup-frame-recommendation');
    if (popupFrame) {
        popupFrame.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRecommendationModal();
            }
        });
    }

    const closeElements = document.querySelectorAll('.popup-recommendation .popup-close');
    closeElements.forEach(function(el) {
        el.addEventListener('click', function() {
            closeRecommendationModal();
        });
    });

    const modalContent = document.querySelector('.popup-recommendation');
    if (modalContent) {
        setTimeout(function() {
            console.log('Opening recommendation modal now');
            openRecommendationModal();
        }, 1000);
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-add-recommendation')) {
            const btn = e.target;
            const itemId = btn.getAttribute('data-item-id');
            const price = btn.getAttribute('data-price');
            const originalText = btn.textContent;

            btn.disabled = true;
            btn.textContent = '@lang("cart_new.adding")...';

            fetch('{{ route("cart.add.recommended") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    item_id: itemId,
                    price: price
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeRecommendationModal();
                    window.location.reload();
                } else {
                    alert(data.message || '@lang("cart_new.error_adding")');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('@lang("cart_new.error_adding")');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }
    });
});
</script>
@endif
