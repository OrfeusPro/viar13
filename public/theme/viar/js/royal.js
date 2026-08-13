$(function () {
    $(document).on("click", ".tabs-js", function () {
        $(".tabs-js").removeClass("active");
        $(this).addClass("active");
        let cat = $(this).data("cat");
        if (cat) {
            $(".portrait-list__slider").slick("slickUnfilter");
            $(".portrait-list__slider").slick(
                "slickFilter",
                '[data-cat="' + cat + '"]'
            );
            $(".portrait-list__slider").slick("slickGoTo", 0);
        } else {
            $(".portrait-list__slider").slick("slickUnfilter");
        }
        centerLI(this, ".section-tabs");
    });

    function centerLI(target, outer) {
        var out = $(outer);
        var tar = $(target);
        var x = out.width() - 50;
        var y = tar.outerWidth(true);
        var z = tar.index();
        var q = 0;
        var m = out.find(".tabs-js");
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

    if (window.matchMedia("(min-width: 768px)").matches) {
        setPadding();

        function setPadding() {
            let padding =
                parseInt($(".section-frame:not(.vz-art)").css("margin-left")) +
                20;
            $(".portrait-content").css("padding-left", padding + "px");
        }

        window.onresize = function () {
            setPadding();
        };
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
    document
        .querySelectorAll(".sectionDefault-tabs__item")
        .forEach((element) => {
            element.addEventListener("click", function (e) {
                e.preventDefault();
                let _this = this;
                changeActiveTab(
                    _this,
                    ".service-info",
                    ".service-info__block",
                    ".sectionDefault-tabs__item",
                    "active"
                );

                return false;
            });
        });

    $(document).on("click", ".frame-js-popup", function (e) {
        e.preventDefault();
        let target = $(this)
            .closest(".frame-item")
            .find(".frame-info ul li span:last-child");
        let info = [
            target.eq(1).text(),
            target.eq(2).text(),
            target.eq(3).text(),
            target.eq(4).text(),
            target.eq(5).text(),
            target.eq(6).text(),
        ];
        let form = $(".popup-rframe").find("li span:last-child");
        console.log(form);
        form.eq(1).text(info[0]);
        form.eq(2).text(info[1]);
        form.eq(3).text(info[2]);
        form.eq(4).text(info[3]);
        form.eq(5).text(info[4]);
        form.eq(6).text(info[5]);
        $(".popup-frame").fadeIn();
        $(".popup-rframe").fadeIn();
    });

    // gallery calc
    $(document).on("click", ".frame-item", function () {
        galleryItemsCalculate();
    });

    function calcExecutionPrice(sizeSize, execution) {
        let executionPrice = 0;

        if (execution == 1) {
            let height = sizeSize.split("x")[0];
            let length = sizeSize.split("x")[1];
            let area = (height * length) / 10000;

            if (area <= 0.4) executionPrice = area * 80;
            else if (area > 0.4 && area <= 1) executionPrice = area * 60;
            else if (area >= 1.01) executionPrice = area * 50;
        }

        return executionPrice;
    }

    let galleryCalcTextItem = $(".mcard-price span");
    let typeGallery = $(".gallery-js-type.active").data("price"),
        dataGallery = $(".gallery-js-data.active").data("price"),
        sizeGallery = $(".gallery-js-size li.active input").data("price"),
        frameGallery = 0,
        exectValue = 0;
    var pid = $(".mcard-title").data("pid");
    var name = $(".mcard-title").data("name");
    var price = parseInt(typeGallery) + parseInt(sizeGallery);
    var holst_id = 2;
    var type = $(".gallery-js-type.active").data("text");
    var size = $(".gallery-js-size li.active input").data("size");
    var terms = $(".gallery-js-data.active").data("text");
    var userComment = "";
    var photo_ex = "";
    var dost_time = "";
    var image = null;
    var is_gall_with_img = 0;
    var pack = "РћР±С‹С‡РЅР°СЏ СѓРїР°РєРѕРІРєР°";
    var forma_id = "undefined";
    var users_count = "undefined";
    var decor_id = "undefined";
    var compl_id = "undefined";
    var hud_of = "";
    var ram_id = 12;
    var obraz_img = "";
    var obraz_title = "";


    var form_data = new FormData();

    galleryItemsCalculate();

    function galleryItemsCalculate(exect = null) {
        if ($(".rcard-type__trigger.active").length) {
        }
        frameGallery = $(".frame-item.active").data("price") || 0;
        console.log(frameGallery);
        (typeGallery = $(".gallery-js-type.active").data("price")),
            (dataGallery = $(".gallery-js-data.active").data("price")),
            (sizeGallery = $(".gallery-js-size li.active input").data("price"));
        size = $(".gallery-js-size li.active input").data("size");
        type = $(".gallery-js-type.active").data("text");
        terms = $(".gallery-js-data.active").data("text");
        obraz_img = $('input[name="obraz_img"]').val();
        obraz_title = $('input[name="obraz_title"]').val();
        
        if (!exect) {
            exect = $(".gallery-js-type.active").data("execution");
        }
        price = parseInt(typeGallery) + parseInt(sizeGallery);
        exectValue = calcExecutionPrice(size, exect);
        console.log(size);
        galleryCalcTextItem.text(
            parseInt(typeGallery) +
                parseInt(dataGallery) +
                parseInt(sizeGallery) +
                parseInt(frameGallery) +
                parseInt(exectValue)
        );
    }

    $(document).on("change", ".js_size", function () {
        galleryItemsCalculate();
    });

    $("#buy .mm-btn").on("click", function (e) {
        e.preventDefault();

        form_data.append("pid", pid);
        form_data.append("price", price);
        form_data.append("name", name);
        form_data.append("userComment", userComment);
        form_data.append("photo_ex", photo_ex);
        form_data.append("pack", pack);
        form_data.append("dost_time", dost_time);
        form_data.append("image", image);
        form_data.append("is_gall_with_img", is_gall_with_img);
        form_data.append("forma_id", forma_id);
        form_data.append("size", size);
        form_data.append("users_count", users_count);
        form_data.append("type", type);
        form_data.append("holst_id", holst_id);
        form_data.append("hud_of", hud_of);
        form_data.append("decor_id", decor_id);
        form_data.append("compl_id", compl_id);
        form_data.append("terms", terms);
        form_data.append("obraz_title", obraz_title);
        form_data.append("obraz_img", obraz_img);
        if ($(".frame-item.active").length) {
            form_data.append("ram_id", $(".frame-item.active").data("code"));
            form_data.append("framePrice", frameGallery);
        } else {
            form_data.append("ram_id", ram_id);
        }

        $.ajax({
            type: "POST",
            processData: false,
            contentType: false,
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            url: "/basket/add/portrait",
            data: form_data,
            success: function (response) {
                $("body").addClass("open-frame");
                $(".popup-frame").css("display", "flex").hide().fadeIn();
                $(".popup-cart").fadeIn();
            },
        });
    });

    $(document).on("click", ".frame-item", function () {
        let status = $(this).hasClass('active');
        if (status) {
            $(this).removeClass('active');
        } else {
            $('.frame-item').removeClass('active');
            $(this).addClass('active');
        }
        // $(".frame-item").removeClass("active");
        let fWidth = parseInt(this.dataset.width);
        console.log('hisdfsdf')
        if (this.dataset.src) {
            console.log(typeof fWidth);
            //gen.setRam(this.dataset.src, fWidth);
        } else {
            //gen.setRam();
        }
    });

    //

    $(document).on("click", ".mc-js-filter > a", function (e) {
        e.preventDefault();
        $(".mc-js-filter ").removeClass("active");
        $(".filter-item--wrapper").fadeOut();
        $(this).parent().addClass("active");
        $(this).next().fadeIn();
    });

    $(document).on("click", function (e) {
        let container = $(".filter-item");
        let target = $(".c-sizeCheck input");

        if (target.is(e.target)) {
        } else if (
            !container.is(e.target) &&
            container.has(e.target).length === 0
        ) {
            $(".mc-js-filter .filter-item--wrapper").fadeOut();
            $(".filter-item").removeClass("active");
        } else {
            if (
                $(e.target).closest(".filter-item").find(".filter-sort")
                    .length != 0
            ) {
                if (
                    $(".sort-it li").has(e.target) ||
                    $(".sort-it li").is(e.target)
                ) {
                    let text = $(e.target)
                        .closest(".sort-it li")
                        .find("span")
                        .text();
                    if (text) {
                        $("#isort").text(text);
                        $("#isort2").text(text);
                        $(e.target)
                            .closest(".sort-it")
                            .find("li")
                            .removeClass("active");
                        $(e.target).closest(".sort-it li").addClass("active");
                    }
                }
            } else if (
                $(e.target).closest(".filter-item").find(".filter-material")
                    .length != 0
            ) {
                if (
                    $(".filter-material li").has(e.target) ||
                    $(".filter-material li").is(e.target)
                ) {
                    $(e.target)
                        .closest(".filter-material")
                        .find("li")
                        .removeClass("active");
                    $(e.target)
                        .closest(".filter-material li")
                        .addClass("active");
                    filterRam();
                }
            }
        }
    });

    // region Frames
    $(document).on("click", ".color-grid li", function (e) {
        $(".color-grid li").removeClass("active");
        $(this).addClass("active");
        filterRam();
    });

    $(document).on("click", ".mc-search button", function (e) {
        e.preventDefault();
        filterRam();
    });

    $(document).on("keyup", ".mc-search input", function (e) {
        e.preventDefault();
        filterRam();
    });

    function filterRam() {
        let color = $(".color-grid li.active").data("ramcolorid");
        let material = parseInt(
            $(".filter-material li.active").data("rammaterialid")
        );
        let search = $("#ram_search").val();
        var form_data = new FormData();
        form_data.append("ramcolorid", color);
        form_data.append("rammaterialid", material);
        form_data.append("search_id", search);
        $.ajax({
            type: "POST",
            processData: false,
            contentType: false,
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            url: "/get/ram_search",
            data: form_data,
            success: function (response) {
                console.log(response);
                let content = "";
                response.forEach((el, i) => {
                    content += `<div class="frame-item" data-code="${
                        el.id
                    }" data-price="${parseInt(el.price)}" data-src="${
                        el.img
                    }" data-width="${el.width}">
              <picture>
              <source srcset="${el.img}" type="image/jpeg">
              <img width="150" height="150" src="${
                  el.img
              }" alt="Viar" loading="lazy">
              </picture>
              <div class="fi-info">
              <p>
              ${el.lng_id}
              <span>${el.id}</span>
              </p>
              <b>+${el.price}</b>
              </div>
              <div class="frame-info">
              <ul>
              <li>
              <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_261_1144)">
              <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
              </g>
              <defs>
              <clipPath id="clip0_261_1144">
              <rect width="8" height="8" fill="white"></rect>
              </clipPath>
              </defs>
              </svg>
              <span>${el.lng_stock}</span>
              </li>
              <li>
              <span>${el.lng_id}</span>
              <span>${el.id}</span>
              </li>
              <li>
              <span>${el.lng_color}</span>
              <span>${el.color}</span>
              </li>
              <li>
              <span>${el.lng_width}</span>
              <span>${el.height}</span>
              </li>
              <li>
              <span>${el.lng_height}</span>
              <span>${el.height}</span>
              </li>
              <li>
              <span>${el.lng_id}</span>
              <span>${el.lng_material}</span>
              </li>
              <li>
              <span>${el.lng_price}</span>
              <span>${el.price}</span>
              </li>
              </ul>
              </div>
              <div class="frame-js-popup">
              <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="14" cy="14" r="14" fill="#FA7846"></circle>
              <g clip-path="url(#clip0_877_8)">
              <path d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z" fill="white"></path>
              <rect width="6.26573" height="0.68813" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)" fill="white"></rect>
              <rect width="6.26574" height="0.658255" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)" fill="white"></rect>
              </g>
              <defs>
              <clipPath id="clip0_877_8">
              <rect width="15" height="15" fill="white" transform="translate(7 7)"></rect>
              </clipPath>
              </defs>
              </svg>
              </div>
              <a href="#" class="mf-popup frame-js-popup">
              More details </a>
              </div>`;
                });
                $(".frames-list").html(content);
                if (color) {
                    $(".mc-f-selected[data-id='1'").remove();
                    addRamFilterItem(response[0].color, 1);
                }
                if (material) {
                    $(".mc-f-selected[data-id='2'").remove();
                    addRamFilterItem($(".filter-material li.active").text(), 2);
                }
            },
        });
    }

    function addRamFilterItem(name, id) {
        console.log("here");
        let filterItems = $(".rc-f-selected-container");
        filterItems.append(
            `
                <div class="mc-f-selected" data-id="${id}">
                  <div>
                    ${name}
                  </div>
                  <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="10.4429" height="0.870241"
                      transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                    <rect width="10.4429" height="0.870241"
                      transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                  </svg>
                </div>
      `
        );
    }

    //endregion

    $(document).on("click", ".js-image-calc", function (e) {
        e.preventDefault();
        $(".image-calculator").show();
        showNewPopup();
    });

    $(document).on("click", ".js-simps-calc", function (e) {
        e.preventDefault();
        $(".simpson-calculator").show();
        showNewPopup();
    });

    function showNewPopup() {
        $("body").addClass("fixed");
        $(".popup-wrapper").addClass("active");
    }

    function closeNewPopup() {
        $("body").removeClass("fixed");
        $('.popup-item').hide();
        !$('.formalization__subrow').is(":visible") ? $('.formalization__subrow').show() : null;
        $(".popup-wrapper").removeClass("active");
    }

    $(document).on("click", ".popup-layout, .close-btn", closeNewPopup);

    $(".sp-slider").slick({
        dots: false,
        prevArrow: $(".simpson-pattern .examples-prev"),
        nextArrow: $(".simpson-pattern .examples-next"),
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 991,
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 2,
                },
            },
        ],
    });

    document.querySelectorAll(".frame-trigger-tab").forEach((element) => {
        element.addEventListener("click", function (e) {
            e.preventDefault();
            let _this = this;
            changeActiveTab(
                _this,
                ".frame-outerContainer",
                ".frame-wrapper",
                ".frame-trigger-tab",
                "kviz-radio_active"
            );

            return false;
        });
    });

    $(".beforeAfter__slider-inner").slick({
        dots: false,
        prevArrow: $(".beforeAfter__slider .examples-prev"),
        nextArrow: $(".beforeAfter__slider .examples-next"),
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        draggable: false,
        autoplay: true,
    });

    $(".shCat-desk-slider").slick({
        dots: false,
        prevArrow: $(".shCat-slider-wrapper .examples-prev"),
        nextArrow: $(".shCat-slider-wrapper .examples-next"),
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
    });
    $(".mob-shCat").slick({
        dots: false,
        prevArrow: $(".shCat-slider-wrapper .examples-prev"),
        nextArrow: $(".shCat-slider-wrapper .examples-next"),
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
    });

    $(".portraitNew-slider").slick({
        dots: false,
        prevArrow: $(".portraitNew-slider-wrapper .examples-prev"),
        nextArrow: $(".portraitNew-slider-wrapper .examples-next"),
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 991,
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 2,
                },
            },
            {
                breakpoint: 525,
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    });

    $(".portrait-list__slider").slick({
        dots: false,
        prevArrow: $(".portrait-list__pagination .prev"),
        nextArrow: $(".portrait-list__pagination .next"),
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 991,
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 2,
                },
            },
        ],
    });
});
