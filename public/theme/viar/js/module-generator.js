$(function () {
    document.querySelectorAll(".mgt-tab").forEach((element) => {
        element.addEventListener("click", function (e) {
            e.preventDefault();
            let _this = this;
            changeActiveTab(
                _this,
                ".mg-types__content",
                ".mg-slider",
                ".mgt-tab",
                "active"
            );
            centerLI(_this, '.mg-types__tabs-wrapper');
        });
    });

    function centerLI(target, outer) {
        var out = $(outer);
        var tar = $(target);
        var x = out.width() - 50;
        var y = tar.outerWidth(true);
        var z = tar.index();
        var q = 0;
        var m = out.find(".mgt-tab");
        for (var i = 0; i < z; i++) {
            q += $(m[i]).outerWidth(true);
        }
        //out.scrollLeft(Math.max(0, q - (x - y)/2));
        out.animate(
            {
                scrollLeft: Math.max(0, q - (x - y) / 2),
            },
            500
        );
    }

    new Swiper(".mg-slider__inner", {
        spaceBetween: 20,
        slidesPerView: 1,
        navigation: {
            nextEl: ".swiper-next",
            prevEl: ".swiper-prev",
        },
    });

    new Swiper(".mg-inspire__slider", {
        spaceBetween: 20,
        slidesPerView: 1.1,
        navigation: {
            nextEl: ".swiper-next",
            prevEl: ".swiper-prev",
        },
        breakpoints: {
            768: {
                slidesPerView: 3,
            },
            551: {
                slidesPerView: 2,
            },
        },
    });

    new Swiper(".similar-slider", {
        spaceBetween: 15,
        slidesPerView: 2,
        navigation: {
            nextEl: ".swiper-next",
            prevEl: ".swiper-prev",
        },
        breakpoints: {
            1080: {
                slidesPerView: 4,
            },
            768: {
                slidesPerView: 2,
            },
        },
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

    $(document).on("click", ".mg-tab__target", function () {
        $(this).next().slideToggle();
    });

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

        let text = $(_this).find(".opens-tab p").text();
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

        if (window.matchMedia("(max-width: 992px)").matches) {
            $(selectorTabWrap).find(".mg-types-text").text(text);
        }
        newActiveTabContent.classList.remove("hidden-block");
    }
});
