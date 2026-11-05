<style>
.cart-notice { box-sizing: border-box; width: min(460px, calc(100% - 32px)); margin: auto; border: 0; border-radius: 16px; padding: 30px; color: #292929; background: #fffaf4; box-shadow: 0 16px 60px #0003; font-family: inherit; }
.cart-notice::backdrop { background: #0006; }
.cart-notice h2 { margin: 0 0 14px; font-size: 22px; line-height: 1.3; }
.cart-notice p { margin: 0 0 24px; font-size: 16px; line-height: 1.6; }
.cart-notice button { width: 100%; border: 0; border-radius: 8px; padding: 14px 20px; background: #e88a51; color: white; cursor: pointer; font: inherit; font-weight: 600; }
.cart-notice button:focus-visible { outline: 3px solid #733b19; outline-offset: 3px; }
</style>
<dialog id="cart-notice" class="cart-notice" aria-labelledby="cart-notice-title" aria-describedby="cart-notice-message">
    <h2 id="cart-notice-title">@lang('cart_new.notice_title')</h2>
    <p id="cart-notice-message"></p>
    <form method="dialog"><button type="submit">@lang('cart_new.notice_close')</button></form>
</dialog>
<script>
window.showCartNotice = function (code, message) {
    var dialog = document.getElementById('cart-notice');
    var messages = @json(['cannot_remove_main_product' => trans('cart_new.cannot_remove_main_product')]);
    document.getElementById('cart-notice-message').textContent = messages[code] || message;
    if (!dialog.open) dialog.showModal();
};
</script>
