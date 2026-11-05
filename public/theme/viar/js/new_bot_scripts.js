    //close modal
    $(document).on('click', '.close-content', function (e) {
        $(this).closest('.popup').removeClass('active');
        $('body').removeClass('open-frame');
    });

    var calc_float_val = function(t) {
        var a = Number(t);
        return parseFloat(a).toFixed(2)
    };

      (function($){

        $.fn.jmspinner = function(value){
          var small = 'small';
          var custom = 'custom';
          var large = 'large';
          var div_bounces ='';
          var div  = document.createElement('div');
          var plc = $(div).prop('class', 'spin_loading');
          var inner = document.createElement('div');
          var center_loading = $(inner).prop('class', 'spinner');
          var made = $(plc).html(center_loading);
          var bce1 = document.createElement('div');
          var bce2 = document.createElement('div');
          var bce3 = document.createElement('div');
          var div_btn_1 = $(bce1).prop('class', 'bounce1');
          var div_btn_2 = $(bce2).prop('class', 'bounce2');
          var div_btn_3 = $(bce3).prop('class', 'bounce3');
          // returning the bounce divs to the template

          //var div_inner_bounces = $(div_bounces).html(div_btn);
          var divs_bts;
          var index = 0;
          var loading =  [];
          loading.push(div_btn_1,div_btn_2, div_btn_3);


          $.each(loading, function(i, index){

            divs_bts = $(center_loading).append(index);

          });

          if(value == 'small'){
            var small = $(divs_bts).addClass('small');
             this.html(small);
             return this;
          }
          if(value == 'large'){
            var large = $(divs_bts).addClass('large');
             this.html(large);
             return this;
          }
          if(value == null){
            var detf = $(divs_bts).addClass('default');
             this.html(detf);
             return this;
          }

          if(value == false){
             this.find('.spinner').remove();
             return this;
          }


        }

      }(jQuery));


    function dataURItoBlob(t) {
        var a, e;
        a = -1 !== t.split(",")[0].indexOf("base64") ? atob(t.split(",")[1]) : decodeURI(t.split(",")[1]), e = t.split(
            ",")[0].split(":")[1].split(";")[0];
        for (var s = new Array, i = 0; i < a.length; i++) s[i] = a.charCodeAt(i);
        return new Blob([new Uint8Array(s)], {
            type: e
        })
    }


    $(function() {
        var $q = function(q, res) {
                if (document.querySelectorAll) {
                    res = document.querySelectorAll(q);
                } else {
                    var d = document,
                        a = d.styleSheets[0] || d.createStyleSheet();
                    a.addRule(q, 'f:b');
                    for (var l = d.all, b = 0, c = [], f = l.length; b < f; b++)
                        l[b].currentStyle.f && c.push(l[b]);

                    a.removeRule(0);
                    res = c;
                }
                return res;
            },
            addEventListener = function(evt, fn) {
                window.addEventListener ?
                    this.addEventListener(evt, fn, false) :
                    (window.attachEvent) ?
                    this.attachEvent('on' + evt, fn) :
                    this['on' + evt] = fn;
            },
            _has = function(obj, key) {
                return Object.prototype.hasOwnProperty.call(obj, key);
            };

        function loadImage(el, fn) {
            var img = new Image(),
                src = el.getAttribute('data-src');
            img.onload = function() {
                if (!!el.parent)
                    el.parent.replaceChild(img, el)
                else
                    el.src = src;

                fn ? fn() : null;
            }
            img.src = src;
        }



    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var csrfToken = $('[name="csrf_token"]').attr('content');

    setInterval(refreshToken, 3600000); // 1 hour

    function refreshToken() {
        $.get('refresh-csrf').done(function(data) {
            csrfToken = data; // the new token
        });
    }

    setInterval(refreshToken, 3600000); // 1 hour

    $(document).ajaxComplete(function(event, xhr, settings) {
        if (xhr.status == 400) {
            location.reload();
        }
    });


        window.onShowInterior = function() {
            return {
                size: {
                    h: size_centimeter_h,
                    w: size_centimeter_w
                },
                src: editor.out(editor.canvas.width, editor.canvas.height, false)
            };
        }

      var CanvasMainImg = null;
      var canvasUploadSerial = 0;
      var base_64_img = null;
      var saved_tab2_img;
      var preview,file,reader;
      var dost_price = 0;

      $('.js-popup-photo').click(function (e) {
          e.preventDefault();
          $('body').addClass('open-frame');
          $('.popup-frame').css("display", "flex").hide().fadeIn();
          $('.popup-photo').fadeIn();
      });

      $(document).on("click", "#interior_zoom_plus1", function () {
          $('#interior_zoom_plus').trigger('click');
      });

      $(document).on("click", "span.delete", function () {
          var btn = $(this).closest('button');
          var eq = btn.index();
          btn.removeClass('is-load');
          btn.find('img').attr('src', '(unknown)').removeAttr('style');
          btn.find('input').val('');

          if (!$('#imgs button.is-load').length) {
              $('.additional-image').addClass('additional-image_pending').removeClass('additional-image_loaded');
          }
      });

      // $(".slider-tabs").on("click", ".slider-tabs a", function () {
      //     // $(".ramu-slider").get(0).slick.setPosition(),
      //     $(".ramu-item .img").matchHeight({
      //         byRow: !1,
      //     });
      // }),

      $(document).on('click', '.interior-item', function(e){
          var size = $(this).data('size');
          var size = size.split(',');

          $('.js_s1').text(size[0]);
          $('.js_s2').text(size[1]);

          var int = $(this).data('interior');
          $('.tab2-item-child').children('img').attr('src', int);

          var index = $(this).data('item');

          $('.range__box').hide(0);
          $('.range__box').eq(index).show(0);
      });

      $('input[name="wall_size"]').on('keyup',function(){
          v = parseInt($(this).val());
          min = parseInt($(this).attr('min'));
          max = parseInt($(this).attr('max'));
          if (v > max){
              $(this).val(max);
          }
          if(v<min){
              $(this).val(min);
          }

      })

      $('.interior-sizes input').on('input', function(e){
          var val = this.value;
          $('.size-item').find('input').val(this.value);
          var cacl_val = this.value/30;
          $('.int-draggable').css({ 'width': 'calc(230px - ' + cacl_val+ '%)' });
      });

      function previewFile() {
          preview = document.getElementById('img__selected__tab1');
          file    = document.getElementById('user_image').files[0];
          reader  = new FileReader();

          reader.onloadend = function () {
              preview.src = reader.result;
          }

          if (file) {
              reader.readAsDataURL(file);
          } else {
              preview.src = "";
          }
      }

    document.addEventListener('click',function(e){var link=e.target.closest('.popup-cart .go_to_cart');if(!link)return;e.preventDefault();e.stopImmediatePropagation();window.location.assign(link.href);},true);
    function showCanvasOrderError(message) {
        var labels=document.getElementById('calculator-labels');
        var box=document.getElementById('canvas-order-error');
        if(!box){box=document.createElement('p');box.id='canvas-order-error';box.setAttribute('role','alert');document.getElementById('t3_submit_btn').before(box);}
        box.textContent=message || (labels && labels.dataset.orderError) || 'Unable to add item. Please try again.';
        box.scrollIntoView({block:'center'});
    }
    var canvasRequestId=null;
    // SUBMIT
    $(document).on('click', '#t3_submit_btn', function (e) {
        e.preventDefault();

        if (document.getElementById('imgs').dataset.uploadBusy==='true' || !editor.grid[0].resource || !CanvasMainImg) { $('.popup-inv-size').addClass('active'); return; }
        $('.popup-inv-size').removeClass('active');
        var $btn = $(this);
        if ($btn.data('processed') === true) return;

        $btn.data('processed', true).attr('aria-busy','true').prop('disabled',true);

        var $loading = $('.loading-bar');
        $loading.stop(true, true).fadeOut(0);

        // Recompute synchronously before capturing the order total.
        calcTotalPrice();
        // собираем данные
        var data = formData();
        if(!canvasRequestId)canvasRequestId=crypto.randomUUID?crypto.randomUUID():Date.now().toString(36)+'-'+Math.random().toString(36).slice(2);
        data.append('canvas_request_id',canvasRequestId);
        data.append('pid', $btn.data('pid'));

        // аккуратно читаем цену
        var priceEl = document.querySelector('.totalPriceNew');
        var priceFromNew = (priceEl && priceEl.dataset) ? priceEl.dataset.total : null;
        var priceFromOld = $('#totalPrice').data('total');
        data.set('price', priceFromNew ?? priceFromOld ?? 0);

        // язык
        var lang = document.documentElement.lang || 'ru';
        if (lang === 'et') lang = 'ee';

        // спиннер
        $('.js_spinner').jmspinner('large');

        $.ajax({
            type: 'POST',
            url: '/' + lang + '/basket/add',
            data: data,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

            beforeSend: function () {
            // показываем без смены flex на block
            $loading.stop(true, true).css('display', 'flex').hide().fadeIn(150);
            },

            success: function (response) {
            if (!response) return;

            // если пришла строка — пробуем распарсить
            if (typeof response === 'string') {
                try { response = JSON.parse(response); } catch (e) {}
            }

            if (response.Error) {
                alert(response.Error);
                return;
            }

            if (response.success) {
                document.dispatchEvent(new Event('canvas-cart-added'));
                canvasRequestId=null;
                $('.popup-cart .go_to_cart').removeClass('js-popup-repair-basket');
                var orderError=document.getElementById('canvas-order-error');if(orderError)orderError.remove();
                $('.popup-cart').attr({'role':'dialog','aria-modal':'true','aria-label':response.message||'Added to cart'});
                // SEO-случай (оставлено как у вас, добавлены защиты от NaN)
                var totalSeoPrice =
                Number($('.tabs-item #totalPrice').data('total')) ||
                Number((document.querySelector('.totalPriceNew') || {}).dataset?.total) ||
                0;

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                ecommerce: {
                    currencyCode: 'EUR',
                    add: {
                    actionField: { list: 'Kanvas' },
                    products: [{ name: 'Kanvas', price: totalSeoPrice, quantity: 1 }]
                    }
                },
                event: 'EE-event',
                'EE-event-category': 'Enhanced Ecommerce',
                'EE-event-action': 'Add To Cart',
                'EE-event-non-interaction': 'False'
                });

                // UI
                $('body').addClass('open-frame');
                $('.popup-frame').css('display', 'flex').hide().fadeIn();
                $('.popup-cart').fadeIn(function(){var target=this.querySelector('.go_to_cart,button,a[href]');if(target)target.focus({preventScroll:true});});
                $loading.stop(true, true).fadeOut(150);

                var cur = parseInt($('#smallCart').text(), 10);
                cur = isNaN(cur) ? 1 : cur + 1;
                $('#smallCart').text(Number.isFinite(Number(response.count)) ? Number(response.count) : cur);
                var cartLink=document.querySelector('.header-cart');
                if(cartLink && Number.isFinite(Number(response.count))) {
                    var badge=cartLink.querySelector('.canvas-cart-count');
                    if(!badge){badge=document.createElement('span');badge.className='canvas-cart-count';badge.setAttribute('aria-live','polite');cartLink.appendChild(badge);}
                    badge.textContent=String(response.count);
                }
            }

            $('#t1_submit_btn').attr('data-disabled', '0');
            $('.images-container').html('');
            $('.file-input').val('');
            },

            error: function (error) {
            $loading.stop(true, true).fadeOut(150);

            var message='';
            if(error && error.status===422) {try {message=JSON.parse(error.responseText).message||'';}catch(ignore){}}
            showCanvasOrderError(message);

            },

            complete: function () {
            // ВСЕГДА снимаем флаг и прячем спиннер/лоадер
            $btn.data('processed', false).removeAttr('aria-busy').prop('disabled',false);
            $('.js_spinner').jmspinner(false);
            $loading.stop(true, true).fadeOut(150);
            }
        });
    });


      // выберите размер
      $(document).on('click', '.js_add_basket_module', function(e){
          $('#t1_submit_btn').trigger('click');
      });

      $(document).on('click', '.calcSize', function(e){
          $('.accordion__sizes-block .calcSize').removeClass('kviz-radio_active');
          $(this).addClass('kviz-radio_active').find('input[name="size"]').prop('checked',true);
          let sizePrice = $(this).data('price');
          let sizeSize = $(this).data('size');
          calcTotalPrice(sizePrice, sizeSize);
      });

      // Card visuals and native values must agree before pricing/submission.
      $(document).on('click', '#generate.calculator-v2 .kviz-c-group .kviz-radio', function(e){
          if($(e.target).closest('[role="button"],.pic-pop').length)return;
          var input=$(this).find('input[type="radio"]').first();
          if(!input.length||input.prop('disabled')||input.attr('name')==='size')return;
          input.prop('checked',true).trigger('change');
      });
      $('.calculator-v2 .kviz-radio_active input[type="radio"]').each(function(){
          if(this.name==='decoration'&&!$('input[name="decoration"]:checked').length)this.checked=true;
      });
      $('#generate .additional-image .popup-log-check').on('click', function(){
          var toggle=this;
          setTimeout(function(){
              $(toggle).find('input[name="save"]').prop('checked',$(toggle).hasClass('log-check_active'));
              calcTotalPrice();
          },0);
      });
      $('input[name="save"]').on('change',function(){calcTotalPrice();});
      function photoImprovementEnabled(){return $('#generate .additional-image .popup-log-check').hasClass('log-check_active');}

      // тип исполнения
      $('.calcExecution').on('click', function () {
          calcTotalPrice(null, null, $(this).data('execution'));
      });

      // вид холста
      $('input[name="canvas_type"]').on('change', function () {
          calcTotalPrice();
      });

      // худ. оформление
      $('input[name="decoration"]').on('change', function () {
          calcTotalPrice();
      });

      $('input[name="boxes[]"]').on('change', function () {
          calcTotalPrice();
      });
      $('input[name="boxes2[]"]').on('change', function () {
          calcTotalPrice();
      });

      // доставка
      $('input[name="dost_time"]').on('change', function () {
          calcTotalPrice();
      });

      function formData() {
          let formData = new FormData();
          let userImage = CanvasMainImg;
          let formId = $('.calcForm.active').data('id');
          let sizeId = $('.accordion__sizes-block:not(.hidden-block) .calcSize.kviz-radio_active').data('size');
          let priceLabel = $('.accordion__sizes-block:not(.hidden-block) .calcSize.kviz-radio_active').data('price-label');
          let canvasId = $('input[name="canvas_type"]:checked').data('id');
          let executionId = $('.calcExecution.active').data('execution');
          let decorationId = $('input[name="decoration"]:checked').data('id');
          let userComment = $('#userComment').val();
          if(editor.getCanvasConfiguration)formData.append('canvas_configuration',JSON.stringify(editor.getCanvasConfiguration()));
          let photoComment = $('#preparationComment').val();
          let boxIds = [];
          // Basket localizes the production term from its numeric price, not its label.
          let terms = String($('.delivery-inputs input[name="time"]:checked').val() || '0');
          let terms_price = $('.delivery-inputs .kviz-radio_active').find('input').val();

          let Image3d = '';
          let canvas = document.getElementById("canvas");

          if (canvas && canvas.width > 0 && canvas.height > 0) {
              let resizedCanvas = document.createElement("canvas");
              let resizedContext = resizedCanvas.getContext("2d");
              resizedCanvas.height = 600;
              resizedCanvas.width = 600;
              resizedContext.imageSmoothingQuality = 'high';
              resizedContext.drawImage(canvas, 0, 0, 600, 600);
              Image3d = resizedCanvas.toDataURL('image/jpeg', 1.0);
          } else if (typeof editor !== 'undefined' && editor && typeof editor.out === 'function') {
              try {
                  Image3d = editor.out(600, 600, false);
              } catch (e) {
                  Image3d = '';
              }
          }

          $('input[name="boxes[]"]:checked').each(function () {
              boxIds.push(parseInt($(this).data('id')));
          });


          formData.append('improvement_id', photoImprovementEnabled()?($('input[name="boxes2[]"]:checked').data('id')||0):0);
          formData.append('improve_photo', photoImprovementEnabled()?($('input[name="boxes2[]"]:checked').data('name')||''):'');
          const totalFromData = parseFloat($('.totalPriceNew').attr('data-total'));
          const totalFromLegacy = parseFloat($('.tabs-item #totalPrice').attr('data-total'));
          const totalFromText = parseFloat(($('.totalPriceNew').text() || '').replace(',', '.'));
          const normalizedTotal = Number.isFinite(totalFromData)
              ? totalFromData
              : (Number.isFinite(totalFromLegacy) ? totalFromLegacy : (Number.isFinite(totalFromText) ? totalFromText : 0));
          formData.append('price', normalizedTotal.toFixed(2));

          if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"]').val() != ''){
              formData.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
          }else{
              formData.append('photo_ex', '');
          }

          if($('.ramu-item.active').length){
              var $activeRam = $('.ramu-item.active').last();
              var activeRamPrice = parseFloat($activeRam.data('price')) || 0;
              var calculatorRoot=document.querySelector('#generate.calculator-v2');
              var selectedSize=document.querySelector('.calcSize.kviz-radio_active');
              var ratesNode=document.getElementById('calculator-frame-rates');
              var orderFrameRates=ratesNode?JSON.parse(ratesNode.textContent):{};
              var orderFrameSignature=$activeRam.data('id')+':'+(selectedSize?selectedSize.dataset.size:'')+':'+Number(orderFrameRates[$activeRam.data('id')]||0);
              var frameConfirmed=!calculatorRoot||calculatorRoot.dataset.confirmedFrame===orderFrameSignature;
              if((activeRamPrice > 0||orderFrameRates[$activeRam.data('id')]>0)&&frameConfirmed){
                  var ram_id = $activeRam.data('id') || $activeRam.find('input[name="ram_id"]').val();
              }
          }

          formData.append('basketType', '1');

          if ($('input[name="pid"]').length && $('input[name="pid"]').val()) {
              formData.append('pid', $('input[name="pid"]').val());
          }

          if (userImage)
              formData.append('userImage', userImage);
          if (Image3d)
              formData.append('Image3d', Image3d);
          if (formId)
              formData.append('formId', formId);
          if (sizeId) formData.append('sizeId', sizeId);
          if (priceLabel) formData.append('priceLabel', priceLabel);

          formData.append('name', 'Canvas');

          if (ram_id)
              formData.append('ram_id', ram_id);
          if (executionId)
              formData.append('executionId', executionId);
          if (canvasId)
              formData.append('canvasId', canvasId);
          if (decorationId)
              formData.append('decorationId', decorationId);
          if (userComment || photoComment)
              formData.append('userComment', [userComment, photoComment].filter(Boolean).join('\n'));
          if (photoComment) formData.append('photoComment', photoComment);
          if (terms)
              formData.append('terms', terms);
          if (terms_price)
              formData.append('terms_price', terms_price);
          if (boxIds)
              formData.append('boxIds', JSON.stringify(boxIds));

          formData.append('is_canvas_collage', 1);
          formData.append('is_with_orig_image', 1);

          return formData;
      }

      function calcExecutionPrice(sizeSize, execution) {
          let executionPrice = 0;

          if (execution == 1) {
              let height = sizeSize.split('x')[0];
              let length = sizeSize.split('x')[1];
              let area = height * length / 10000;

              if (area <= 0.4)
                  executionPrice = area * 80;
              else if (area > 0.4 && area <= 1)
                  executionPrice = area * 60;
              else if (area >= 1.01)
                  executionPrice = area * 50;
          }

          return executionPrice;
      }

      var rp = 0;

      function calcTotalPrice(sizePrice = null, sizeSize = null, execution = null) {
          let $activeSize = $('.accordion__sizes-block:not(.hidden-block) .calcSize.kviz-radio_active');
          if (!$activeSize.length) {
              $activeSize = $('.calcSize.kviz-radio_active').first();
          }

          sizePrice = $activeSize.data('price');

          if (!sizeSize) {
              sizeSize = $activeSize.data('size');
          }


          if (!execution) {
              execution = $('.calcExecution.active').data('execution');
          }

          let dostTimePrice = parseFloat(dost_price);

          let totalPrice = 0;

          totalPrice += parseFloat(sizePrice);



          var ram_price = $('.ramu-item.active').data('price');



          var cur_size  = sizeSize;



          var cur_size_calc = cur_size.split("x");



          var s1 = parseInt(cur_size_calc[0]);
          var s2 = parseInt(cur_size_calc[1]);



          if(typeof ram_price === 'undefined'){
              rp = 0;
          }else{
              var ratesNode=document.getElementById('calculator-frame-rates');
              var frameRates=ratesNode?JSON.parse(ratesNode.textContent):{};
              var meterRate=frameRates[$('.ramu-item.active').data('id')];
              rp=meterRate!=null?Math.round(2*(Number(cur_size_calc[0])+Number(cur_size_calc[1]))/100*Number(meterRate)*100)/100:parseFloat(ram_price);
              // if($('.calcSize.kviz-radio_active'.length)){
              //     rp = ((s1+s1+s2+s2)/100)*parseInt(ram_price);
              // }else{
              //     rp  = 0;
              // }
          }


          // холст
         // let defPrice = $('.calcSize.kviz-radio_active').data('price');
          let defPrice = totalPrice;



          let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());

          if (canvasTypePrice == null || isNaN(parseFloat(canvasTypePrice))) {
              canvasTypePrice=1
          }



          let cur_sizes_add_coef = defPrice * canvasTypePrice;



          let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);



          totalPrice = cur_sizes_add;



          totalPrice = parseFloat(totalPrice) + parseFloat(dostTimePrice);
          totalPrice = parseFloat(totalPrice).toFixed(2);



          // тип исполнения
          let ex_add_price = calcExecutionPrice(sizeSize, execution);




          // console.log('+ за тип исполнения',ex_add_price);
          totalPrice = parseFloat(totalPrice) + parseFloat(ex_add_price);
          totalPrice = parseFloat(totalPrice).toFixed(2);

          // худ. оформление
          totalPrice = parseFloat(totalPrice).toFixed(2);




          let coef_1 = parseFloat($('input[name="decoration"]:checked').data('coef_sm'));
          let coef_2 = parseFloat($('input[name="decoration"]:checked').data('coef_md'));
          let coef_3 = parseFloat($('input[name="decoration"]:checked').data('coef_lg'));
          let area = s1 * s2 / 10000;
          let decorAddPrice = 0;

          if (area <= 0.4 && Number.isFinite(coef_1)) {
              decorAddPrice = area * coef_1;
          }
          else if (area > 0.4 && area <= 1 && Number.isFinite(coef_2)) {
              decorAddPrice = area * coef_2;
          }
          else if (area >= 1.01 && Number.isFinite(coef_3)) {
              decorAddPrice = area * coef_3;
          }

          if(isNaN(decorAddPrice)){
              decorAddPrice = 0;
          }
          if (decorAddPrice === 0) {
              const decorFallbackPrice = parseFloat($('input[name="decoration"]:checked').val());
              if (Number.isFinite(decorFallbackPrice)) {
                  decorAddPrice = decorFallbackPrice;
              }
          }



          decorAddPrice = parseFloat(decorAddPrice);



          totalPrice = parseFloat(totalPrice);

          totalPrice += decorAddPrice;

          const boxImprovePrice = parseFloat($('input[name="boxes[]"]:checked').val());
          if (Number.isFinite(boxImprovePrice)) {
              totalPrice = parseFloat(totalPrice) + boxImprovePrice;
          }



          var picture_improve = photoImprovementEnabled()?$('input[name="boxes2[]"]:checked').val():0;
          var pictureImprovePrice = parseFloat(picture_improve);
          if (Number.isFinite(pictureImprovePrice)) {
              totalPrice = parseFloat(totalPrice) + pictureImprovePrice;
          }



          totalPrice = parseFloat(totalPrice).toFixed(2);

          if(undefined == sizePrice){
              sizePrice = $('.js__hidden_sizes>li:first').find('a').data('price');
          }



          if(isNaN(totalPrice)) return;

          totalPrice = calc_float_val(totalPrice);

          $('.total_with_total').remove();
          $('.ramm__pr').remove();




          if(rp != 0){
              var cur_summ_price = parseFloat(totalPrice) + parseFloat(rp);
              cur_summ_price = cur_summ_price.toFixed(2);

              $('.tabs-item #totalPrice').html(calc_float_val(totalPrice));
              $('.sum__mod #glob_summ2').html(calc_float_val(totalPrice));

              var rm_txt = $('body').data('for_ram');

              var vs_text = $('body').data('full');


              $('.totalPriceNew').text(+calc_float_val(cur_summ_price));
              $('.totalPriceNew').attr('data-total',calc_float_val(cur_summ_price));


              $('.tabs-item #totalPrice').attr('data-total',calc_float_val(cur_summ_price));
          }else{
              var cur_summ_price = totalPrice;
              cur_summ_price = calc_float_val(cur_summ_price);
              $('.tabs-item #totalPrice').html(cur_summ_price);
              $('.tabs-item #totalPrice').attr('data-total',cur_summ_price);
              $('.totalPriceNew').text(cur_summ_price);
              $('.totalPriceNew').attr('data-total',cur_summ_price);
          }

          // Expose the same calculation operands to the new UI; ordering remains unchanged.
          var pricingRoot = document.querySelector('#generate.calculator-v2');
          if (pricingRoot) {
              function optionName(selector) {
                  var el = document.querySelector(selector);
                  if (!el) return '';
                  if (el.dataset.name) return el.dataset.name.replace(/\s+[+-]?\d+(?:[.,]\d+)?\s*(?:€|EUR)\s*$/, '').trim();
                  var label = el.closest('label');
                  var caption = label && label.querySelector('span:not(.jcf-radio):not(.jcf-checkbox)');
                  return (caption ? caption.textContent : label ? label.textContent : '').replace(/\s+/g, ' ').replace(/\s+[+-]?\d+(?:[.,]\d+)?\s*(?:€|EUR)\s*$/, '').trim();
              }
              var frameName = $('.ramu-item.active .code').text().trim();
              var breakdown = [
                  {key:'base', name:'', amount:Number(sizePrice)},
                  {key:'material', name:optionName('input[name="canvas_type"]:checked'), amount:Number(cur_sizes_add)-Number(sizePrice)},
                  {key:'execution', name:$('.calcExecution.active').text().trim(), amount:Number(ex_add_price)},
                  {key:'decoration', name:optionName('input[name="decoration"]:checked'), amount:Number(decorAddPrice)},
                  {key:'packaging', name:optionName('input[name="boxes[]"]:checked'), amount:Number(boxImprovePrice)||0},
                  {key:'improvement', name:photoImprovementEnabled()?optionName('input[name="boxes2[]"]:checked'):'', amount:Number(pictureImprovePrice)||0},
                  {key:'frame', name:frameName, amount:Number(rp)||0},
                  {key:'delivery', name:optionName('.delivery-inputs input:checked'), amount:Number(dostTimePrice)||0}
              ];
              pricingRoot.dataset.priceBreakdown = JSON.stringify(breakdown);
              pricingRoot.dispatchEvent(new CustomEvent('calculator:price', {detail:breakdown}));
          }


      }


      var cur_name = $('.js_add_basket_stena').data('name');

      $('.jcf-button-content').text('Загрузить');
      $('.jcf-fake-input').text('Загрузить');

      $(document).on('click', '.interior-item', function(e){
          var img_border = $('.ramu-item.active').find('img').attr('src');
          var bi_full = 'url('+img_border+') 65 68 / 5em stretch';
      })

      // Delegation runs after the frame's direct selection handler.
      $(document).on('click', '.ramu-item', function(){
          calcTotalPrice();
      });

      $('.delivery-inputs input').change(function(e){
          dost_price = $(this).val();
          if(dost_price == 0){
              $('.tabs-item1 .dost_items').children('li').eq(1).find('label').trigger('click');
              $('.tabs-item2 .dost_items').children('li').eq(1).find('label').trigger('click');
          }else{
              $('.tabs-item1 .dost_items').children('li').eq(2).find('label').trigger('click');
              $('.tabs-item2 .dost_items').children('li').eq(2).find('label').trigger('click');
          }
          setTimeout(() => {
          calcTotalPrice();
          }, 200);
      });


      var tool_undo = document.getElementById("undo");
      var tool_redo = document.getElementById("redo");
      var tool_photo_zoom_minus = document.getElementById("photo_zoom_minus");
      var tool_photo_zoom_plus = document.getElementById("photo_zoom_plus");
      var tool_turn_right = document.getElementById("turn_right");
      var tool_turn_left = document.getElementById("turn_left");
      var tool_cell_delete = document.getElementById("cell_delete");

      var canvas = document.getElementById("canvas");

      var editor, flatPreview=false;
      try {
          if(location.hostname==='test.viarcanvas.com'&&new URLSearchParams(location.search).get('canvas_fallback')==='1')throw new Error('QA fallback');
          editor=new Canvas3D(canvas);
      } catch(error) {
          if(!document.querySelector('#generate.calculator-v2'))throw error;
          flatPreview=true;
          var replacement=document.createElement('canvas');replacement.id=canvas.id;canvas.replaceWith(replacement);canvas=replacement;
          var surface=document.createElement('canvas');surface.width=surface.height=1200;
          editor=new PhotoEditor({canvas:surface,isMouseEvents:false,DragAndDropEvents:false,isCellDiv:false,isAct_cellresize:false,isOutPhoto:false});
          editor.canvas_ViewPort=canvas;
          editor.setSize=function(h,w){canvas.width=w;canvas.height=h;};
          editor.setSizeBox=function(h,w,d){this.size_height=h;this.size_width=w;this.size_depth=d;surface.height=Math.round(1200*h);surface.width=Math.round(1200*w);};
          editor.render2D=function(){PhotoEditor.prototype.render.call(this,false);};
          editor.render=function(){this.render2D();this.render3D();};
          editor.render3D=function(){var ctx=canvas.getContext('2d');ctx.fillStyle='#f5f2ec';ctx.fillRect(0,0,canvas.width,canvas.height);var scale=Math.min(canvas.width/surface.width,canvas.height/surface.height)*.9;ctx.drawImage(surface,(canvas.width-surface.width*scale)/2,(canvas.height-surface.height*scale)/2,surface.width*scale,surface.height*scale);};
          document.getElementById('generate').classList.add('calculator-flat');
          var notice=document.createElement('p');notice.className='calculator-flat-notice';notice.setAttribute('role','status');
          notice.textContent=document.getElementById('calculator-labels').dataset.fallback;
          document.getElementById('PhotoEditor').after(notice);
      }
      if(!flatPreview)CanvasWrap.install(editor, document.getElementById('canvas-wrap-controls'));
      if(!flatPreview && document.querySelector('#generate.calculator-v2')) {
          if(window.CanvasRoom) CanvasRoom.install(editor);
          else window.addEventListener('canvas-room-ready',function(){CanvasRoom.install(editor);},{once:true});
      }
      console.log(editor)
      editor.ColorCell = document.querySelector('#generate.calculator-v2') ? '#f1f1f1' : '#eee9df';
      //правки123
      editor.is_box_offset_2_x = true;
      editor.is_box_offset_1_y = true;
      editor.is_box_offset_2_y = true;
      //правки123

      // resize
      function resize()
      {
          var fitted=document.querySelector('#generate.calculator-v2');
          var cw=Math.max(1,canvas.parentElement.clientWidth);
          var ch=fitted?Math.max(1,canvas.parentElement.clientHeight):cw;
          if(!canvas.parentElement.clientWidth || !canvas.parentElement.clientHeight)return;
          editor.setSize(ch,cw);
          if(editor.fitPreview)editor.fitPreview(cw,ch);

          Canvas3D.prototype.render.call(editor);
      }

      var glob_rotated_img = null;
      // fix click
      $(document).on('click', '.tools>div', function(e){
          Canvas3D.prototype.render.call(editor);
          glob_rotated_img = editor.out(200,150);
      });

      // new
      function elementInViewport(el) {
          var top = el.offsetTop;
          var left = el.offsetLeft;
          var width = el.offsetWidth;
          var height = el.offsetHeight;

          while(el.offsetParent) {
              el = el.offsetParent;
              top += el.offsetTop;
              left += el.offsetLeft;
          }

          return (
              top < (window.pageYOffset + window.innerHeight) &&
              left < (window.pageXOffset + window.innerWidth) &&
              (top + height) > window.pageYOffset &&
              (left + width) > window.pageXOffset
          );
      }

      var time_wheel;
      document.addEventListener("wheel", function(e){
          if (e.target !== canvas)
          time_wheel = new Date().getTime();
      });

      function render()
      {
          if (time_wheel< new Date().getTime() - 500 && elementInViewport(canvas))
          {
              Canvas3D.prototype.render.call(editor);
          }
          window.requestAnimationFrame(render);
      }

      window.requestAnimationFrame(render);
      // endnew

      var old_clientWidth, old_clientHeight;

      setInterval(function()
      {
          if (canvas.parentElement.clientWidth !== old_clientWidth || canvas.parentElement.clientHeight !== old_clientHeight)
          {
          old_clientHeight = canvas.parentElement.clientHeight;
          old_clientWidth = canvas.parentElement.clientWidth;
          resize();
      }
      },10);

      window.addEventListener("orientationchange",resize,true);
      window.addEventListener("resize",resize,true);

      resize();

      editor.resetCanvasUpload = function () {
          canvasUploadSerial++;
          CanvasMainImg = null; js_img_tab2 = ''; base_64_img = null;
          imgs.forEach(function(item){item.isLoad=false;item.img=null;});
      };
      editor.onchange = function(){//ловим изменения в редакторе
          if(flatPreview)editor.render();
          var current = editor.grid[0] && editor.grid[0].resource;
          CanvasMainImg = current && current.img.sourceFile || null;
          js_img_tab2 = current ? current.img.original.src : '';
          if(current && imgs && imgs[0]) {
              imgs[0].img=current.img;imgs[0].isLoad=true;
              imgs[0].element.src=current.img.original.src;
              imgs[0].element.parentElement.classList.add('is-load');
              $('#imgs .js-file-preview').hide();$('#imgs .abs-close,#imgs .js-file-multiple').show();
              $('.additional-image').removeClass('additional-image_pending').addClass('additional-image_loaded');
          } else if(imgs) {
              imgs.forEach(function(item){item.isLoad=false;item.img=null;item.element.removeAttribute('src');item.element.parentElement.classList.remove('is-load');});
              $('#imgs .abs-close,#imgs .js-file-multiple').hide();$('#imgs .js-file-preview').show();
              $('.additional-image').addClass('additional-image_pending').removeClass('additional-image_loaded');
          }


          editor.is_Redo()?tool_redo.classList.remove("disable"):tool_redo.classList.add("disable");
          editor.is_Undo()?tool_undo.classList.remove("disable"):tool_undo.classList.add("disable");
          // Single-canvas toolbar must work immediately after upload, before a canvas tap.
          if(current || (editor.selection && editor.selection.cell && editor.selection.cell.resource))
          {   tool_cell_delete.classList.remove("disable");
              tool_photo_zoom_minus.classList.remove("disable");
              tool_photo_zoom_plus.classList.remove("disable");
              tool_turn_right.classList.remove("disable");
              tool_turn_left.classList.remove("disable");
          }else
          {   tool_cell_delete.classList.add("disable");
              tool_photo_zoom_minus.classList.add("disable");
              tool_photo_zoom_plus.classList.add("disable");
              tool_turn_right.classList.add("disable");
              tool_turn_left.classList.add("disable");
          }
      }



      editor.enable_drawing();// включить рисование. editor.disable_drawing(); отключить
      // editor.disable_drawing();
      editor.radius = 0;
      editor.between = 0.00;

      // function _sizeSetter(h,w) {
      //   let depth = (Math.max(h,w)) * 0.07;
      //   editor.setSizeBox(h,w,depth);
      //     resize();
      // }
      var canvasFormTimer;
      function setSize(centimeter_h,centimeter_w,centimeter_depth = 0)
      {
          clearTimeout(canvasFormTimer);
          console.log("setSize");
          if(location.hostname==='test.viarcanvas.com'&&new URLSearchParams(location.search).has('canvas_qa')){
              document.getElementById('canvas').dataset.sizeTrace=JSON.stringify({w:centimeter_w,h:centimeter_h,stack:new Error().stack});
          }
          console.log(centimeter_h,centimeter_w);
          size_centimeter_h = centimeter_h;
          size_centimeter_w = centimeter_w;
          if (!centimeter_depth) {
            centimeter_depth = size_centimeter_h * 0.07;
          }
          var mx = Math.max(centimeter_h,centimeter_w);
          var h = (1 / mx) * centimeter_h;
          var w = (1 / mx) * centimeter_w;
          var depth = (1 / mx) * centimeter_depth;

          editor.setSizeBox(h,w,depth);
          resize();
      }

      // Default to 40 × 50 cm; preserve explicit customer choices.
      function initialCanvasSize(block) {
          var choices=Array.from(block.querySelectorAll('.calcSize[data-size]')).filter(function(el){return !el.classList.contains('disabled') && el.getAttribute('aria-disabled')!=='true';});
          var remembered=choices.find(function(el){return el.dataset.customerSize==='1';});
          if(remembered)return $(remembered);
          var preferred=choices.find(function(el){return el.dataset.size==='40x50';});
          return $(preferred||choices[1]||choices[0]);
      }
      document.addEventListener('click',function(e){
          var size=e.target.closest('.calcSize');
          if(!e.isTrusted || !size || !document.querySelector('#generate.calculator-v2'))return;
          var block=size.closest('.accordion__sizes-block');
          block.querySelectorAll('.calcSize').forEach(function(el){delete el.dataset.customerSize;});
          size.dataset.customerSize='1';
      },true);
      window.addEventListener('load',function(){
          if(!document.querySelector('#generate.calculator-v2'))return;
          var block=document.querySelector('.accordion__sizes-block:not(.hidden-block)');
          if(block && !block.querySelector('[data-customer-size="1"]'))initialCanvasSize(block).click();
      },{once:true});

      function setForm(index)
      {



          var act_h, act_w, act_depth, act_price;
          var targetContainer = $(`.accordion__sizes-block[data-id="${index + 1}"] .kviz-group__item:first-child .calcSize:first-child`);
          if(document.querySelector('#generate.calculator-v2')) {
              var block=document.querySelector('.accordion__sizes-block[data-id="'+(index+1)+'"]');
              if(block)targetContainer=initialCanvasSize(block);
          }
          clearTimeout(canvasFormTimer);
          canvasFormTimer = setTimeout(()=>{
            targetContainer.click();

          }, 200)

          }


      tool_cell_delete.onclick = function() {
          var close=document.querySelector('#imgs .abs-close');
          if(close)close.click();
      }

      tool_undo.onclick = function()
      {
          editor.Undo();
      }

      tool_redo.onclick = function()
      {
          editor.Redo();
      }

      tool_photo_zoom_minus.onclick = function()
      {
          var cell=editor.selection && editor.selection.cell || editor.grid[0];
          if(cell && cell.resource)cell.photo_zoom(Math.max(1, cell.resource.zoom * .9));
      }

      tool_photo_zoom_plus.onclick = function()
      {
          var cell=editor.selection && editor.selection.cell || editor.grid[0];
          if(cell && cell.resource)cell.photo_zoom(cell.resource.zoom * 1.1);
      }

      tool_turn_right.onclick = function()
      {
          var cell=editor.selection && editor.selection.cell || editor.grid[0];
          if(cell && cell.resource)cell.photo_rotate(cell.resource.rotate + Math.PI/2);
      }

      tool_turn_left.onclick = function()
      {
          var cell=editor.selection && editor.selection.cell || editor.grid[0];
          if(cell && cell.resource)cell.photo_rotate(cell.resource.rotate - Math.PI/2);

      }

      function GetIcon(original)
      {
          var canvas = document.createElement("canvas");
          canvas.width = canvas.height = 80;
          var ctx = canvas.getContext('2d');
          var s = Math.max((canvas.width / original.width),(canvas.height / original.height));
          ctx.scale(s,s);
          ctx.drawImage(original, 0, 0);
          var r = new Image();
          r.src = canvas.toDataURL('image/jpeg', 0.95);

          // save image to future send
          base_64_img = original.src;

          return r;
      }


      function loadImg(src,callback)
      {
          var img = {};
          img.original = new Image();
          if (/^(https?:)?\/\//i.test(src)) img.original.crossOrigin = 'anonymous';

          img.original.onload = function()
          {
              try { img.icon = GetIcon(img.original); } catch (error) { callback(); return; }
              callback(img);
          }

          img.original.onerror = function()
          {
              callback();
          }
          img.original.src = src;
      }


      var js_img_tab2 = '';
      function canvasUploadError(inputFile) {
          document.getElementById('imgs').dataset.uploadBusy='false';
          inputFile.value='';
          if(editor.onchange)editor.onchange();
          var notice=document.getElementById('canvas-upload-error');
          if(!notice){notice=document.createElement('p');notice.id='canvas-upload-error';notice.setAttribute('role','alert');document.getElementById('imgs').appendChild(notice);}
          var controls=document.getElementById('canvas-wrap-controls');
          notice.textContent=controls&&controls.dataset.uploadError||'Unable to read this image. Choose a valid image.';
      }
      function InitImgs(imgs,type)
      {
          imgs.forEach(function (item){
              if (item.isOpen) {
                  var inputFile = document.createElement('input');
                  inputFile.setAttribute('type', 'file');
                  inputFile.setAttribute('accept', 'image/*,image/heif,image/heic');
                  item.element.appendChild(inputFile);

                  inputFile.onchange = async function ()
                  {
                      var ticket=++canvasUploadSerial;
                      var selectedFiles=Array.from(inputFile.files || []);
                      if(!selectedFiles.length)return;
                      document.getElementById('imgs').dataset.uploadBusy='true';

                      var notice=document.getElementById('canvas-upload-error');if(notice)notice.remove();

                      let pos = imgs.indexOf(item);

                      for (let file of selectedFiles)
                      {
                          if (pos < imgs.length)
                          {
                              let b = imgs[pos];

                              if (file.type === 'image/heic' || file.type === 'image/heif' || file.type === '') {
                                  try {
                                      const convertedBlob = await heic2any({ blob: file, toType: "image/png" });
                                      file = new File([convertedBlob], file.name.replace(/\.(heic|heif)$/i, '.png'), {
                                          type: "image/png",
                                      });
                                  } catch (err) {
                                      canvasUploadError(inputFile);
                                      continue;
                                  }
                              }

                              if(!/^image\/(jpeg|png|webp|gif|bmp|avif)$/i.test(file.type)) {
                                  canvasUploadError(inputFile); continue;
                              }
                              var sourceFile=file;
                              var reader = new FileReader();
                              reader.onload = function (e) {
                                  loadImg(e.target.result, function (img) {
                                      if (ticket!==canvasUploadSerial)return;
                                      inputFile.value='';document.getElementById('imgs').dataset.uploadBusy='false';
                                      if (img) {
                                          img.sourceFile=sourceFile;
                                          CanvasMainImg=sourceFile;
                                          b.element.src = img.original.src;
                                          js_img_tab2 = b.element.src;
                                          b.element.parentElement.setAttribute('class', 'is-load');
                                          b.img = img;
                                          b.isLoad = true;

                                          editor.grid[0].setImg(img);
                                      } else {
                                          canvasUploadError(inputFile);
                                      }
                                  });
                              }
                              reader.onerror=function(){if(ticket===canvasUploadSerial)canvasUploadError(inputFile);};

                              reader.readAsDataURL(file);
                              pos++;
                            }

                        }
                    }

                    item.element.onclick = function()
                    {
                        inputFile.click();
                    }
              }

              DragAndDrop_img(item, type);

              if (item.element.src !== "")
              {

                    loadImg(item.element.src,function(img){
                            if (img)
                            {
                                item.element.src= img.icon.src;
                                item.img = img;
                                item.isLoad = true;

                            }else
                            {
                                item.isLoad = false;

                            }
                        });
              }

              if (item.isLoad) {
                  console.log(item);
              }
        });

        console.log(imgs)
    }


      var imgs = Object.values(document.getElementById("imgs").getElementsByTagName("img")).map(function(img)
      {
          return {element : img,isOpen:true};
      });

      InitImgs(imgs,"img");

      // $('.calcForm.active').trigger('click');

// ...........................................................
      var mult = $('body').data('multiplier');

      if (isNaN(mult)){
          mult = 1;
      }

      // ...........................................................

