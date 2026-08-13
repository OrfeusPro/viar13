$(function() {

    $(document).on('click', '.del_js-select', function() {
        $(this).next().toggleClass('open');
    })

    $(document).on('click', '.countries-list li', function() {
        $('.countries-list li').removeClass('active');
        $(this).addClass('active');
        let text = $(this).find('p').text();
        $('.del_js-select p span').text(text);
        $('.del_js-select').next().removeClass('open');
    })


    $(document).on('click', '.pickup-js', function(e) {
        e.preventDefault();
        $('.pickup-container').slideToggle();
        $(this).closest('.delivery-item').toggleClass('no-border')
    })

    $(document).on('click', '.select-inner', function() {
        $(this).parent().toggleClass('active');
        
    })
    $(document).on('click', '.list-item', function() {
        $('.list-item').removeClass('active');
        $(this).addClass('active');
    })
})