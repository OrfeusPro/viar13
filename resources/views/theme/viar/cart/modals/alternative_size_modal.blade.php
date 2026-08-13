@if(isset($alternativeSizeData) && !empty($alternativeSizeData))
    <div class="vz-art popup-frame popup-frame-alternative-size">
        <div class="vz-art js-popup target-box popup-alternative-size">
            <div class="popup-alternative-size--wrapper">
                <i class="vz-art fa-close popup-close"></i>
                <div class="alt-popup-header">
                    <h2 class="alt-title">@lang('cart_new.alt_modal_title') <br> <span>@lang('cart_new.alt_modal_title_for_you')</span></h2>
                    <p class="alt-subtitle">
                        @lang('cart_new.alt_modal_replace_order')
                        <span class="accent">
                            @lang('cart_new.alt_modal_size') {{ preg_replace('/[hst]$/i', '', $alternativeSizeData['alternative_size']) }}
                        </span>
                        <br>
                        <span class="accent-2">@lang('cart_new.alt_modal_with_discount')</span>
                    </p>
                </div>
                <div class="alt-images">
                    <div class="alt-current">
                        <span class="alt-label">@lang('cart_new.alt_modal_your_order')</span>
                        <div class="alt-img-box">
                            <svg width="78" height="81" viewBox="0 0 78 81" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.655078 9.17578C0.472266 9.25488 0.258984 9.46054 0.167578 9.63457C0.0457031 9.85605 0 19.19 0 40.5475V71.1439L0.350391 71.4762L0.700781 71.8242H39.0152H77.3449L77.6648 71.4604L78 71.0965V40.4684C78 10.5521 78 9.84023 77.7105 9.47636L77.4211 9.09668L39.198 9.06504C17.3672 9.04922 0.853125 9.09668 0.655078 9.17578ZM75.7148 40.5V69.6094H39H2.28516V40.5V11.3906H39H75.7148V40.5Z" fill="#1E2533"/>
                                <path d="M5.68292 14.4281C5.5001 14.523 5.28682 14.7762 5.19542 15.0135C5.07354 15.2982 5.04307 22.8129 5.05831 40.7057L5.10401 66.0023L5.46964 66.3029C5.82003 66.6035 6.56651 66.6035 39.031 66.6035H72.2267L72.5923 66.208L72.9732 65.8283V40.4525C72.9732 15.6937 72.9732 15.0609 72.6837 14.6971L72.3943 14.3174L39.1985 14.2857C16.1489 14.2699 5.91143 14.3174 5.68292 14.4281ZM70.8404 35.216C70.8404 47.0021 70.7794 53.7732 70.688 53.71C70.6118 53.6625 68.7532 51.2578 66.5595 48.3627C64.3657 45.4676 62.37 42.873 62.1111 42.5883C61.5626 41.9871 61.1056 41.9396 60.5571 42.4301C60.3439 42.6357 56.6571 47.4135 52.361 53.0613C48.065 58.7092 44.4849 63.3604 44.4087 63.3762C44.3173 63.4078 38.772 56.1937 32.0689 47.3502C25.381 38.5225 19.7442 31.2135 19.5462 31.1186C19.3025 31.0078 19.0892 31.0078 18.8302 31.1344C18.6474 31.2135 15.9966 34.5832 12.9497 38.5857C9.91807 42.6041 7.4044 45.8789 7.37393 45.8789C7.34346 45.8789 7.313 39.2977 7.313 31.2451V16.6113H39.0767H70.8404V35.216ZM30.5607 49.1537C36.761 57.3012 41.8798 64.0564 41.9407 64.183C42.0474 64.357 39.1985 64.3887 24.6802 64.3887H7.313V57.0639L7.32823 49.7549L13.1935 42.0346C16.4232 37.7947 19.1196 34.3301 19.1958 34.3301C19.2567 34.3301 24.3755 40.9904 30.5607 49.1537ZM66.1025 51.5109L70.8404 57.7758L70.8099 61.0506L70.7642 64.3096L58.7595 64.357C52.1478 64.3729 46.77 64.3254 46.8005 64.2621C46.8919 64.009 61.1665 45.2461 61.2579 45.2461C61.3189 45.2461 63.4974 48.0621 66.1025 51.5109Z" fill="#FA7846"/>
                                <path d="M44.1041 27.132C41.9409 27.7015 40.387 29.0146 39.3815 31.087C37.9799 33.9822 38.4827 37.2411 40.6916 39.5351C43.9366 42.9048 49.2991 42.2878 51.8127 38.2536C53.0619 36.2286 53.2295 33.4759 52.1936 31.2452C50.7311 28.0337 47.2881 26.2777 44.1041 27.132ZM47.9127 29.7265C51.6299 31.6249 51.508 37.1779 47.7147 38.9655C46.6178 39.4718 44.8354 39.4402 43.708 38.9023C42.017 38.0796 40.9963 36.545 40.8592 34.6308C40.585 30.6124 44.4241 27.9388 47.9127 29.7265Z" fill="#1E2533"/>
                            </svg>
                        </div>
                        <div class="alt-old-price">
                            {{ number_format($alternativeSizeData['current_price'], 2) }}€
                        </div>
                    </div>

                    <div class="alt-new">
                        <span class="alt-before">@lang('cart_new.alt_modal_instead_of') <br> <span class="alt-old">{{ number_format($alternativeSizeData['alternative_original_price'] ?? $alternativeSizeData['alternative_price'], 2) }}€</span></span>
                        <div class="alt-img-box-before">
                            <svg width="36" height="34" viewBox="0 0 36 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.2872 0.0930021C18.3309 0.318783 18.7739 -0.0730136 9.34497 8.82543C1.19575 16.5219 0.569969 17.1328 0.373094 17.5313C-0.133156 18.5407 -0.112062 19.4969 0.429344 20.4465C0.738719 20.9977 13.852 33.3758 14.3934 33.6282C15.4129 34.1063 16.3833 34.1129 17.4379 33.6547C17.8668 33.4688 18.4434 32.9375 26.6559 25.1746C34.777 17.4981 35.4309 16.8672 35.6348 16.4688C35.7473 16.2297 35.8809 15.8578 35.9301 15.6387C35.9864 15.3532 36.0004 13.4141 35.9864 8.83207L35.9653 2.42386L35.8036 2.05199C35.452 1.22191 34.7067 0.518001 33.8348 0.18597L33.434 0.0332372L26.5434 0.0199544C21.1856 0.0133131 19.5754 0.0265958 19.2872 0.0930021ZM29.7426 5.45863C30.7481 5.93675 30.3965 7.30472 29.2715 7.30472C28.0973 7.30472 27.8934 5.70433 29.0325 5.37894C29.3208 5.29261 29.4122 5.30589 29.7426 5.45863ZM18.809 7.93558C19.9833 8.22777 20.8903 9.08441 21.1997 10.2067C21.734 12.1457 20.1379 14.0782 18.0004 14.0782C15.0754 14.0782 13.5989 10.7047 15.6731 8.76566C16.5309 7.96879 17.684 7.66332 18.809 7.93558ZM25.2426 16.1235C25.5942 16.2895 25.8051 16.6215 25.8051 17C25.8051 17.3786 25.5942 17.7106 25.2426 17.8766C25.0106 17.9895 24.5184 17.9961 18.0004 17.9961C11.4825 17.9961 10.9903 17.9895 10.7583 17.8766C10.0059 17.5246 10.0059 16.4754 10.7583 16.1235C10.9903 16.0106 11.4825 16.0039 18.0004 16.0039C24.5184 16.0039 25.0106 16.0106 25.2426 16.1235ZM18.7528 19.9883C19.5895 20.1743 20.4754 20.8051 20.8692 21.5024C21.5934 22.7907 21.3895 24.245 20.3278 25.2344C18.4856 26.9477 15.4551 26.1575 14.8012 23.7934C14.3512 22.1731 15.4059 20.4731 17.1567 20.0149C17.5364 19.9153 18.345 19.902 18.7528 19.9883Z" fill="#FC8C5F"/>
                                <path d="M17.4374 9.98086C16.3617 10.5187 16.7835 12.0859 17.9999 12.0859C19.2163 12.0859 19.6382 10.5187 18.5624 9.98086C18.1757 9.78828 17.8241 9.78828 17.4374 9.98086Z" fill="#FC8C5F"/>
                                <path d="M17.5781 21.9605C17.1352 22.1332 16.8047 22.5914 16.8047 23.0297C16.8047 23.4215 17.0719 23.8398 17.4375 24.0191C17.8242 24.2117 18.1758 24.2117 18.5625 24.0191C19.3289 23.6406 19.3992 22.5781 18.6891 22.1066C18.4641 21.9539 17.8102 21.8676 17.5781 21.9605Z" fill="#FC8C5F"/>
                            </svg>
                            <svg width="107" height="104" viewBox="0 0 107 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.898633 11.7812C0.647852 11.8828 0.355273 12.1469 0.229883 12.3703C0.0626953 12.6547 0 24.6391 0 52.0609V91.3453L0.480664 91.7719L0.961328 92.2188H53.5209H106.101L106.54 91.7516L107 91.2844V51.9594C107 13.5484 107 12.6344 106.603 12.1672L106.206 11.6797L53.7717 11.6391C23.8242 11.6187 1.17031 11.6797 0.898633 11.7812ZM103.865 52V89.375H53.5H3.13477V52V14.625H53.5H103.865V52Z" fill="#1E2533"/>
                                <path d="M7.7954 18.525C7.54461 18.6469 7.25204 18.9719 7.12665 19.2766C6.95946 19.6422 6.91766 29.2906 6.93856 52.2641L7.00125 84.7438L7.50282 85.1297C7.98348 85.5156 9.0075 85.5156 53.5421 85.5156H99.0798L99.5814 85.0078L100.104 84.5203V51.9391C100.104 20.15 100.104 19.3375 99.7067 18.8703L99.3097 18.3828L53.772 18.3422C22.1526 18.3219 8.10887 18.3828 7.7954 18.525ZM97.178 45.2156C97.178 60.3484 97.0944 69.0422 96.969 68.9609C96.8646 68.9 94.3149 65.8125 91.3056 62.0953C88.2962 58.3781 85.5585 55.0469 85.2032 54.6813C84.4509 53.9094 83.8239 53.8484 83.0716 54.4781C82.779 54.7422 77.7216 60.8766 71.8282 68.1281C65.9349 75.3797 61.0237 81.3516 60.9192 81.3719C60.7938 81.4125 53.1868 72.15 43.9915 60.7953C34.8171 49.4609 27.0847 40.0766 26.813 39.9547C26.4786 39.8125 26.186 39.8125 25.8307 39.975C25.58 40.0766 21.9436 44.4031 17.764 49.5422C13.6052 54.7016 10.1569 58.9063 10.1151 58.9063C10.0733 58.9063 10.0315 50.4563 10.0315 40.1172V21.3281H53.6048H97.178V45.2156ZM41.9225 63.1109C50.4282 73.5719 57.4501 82.2453 57.5337 82.4078C57.68 82.6313 53.772 82.6719 33.8557 82.6719H10.0315V73.2672L10.0524 63.8828L18.0983 53.9703C22.5288 48.5266 26.2278 44.0781 26.3323 44.0781C26.4159 44.0781 33.4378 52.6297 41.9225 63.1109ZM90.6786 66.1375L97.178 74.1813L97.1362 78.3859L97.0735 82.5703L80.6056 82.6313C71.5356 82.6516 64.1585 82.5906 64.2003 82.5094C64.3257 82.1844 83.9075 58.0938 84.0329 58.0938C84.1165 58.0938 87.105 61.7094 90.6786 66.1375Z" fill="#FA7846"/>
                                <path d="M60.501 34.8359C57.5335 35.5672 55.4018 37.2531 54.0225 39.9141C52.0999 43.6312 52.7895 47.8156 55.8198 50.7609C60.2711 55.0875 67.6274 54.2953 71.0756 49.1156C72.7893 46.5156 73.0192 42.9812 71.5981 40.1172C69.5919 35.9937 64.8688 33.7391 60.501 34.8359ZM65.7256 38.1672C70.8249 40.6047 70.6577 47.7344 65.454 50.0297C63.9493 50.6797 61.5042 50.6391 59.9577 49.9484C57.6379 48.8922 56.2377 46.9219 56.0497 44.4641C55.6735 39.3047 60.9399 35.8719 65.7256 38.1672Z" fill="#1E2533"/>
                            </svg>
                        </div>
                        <div class="alt-price-new">
                            {{ number_format($alternativeSizeData['alternative_price'], 2) }}€
                        </div>
                    </div>
                </div>

                <div class="alt-action">
                    <button type="button"
                            class="alt-btn btn-replace"
                            data-basket-key="{{ $alternativeSizeData['basket_key'] }}"
                            data-item-id="{{ $alternativeSizeData['item_id'] }}"
                            data-new-size="{{ $alternativeSizeData['alternative_size'] }}">
                        @lang('cart_new.alt_modal_use_discount')
                    </button>

                    <button type="button" class="alt-cancel popup-close">
                        @lang('cart_new.keep_current_size')
                    </button>
                </div>

            </div>
        </div>
    </div>

    <style>
        .popup-frame-alternative-size {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .popup-frame-alternative-size .popup-alternative-size.vz-art.js-popup {
            display: block !important;
            visibility: visible !important;
        }

        .popup-alternative-size--wrapper {
            background: #FFF;
            width: 100%;
            border-radius: 22px;
            padding: 40px 30px 35px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            font-family: 'Georgia', serif;
            text-align: center;
        }

        @media (min-width: 1468px) {
            .popup-alternative-size--wrapper{
                min-width: 800px;
            }
        }
        .fa-close.popup-close {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: .2s;
        }
        .fa-close.popup-close:hover {
            transform: rotate(90deg);
            color: #FA7846;
        }

        .alt-title {
            font-size: 24px;
            font-weight: 600;
            color: #d46f42;
            margin: 0;
            line-height: 1.3;
        }
        .alt-title span {
            color: #000;
        }
        .alt-subtitle {
            color: #444;
            font-size: 15px;
            margin-top: 10px;
            line-height: 1.45;
        }
        .accent { color:#d46f42; font-weight:600; }
        .accent-2 { color:#d46f42; font-weight:600; text-decoration: underline; }

        .alt-images {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 35px;
            align-items: center;
        }
        .alt-img-box {
            width: 90px;
            height: 90px;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fafafa;
            position: relative;
        }
        .alt-img-box-before {
            display: flex;
        }
        .alt-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .alt-label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #666;
        }

        .alt-old-price {
            margin-top: 6px;
            font-size: 15px;
            color: #444;
        }

        .alt-before {
            font-size: 14px;
            color: #444;
        }
        .alt-before .alt-old {
            text-decoration: line-through;
        }

        .alt-price-new {
            margin-top: 10px;
            font-size: 26px;
            font-weight: 700;
            color: #d46f42;
        }

        .alt-action {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .alt-btn {
            width: 50%;
            align-self: center;
            padding: 15px 0;
            background: linear-gradient(180deg, #FFA979, #FF8650);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            cursor: pointer;
            transition: .2s;
        }
        .alt-btn:hover { opacity: .9; }

        .alt-cancel {
            width: 50%;
            align-self: center;
            background: #f2f2f2;
            border: none;
            padding: 12px 0;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            color:#666;
        }
        .alt-cancel:hover {
            background:#e2e2e2;
            color:#333;
        }

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            window.openAlternativeSizeModal = function() {
                const modal = document.querySelector('.popup-frame-alternative-size');
                if (modal) {
                    modal.style.display = 'flex';
                    modal.style.visibility = 'visible';
                    document.body.style.overflow = 'hidden';
                }
            }

            window.closeAlternativeSizeModal = function() {
                const modal = document.querySelector('.popup-frame-alternative-size');
                if (modal) {
                    modal.style.display = 'none';
                    modal.style.visibility = 'hidden';
                    document.body.style.overflow = '';
                }
            }

            document.querySelector('.popup-frame-alternative-size')?.addEventListener('click', function(e) {
                if (e.target === this) closeAlternativeSizeModal();
            });

            document.querySelector('.popup-alternative-size .popup-close')?.addEventListener('click', function() {
                closeAlternativeSizeModal();
            });

            if (document.querySelector('.popup-alternative-size')) {
                setTimeout(() => openAlternativeSizeModal(), 800);
            }

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-replace')) {
                    const btn = e.target;
                    btn.disabled = true;
                    btn.textContent = '@lang("cart_new.replacing")...';

                    fetch('{{ route("cart.replace.size") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
                        },
                        body: JSON.stringify({
                            basket_key: btn.dataset.basketKey,
                            item_id: btn.dataset.itemId,
                            new_size: btn.dataset.newSize
                        })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) window.location.href = '{{ route('cart.index') }}';
                            else {
                                alert(data.message || '@lang("cart_new.error_replacing_size")');
                                btn.disabled = false;
                                btn.textContent = '@lang("cart_new.replace_size_button")';
                            }
                        })
                        .catch(() => {
                            alert('@lang("cart_new.error_replacing_size")');
                            btn.disabled = false;
                            btn.textContent = '@lang("cart_new.replace_size_button")';
                        });
                }
            });

        });
    </script>
@endif
