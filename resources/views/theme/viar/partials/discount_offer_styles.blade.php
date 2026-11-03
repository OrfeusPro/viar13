<style>
/* Collapsed coupon: horizontal on desktop, vertical at the left edge on phones. */
#offer.offer { left: 3.3%; right: auto; bottom: 0; z-index: 19; }
#offer .popup-offer { max-width: calc(100vw - 32px); }
#offer.offer-hidden .popup-offer {
    width: 300px; height: 48px; min-height: 0; padding: 0; overflow: visible;
    transform: none; box-shadow: none; border-radius: 10px 10px 0 0;
}
#offer.offer-hidden .popup-offer::before {
    content: '+'; display: block; visibility: visible; opacity: 1;
    left: 14px; top: -29px; bottom: auto; width: 40px; height: 30px;
    line-height: 30px; font-size: 30px; transform: none;
}
#offer.offer-hidden .hb_popup_coupon > p { display: none; }
#offer.offer-hidden .hb_popup_coupon > .hb_popup_coupon_t1 {
    display: flex; align-items: center; justify-content: center;
    height: 48px; margin: 0 38px; line-height: 1.2;
}
#offer.offer-hidden .hb_popup_coupon_t1 > span { font-size: 24px; white-space: nowrap; }
#offer.offer-hidden .hb_popup_coupon > a { right: 10px; top: 1px; font-size: 36px; line-height: 36px; }
@media (max-width: 700px) {
    #offer.offer { left: 16px; bottom: 20px; }
    #offer.offer-hidden { left: 0; bottom: 72px; }
    #offer.offer-hidden .popup-offer {
        width: 38px; height: 138px; max-width: none; border-radius: 0 9px 9px 0;
    }
    #offer.offer-hidden .popup-offer::before {
        left: 0; top: 8px; width: 38px; height: 26px; line-height: 26px;
        font-size: 26px; border-radius: 0; background: transparent;
    }
    #offer.offer-hidden .hb_popup_coupon > a { display: none; }
    #offer.offer-hidden .hb_popup_coupon > .hb_popup_coupon_t1 {
        position: absolute; left: 0; top: 37px; width: 38px; height: 92px; margin: 0;
        writing-mode: vertical-rl; transform: rotate(180deg);
    }
    #offer.offer-hidden .hb_popup_coupon_t1 > span { font-size: 12px; font-weight: 700; }
}
</style>
