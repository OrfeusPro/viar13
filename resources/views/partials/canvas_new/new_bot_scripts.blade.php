    <script>
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
    </script>

    <script>
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
    </script>
    <script>
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
    </script>


    <script src="{{ ver_asset(env('THEME').'js/add.js') }}?v=0.03"></script>

    <script src="{{ ver_asset(env('THEME').'js/main.js') }}"></script>
    {{-- <script src="{{ asset(env('THEME').'js/script.js') }}"></script> --}}
    <script data-defdel="{{ ver_asset(env('THEME').'js/fancybox.js') }}"></script>
    <script data-defdel="{{ ver_asset(env('THEME').'js/swal.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/before-after.min.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/three.min.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/3dcanvas.js') }}"></script>
    <script>
      window.onShowInterior = function() {
          return {
              size: {
                  h: size_centimeter_h,
                  w: size_centimeter_w
              },
              src: editor.out(editor.canvas.width, editor.canvas.height, false)
          };
      }
    </script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.min.js') }}"></script>
        {{-- <script src="{{ asset(env('THEME').'js/jcf.file.min.js') }}"></script> --}}
        <script src="{{ ver_asset(env('THEME').'js/jcf.radio.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.checkbox.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.range.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jquery.collapse_storage.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jquery.collapse.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/slick.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/templates.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/canvas.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/interior.js') }}"></script>
    <script>
      var CanvasMainImg = null;
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

    // SUBMIT
    $('#t3_submit_btn').click(function () {

        obj=$(this);
        if (obj.data('processed')) {return; }
        $('.loading-bar').css('display', 'none').hide().fadeOut();
        obj.data('processed', true);

        if(CanvasMainImg == null){
            $('.popup-inv-size').addClass('active');
            return;
        }


        let data = formData();
        data.append('pid', $('#t3_submit_btn').data('pid'));

        $('.js_spinner').jmspinner('large');
        $.ajax({
            type: 'post',
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('add_item_to_basket') }}',
            data: data,
            beforeSend: function() {
                $('.loading-bar').css('display', 'flex').hide().fadeIn();
            },
            success: function (response) {
                if (response) {
                    var data = jQuery.parseJSON(response);

                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {
                    // seo
                    var total_seo__price = $('.tabs-item #totalPrice').data('total');
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push({
                    'ecommerce': {
                        'currencyCode': 'EUR',
                        'add': {
                        'actionField': {'list': 'Kanvas'},
                        'products': [{
                            'name': 'Kanvas',
                            'price': total_seo__price,
                            'quantity': 1,
                        }]
                        }
                    },
                    'event': 'EE-event',
                    'EE-event-category': 'Enhanced Ecommerce',
                    'EE-event-action': 'Add To Cart',
                    'EE-event-non-interaction': 'False'
                    });

                    // endseo
                    $('body').addClass('open-frame');
                    $('.popup-frame').css("display", "flex").hide().fadeIn();
                    $('.popup-cart').fadeIn();
                    $('.loading-bar').fadeOut();

                    // $('.popup-bask-add').addClass('active');
                    var cur_count = $('#smallCart').text();
                    cur_count = parseInt(cur_count);
                    cur_count++;
                     $('#smallCart').text(cur_count);
                    }

                    $('#t1_submit_btn').attr('data-disabled', '0');
                }

                $('.js_spinner').jmspinner(false);
                $('.images-container').html("");
                $('.file-input').val('');
                $('.loading-bar').css('display','none');

            },
            error: function (error) {

                $('.images-container').html("");
                $('.file-input').val('');
                $('.loading-bar').css('display','none');
                obj.data('processed', false);

                if (error.responseText) {
                    let response = JSON.parse(error.responseText);
                    $.each(response.errors, function (key, value) {
                        alert(value[0])
                    });
                } else {
                    alert('Server error');
                }

                $('.js_spinner').jmspinner(false);
                $('#t1_submit_btn').attr('data-disabled', '0');
            }
        });
    });

      // выберите размер
      $(document).on('click', '.js_add_basket_module', function(e){
          $('#t1_submit_btn').trigger('click');
      });

      $(document).on('click', '.calcSize', function(e){
          let sizePrice = $(this).data('price');
          let sizeSize = $(this).data('size');
          calcTotalPrice(sizePrice, sizeSize);
      });

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
          let canvasId = $('input[name="canvas_type"]:checked').data('id');
          let executionId = $('.calcExecution.active').data('execution');
          let decorationId = $('input[name="decoration"]:checked').data('id');
          let userComment = $('#userComment').val();
          let boxIds = [];
          let terms = $('.delivery-inputs .kviz-radio_active').find('.js_term__selected').text();
          let terms_price = $('.delivery-inputs .kviz-radio_active').find('input').val();

          let Image3d = '';
          let canvas = document.getElementById("canvas");

          if (canvas && canvas.width > 0 && canvas.height > 0) {
              let resizedCanvas = document.createElement("canvas");
              let resizedContext = resizedCanvas.getContext("2d");
              resizedCanvas.height = 200;
              resizedCanvas.width = 200;
              resizedContext.drawImage(canvas, 0, 0, 200, 200);
              Image3d = resizedCanvas.toDataURL();
          } else if (typeof editor !== 'undefined' && editor && typeof editor.out === 'function') {
              try {
                  Image3d = editor.out(200, 200, false);
              } catch (e) {
                  Image3d = '';
              }
          }

          $('input[name="boxes[]"]:checked').each(function () {
              boxIds.push(parseInt($(this).data('id')));
          });


          formData.append('improve_photo', $('input[name="boxes2[]"]:checked').data('name'));
          const totalFromData = parseFloat($('.totalPriceNew').attr('data-total'));
          const totalFromLegacy = parseFloat($('.tabs-item #totalPrice').attr('data-total'));
          const totalFromText = parseFloat(($('.totalPriceNew').text() || '').replace(',', '.'));
          const normalizedTotal = Number.isFinite(totalFromData)
              ? totalFromData
              : (Number.isFinite(totalFromLegacy) ? totalFromLegacy : (Number.isFinite(totalFromText) ? totalFromText : 0));
          formData.append('price', normalizedTotal.toFixed(2));

          if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
              formData.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
          }else{
              formData.append('photo_ex', '');
          }

          if($('.ramu-item.active').length){
              var $activeRam = $('.ramu-item.active').last();
              var activeRamPrice = parseFloat($activeRam.data('price')) || 0;
              if(activeRamPrice > 0){
                  var ram_id = $activeRam.data('id') || $activeRam.find('input[name="ram_id"]').val();
              }
          }

          formData.append('basketType', '1');

          if (userImage)
              formData.append('userImage', userImage);
          if (Image3d)
              formData.append('Image3d', Image3d);
          if (formId)
              formData.append('formId', formId);
          if (sizeId) formData.append('sizeId', sizeId);

          formData.append('name', 'Canvas');

          if (ram_id)
              formData.append('ram_id', ram_id);
          if (executionId)
              formData.append('executionId', executionId);
          if (canvasId)
              formData.append('canvasId', canvasId);
          if (decorationId)
              formData.append('decorationId', decorationId);
          if (userComment)
              formData.append('userComment', userComment);
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

          if (!sizeSize)
              sizeSize = $activeSize.data('size');

          if (!execution)
              execution = $('.calcExecution.active').data('execution');

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
              if($('.calcSize.kviz-radio_active'.length)){
                  rp = ((s1+s1+s2+s2)/100)*parseInt(ram_price);
              }else{
                  rp  = 0;
              }
          }

          // холст
          let defPrice = $activeSize.data('price');
          let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
          let cur_sizes_add_coef = defPrice * canvasTypePrice;

          let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

          if(cur_sizes_add != 0.00){
              totalPrice = cur_sizes_add;
          }
          //

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
          const pictureImprovePrice = parseFloat($('input[name="boxes2[]"]:checked').val());
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
      }

      var cur_name = $('.js_add_basket_stena').data('name');

      $('.jcf-button-content').text('Загрузить');
      $('.jcf-fake-input').text('Загрузить');

      $(document).on('click', '.interior-item', function(e){
          var img_border = $('.ramu-item.active').find('img').attr('src');
          var bi_full = 'url('+img_border+') 65 68 / 5em stretch';
      })

      $('.ramu-item').click(function(e){
          setTimeout(() => {
          calcTotalPrice();
          }, 200);
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
    </script>
    <script>
      var tool_undo = document.getElementById("undo");
      var tool_redo = document.getElementById("redo");
      var tool_photo_zoom_minus = document.getElementById("photo_zoom_minus");
      var tool_photo_zoom_plus = document.getElementById("photo_zoom_plus");
      var tool_turn_right = document.getElementById("turn_right");
      var tool_turn_left = document.getElementById("turn_left");
      var tool_cell_delete = document.getElementById("cell_delete");

      var canvas = document.getElementById("canvas");

      var editor = new Canvas3D(canvas);
      console.log(editor)
      editor.ColorCell = '#d2afac';
      //правки123
      editor.is_box_offset_2_x = true;
      editor.is_box_offset_1_y = true;
      editor.is_box_offset_2_y = true;
      //правки123

      // resize
      function resize()
      {
          var mn = canvas.parentElement.clientWidth;
          canvas.height = mn;
          canvas.width  = mn;
          editor.setSize(canvas.height,canvas.width);

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

      editor.onchange = function(){//ловим изменения в редакторе
          editor.is_Redo()?tool_redo.classList.remove("disable"):tool_redo.classList.add("disable");
          editor.is_Undo()?tool_undo.classList.remove("disable"):tool_undo.classList.add("disable");
          if(editor.selection && editor.selection.cell && editor.selection.cell.resource)
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
      function setSize(centimeter_h,centimeter_w,centimeter_depth = 0)
      {

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

      function setForm(index)
      {



          var act_h, act_w, act_depth, act_price;
          var targetContainer = $(`.accordion__sizes-block[data-id="${index + 1}"] .kviz-group__item:first-child .calcSize:first-child`);
          setTimeout(()=>{
            targetContainer.click();

          }, 200)

          }


      tool_cell_delete.onclick = function()
      {
          if (editor.selection.cell)editor.selection.cell.resource = undefined;
          editor.selection.delete();
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
          editor.selection.cell.photo_zoom(Math.max(1, editor.selection.cell.resource.zoom - (editor.selection.cell.resource.zoom * 0.1)));
      }

      tool_photo_zoom_plus.onclick = function()
      {
          editor.selection.cell.photo_zoom(editor.selection.cell.resource.zoom + (editor.selection.cell.resource.zoom * 0.1));
      }

      tool_turn_right.onclick = function()
      {
          editor.selection.cell.photo_rotate(editor.selection.cell.resource.rotate + Math.PI/2);
      }

      tool_turn_left.onclick = function()
      {
          editor.selection.cell.photo_rotate(editor.selection.cell.resource.rotate - Math.PI/2);

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
          img.original.src = src;

          img.original.onload = function()
          {
              img.icon = GetIcon(img.original);
              callback(img);
          }

          img.original.onerror = function()
          {
              callback();
          }
      }


      var js_img_tab2 = '';
      function InitImgs(imgs,type)
      {

          imgs.forEach(function(item){
                  if (item.isOpen)
                  {
                      var inputFile = document.createElement('input');
                      inputFile.setAttribute('type', 'file');

                      item.element.appendChild(inputFile);

                    //   inputFile.setAttribute('multiple', 'multiple');
                      inputFile.setAttribute('accept', 'image/*,image/heif,image/heic');

                      inputFile.onchange = function()
                      {
                          CanvasMainImg = inputFile.files[0];

                          let pos = imgs.indexOf(item);

                          for (let file of inputFile.files)
                          {
                              if (pos < imgs.length)
                              {
                                  let b = imgs[pos];

                                  var reader = new FileReader();
                                // console.log(this)
                                  reader.onload = function (e)
                                  {
                                      loadImg(e.target.result,function(img){
                                              if (img)
                                              {
                                                  b.element.src= img.original.src;
                                                  js_img_tab2 = b.element.src;
                                                  b.element.parentElement.setAttribute('class', 'is-load');
                                                  // CanvasMainImg
                                                  b.img = img;
                                                  b.isLoad = true;

                                                  // if (!editor.grid[0].resource)
                                                  editor.grid[0].setImg(img);
                                              }else
                                              {
                                                  b.isLoad = false;
                                              }
                                          });
                                  }

                                  reader.readAsDataURL(file);

                                  // CanvasMainImg

                                  pos++;
                              }

                          }
                      }

                      item.element.onclick = function()
                      {
                          inputFile.click();
                      }
                  }

                  DragAndDrop_img(item,type);

                  if  (item.element.src !== "")
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
                  console.log(item)
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
    </script>
