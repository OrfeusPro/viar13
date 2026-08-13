$(function() {


    $(document).on('click', '.mc-active-a, .c-list-suggests > a, .mc-near-points > a', function(e) {
        e.preventDefault();
        $(this).next().fadeToggle();
        $(this).toggleClass('active');
    })



    function changeActiveTab1(
        _this,
        selectorTabWrap,
        selectorTabContent,
        selectorTabContent2,
        selectorTabLink,
        classLinkActive
      ) {
        _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabLink)
          .forEach((element) => {
            element.classList.remove(classLinkActive);
          });
    
        _this.classList.add(classLinkActive);
    
        const indexTab = [..._this.parentElement.children].indexOf(_this);

        const newActiveTabContent = _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent)[indexTab];

          const newActiveTabContent2 = _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent2)[indexTab];


        _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent)
          .forEach((element) => {
            element.classList.add("hidden-block");
          });

          _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent2)
          .forEach((element) => {
            element.classList.add("hidden-block");
          });
    
        newActiveTabContent.classList.remove("hidden-block");
        newActiveTabContent2.classList.remove("hidden-block");
    
    
      }
      // trigger for comments
      document.querySelectorAll(".mctabs li").forEach((element) => {
        element.addEventListener("click", function (e) {
          e.preventDefault();
          let _this = this;
          changeActiveTab1(
            _this,
            ".mcs-inner",
            ".msc-block__item",
            ".title-item",
            ".mctabs li",
            "active"
          );
          
    
          return false;
        });
      });


      if ($(".office-slider").length > 0) {
        $('.office-slider').each(function() {
          $(this).slick({
            infinite: false,
            slidesToShow: 1,
            prevArrow: $(this).next().find(".office-prev"),
            nextArrow: $(this).next().find(".office-next"),
          });
        })
        
      }


      function changeActiveTab(
        _this,
        selectorTabWrap,
        selectorTabContent,
        selectorTabLink,
        classLinkActive
      ) {
        _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabLink)
          .forEach((element) => {
            element.classList.remove(classLinkActive);
          });
    
        _this.classList.add(classLinkActive);
    
        const indexTab = [..._this.parentElement.children].indexOf(_this);
        const newActiveTabContent = _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent)[indexTab];
    
        _this
          .closest(selectorTabWrap)
          .querySelectorAll(selectorTabContent)
          .forEach((element) => {
            element.classList.add("hidden-block");
          });
    
        newActiveTabContent.classList.remove("hidden-block");
    
    
      }
      // trigger for comments
      document.querySelectorAll(".otabs li").forEach((element) => {
        element.addEventListener("click", function (e) {
          e.preventDefault();
          let _this = this;
          changeActiveTab(
            _this,
            ".office__block",
            ".office__block-item",
            ".otabs li",
            "active"
          );
          $('.office-slider').slick('refresh');
        });
      });


      var playVideo = $(".video-btn");
      var playButton = $(".video-btn");
      var video = $("#videoPlayer");
      // Event listener for the play/pause button
      playVideo.click(function () {
        if (video[0].paused == true) {
          // Play the video
          video[0].play();
    
          // Update the button text to 'Pause'
          playButton.fadeOut();
        } else {
          // Pause the video
          video[0].pause();
    
          // Update the button text to 'Play'
          playButton.fadeIn();
        }
      });
})