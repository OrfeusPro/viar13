require('./bootstrap');


$(function() {

    let size_price = 0;
    let size_name = null;

    /* size changing */
    $(document).on('change', '.kviz-c-group input', function(e){
        size_name = $(this).val();
        size_price = $(this).data('sale-price');
    });

});
