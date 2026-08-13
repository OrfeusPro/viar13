$(document).ready(function () {


  let blogSlider = $(".newone-slider--inner");

  blogSlider.slick({
    infinite: false,
    slidesToShow: 3,
    prevArrow: ".newone-prev",
    nextArrow: ".newone-next",
    responsive: [
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 2,
        },
      },
      {
        breakpoint: 525,
        settings: {
          slidesToShow: 1,
          dots: true
        },
      },
    ],
  });


    $(document).on('click', function(e) {
      let container = $('.f-search');
      let target = $('.search-trigger');
      console.log(e.target)
      if (target.is(e.target) || target.has(e.target).length != 0) {
       target.fadeToggle();
       container.toggleClass('active');
      } else if (!container.is(e.target) && container.has(e.target).length === 0 && container.hasClass('active')) {
        container.toggleClass('active');
        target.fadeIn();
      }
    })



    let cblogSlider = $(".cblog__section-inner");

    cblogSlider.slick({
      infinite: false,
      slidesToShow: 1,
      prevArrow: ".cblog__section-nav .newone-prev",
      nextArrow: ".cblog__section-nav .newone-next",
      responsive: [
        {
          breakpoint: 525,
          settings: {
            dots: true
          },
        },
      ],
    });


    $(document).on('click', function(e) {
      let target = $('.f-trigger');
      let container = $('.f-inner');
      if ((target.is(e.target) || target.has(e.target).length != 0) && !target.hasClass('active'))  {
        target.addClass('active')
        target.next().css("display", "flex")
        .hide().fadeIn();
      } else if (!container.is(e.target) && container.has(e.target).length === 0) {
        target.removeClass('active')
        target.next().css("display", "flex")
        .hide().fadeOut();
      
      }

    })
});
