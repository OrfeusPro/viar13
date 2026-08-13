<script src="{{ ver_asset(env('THEME').'js/collage-templates.js') }}"></script>
<script src="{{ ver_asset(env('THEME').'js/PhotoEditor.js') }}"></script>


<script>
  var arr_zone = [];
  if ($('.js_zone').length) {
    Dropzone.autoDiscover = false;

    var myDropzone = new Dropzone(".js_zone", {
      addRemoveLinks: true,
      maxFilesize: 20,
      maxThumbnailFilesize: 20,
      maxFiles: 100,
      thumbnailWidth: 600,
      thumbnailHeight: 600,
      previewTemplate: document.querySelector('#template-preview').innerHTML,
      url: "/ajax_file_upload_handler/",
      autoProcessQueue: false,
      acceptedFiles: ".jpeg,.jpg,.png,.heic,.heif",
      init: function () {
        var thisDropzone = this;


        this.on('addedfile', function (file) {


          var reader = new FileReader();
          reader.onload = function (event) {
            console.log('event:', event);

            var image = new Image();
            image.src = event.target.result;
            image.onload = function () {
              editor.InitResource({ isLoad: true, element: file.previewElement, img: { icon: image, original: image } }, "img");
            };
          };
          reader.readAsDataURL(file);
        })
      },
    });
  }

</script>
<script>
  $(document).ready(function () {
    $(".interior-slider").not('.slick-initialized').slick({
      infinite: !0,
      slidesToShow: 3,
      slidesToScroll: 1,
      nextArrow:
        '<a href="javascript:void(0)" class="slick-arrow next"><i class="icon-icon25"></i></a>',
      prevArrow:
        '<a href="javascript:void(0)" class="slick-arrow prev"><i class="icon-icon22"></i></a>',
    });


    // $(".ramu-slider").not('.slick-initialized').slick({
    //     rows: 2,
    //     infinite: !0,
    //     slidesToShow: 3,
    //     slidesToScroll: 1,
    //     nextArrow:
    //         '<a href="javascript:void(0)" class="slick-arrow next"><i class="icon-icon25"></i></a>',
    //     prevArrow:
    //         '<a href="javascript:void(0)" class="slick-arrow prev"><i class="icon-icon22"></i></a>',
    // });
  });

  // send
  var glob_price;
  var price_size;
  var ram_price = 0;

  var rp = 0;
  var calcPrice = function () {

    if (price_size == undefined) {
      var err = $('body').data('err_size');
      $('.popup-inv-size').addClass('active');
      return;
    }

    // holst add
    let defPrice = $('.js_size.kviz-radio_active').data('price');

    // let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
    let canvasTypePrice = 0;
    let cur_sizes_add_coef = defPrice * canvasTypePrice;

    let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

    if (cur_sizes_add == 0.00) {
      cur_sizes_add = defPrice;
    }
    // endholst

    var price_pack = $('.formalization-bottom--check .kviz-c-group .kviz-radio_active').find('input').val();
    var dost_price = $('.delivery-inputs .kviz-radio_active').find('input').val();
    var quality_price = $('.additional-image .kviz-c-group .kviz-radio_active').find('input').val() || 0;
    var ram_price = $('.ramu-item.active').data('price');

    if (typeof ram_price === 'undefined') {
      rp = 0;
    } else {
      if ($('.js_size.kviz-radio_active'.length)) {
        var cur_size = $('.js_size.kviz-radio_active').data('size');
        var cur_size_calc = cur_size.split("x");
        var s1 = parseInt(cur_size_calc[0]);
        var s2 = parseInt(cur_size_calc[1]);

        rp = ((s1 + s1 + s2 + s2) / 100) * parseInt(ram_price);
      } else {
        rp = 0;
      }
    }

    glob_price = parseFloat(cur_sizes_add) + parseFloat(price_pack)
      + parseFloat(dost_price) + parseFloat(rp) + parseFloat(quality_price);

    glob_price = glob_price.toFixed(2);

    if (isNaN(glob_price)) return;

    glob_price = parseFloat(glob_price).toFixed(2);

    var glob_price_show = glob_price;
    glob_price_show = parseFloat(glob_price_show).toFixed(2);

    console.log(glob_price_show);
    $('.totalPriceNew').text(glob_price_show);
    $('#totalSum span span').text(glob_price_show);
    $('.js_summ').text(glob_price_show);

  }

  $(document).on('click', '.ramu-item', function (e) {
    calcPrice();
  });

  $(document).on('click', '.js_size', function (e) {
    price_size = $(this).data('price');

    calcPrice();
  });

  $(document).on('click', '.js__canv_radio .jcf-radio', function (e) {
    setTimeout(() => {
      calcPrice();
    }, 200);
  });

  $(document).on('click', '.kviz-c-group .jcf-radio', function (e) {
    setTimeout(() => {
      calcPrice();
    }, 200);
  });

  $(document).on('click', '.dost_items .jcf-radio', function (e) {
    setTimeout(() => {
      calcPrice();
    }, 200);
  });

  $(document).on('click', '.sbm__collage', function (e) {
    e.preventDefault();
    calcPrice();

    var price_size = $('#sizes').find('.kviz-radio_active').data('price');

    if (price_size == undefined) {
      var err = $('body').data('err_size');
      $('.popup-inv-size').addClass('active');
      return;
    }

    var th = $(this);

    var name = $(this).data('name');
    var forma_id = $('.calcForm.active').data('id');
    var size = $('a.kviz-radio_active').data('size');

    var users_count = undefined;

    if (isNaN(glob_price) || glob_price == 0 || glob_price == undefined || glob_price == '') {
      var price_err_text = $('body').data('err_price');
      $('#err_msgs>div').text(price_err_text);
      $('.popup-inv-size').addClass('active');
      return;
    }

    var route = $(this).data('route');

    if (glob_price > 0) {
      $('.js_spinner').jmspinner('large');
      var form_data = new FormData();

      var type = 'undefined';
      var holst_id = $('.js__holsts').find('.jcf-checked').parent().data('id');
      var hud_of = 'undefined';
      var decor_id = $('.js_decors').find('.jcf-checked').data('id');
      var ram_id = $('.js_rams').find('.jcf-checked').parent().data('id');
      // Keep the packaging ID alongside its translated label. GalleryBox ID 2
      // is the stable source for G1, regardless of the storefront locale.
      var packagingInput = $('.formalization-bottom--check .kviz-c-group .kviz-radio_active').find('input[name="boxes[]"]');
      var compl_id = packagingInput.data('id');
      var comm = $('#userComment').val();
      var terms = $('.delivery-inputs').find('.kviz-radio_active').find('span').text();

      form_data.append('price', glob_price);
      form_data.append('name', name);
      form_data.append('formId', forma_id);
      form_data.append('type', type);
      form_data.append('holst_id', holst_id);
      form_data.append('pid', $('.sbm__collage').data('pid'));
      form_data.append('size', $('a.kviz-radio_active').data('size'));
      form_data.append('decor_id', decor_id);
      form_data.append('ram_id', ram_id);
      form_data.append('compl_id', compl_id);
      form_data.append('boxIds', compl_id);
      form_data.append('userComment', comm);
      form_data.append('terms', terms);
      form_data.append('pack', packagingInput.data('name'));
      form_data.append('dost_time', $('.delivery-inputs').find('.kviz-radio_active').find('span').text());
      form_data.append('terms_price', $('.delivery-inputs .jcf-label-active input').val());

      /*$('.delivery-inputs .kviz-radio_active').find('input').val()*/

      let resizedCanvas = document.createElement("canvas");
      let resizedContext = resizedCanvas.getContext("2d");
      let cw = $('#canvas').attr('width');
      let ch = $('#canvas').attr('height');

      resizedCanvas.height = ch;
      resizedCanvas.width = cw;

      let canvas = document.getElementById("canvas");
      let context = canvas.getContext("2d");
      resizedContext.drawImage(canvas, 0, 0, cw, ch);

      var Image2d = resizedCanvas.toDataURL();
      Image2d = Image2d += '';

      Image2d = Image2d.replace(/^data:image\/(png|jpg|jpeg|heic|heif);base64,/, "");

      var image_offset = editor.out(cw * 6, ch * 6, 1);
      image_offset = image_offset += '';

      if (image_offset.includes("png")) {
        form_data.append('is_png_base', '');
      }

      image_offset = image_offset.replace(/^data:image\/(png|jpg|jpeg|heic|heif);base64,/, "");

      form_data.append('image_offset', image_offset);
      form_data.append('image', Image2d);

      $(".all_images_to_upl").each(function (index, field) {
        const file = field.files[0];
        if (file.length != 0) {
          form_data.append('fon[]', file);
        }
      });

      if ($('input[name="photo_ex"]').length && $('input[name="photo_ex"]').val() != '') {
        form_data.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
      } else {
        form_data.append('photo_ex', '');
      }

      $.ajax({
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: "json",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: route,
        data: form_data,
        beforeSend: function() {
              $('.loading-bar').css('display', 'flex').hide().fadeIn();
        },
      }).then(function (data) {
        if (data['message']) {
          $('.js_spinner').jmspinner(false);
          $('.popup-inv-size').addClass('active');
          $('#err_msgs>div').text(data['message']);
          return;
        } else if (data['success']) {
          // seo
          var total_seo__price = $('#glob_summ').text();
          window.dataLayer = window.dataLayer || [];
          dataLayer.push({
            'ecommerce': {
              'currencyCode': 'EUR',
              'add': {
                'actionField': { 'list': 'Collage constructor' },
                'products': [{
                  'name': 'Collage constructor',
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
          $('.js_spinner').jmspinner(false);

      $('.popup-frame').css("display", "flex").hide().fadeIn();
      $('.popup-cart').fadeIn();

      $('.loading-bar').fadeOut();

            /*$('.popup-bask-add').addClass('active');*/

            var cur_count = $('#smallCart').text();
            cur_count = parseInt(cur_count);
            cur_count++;
            $('#smallCart').text(cur_count);
            return;
          }
        });

      }
  });

  $(document).on('click', '.js_add_tab2', function (e) {
    $('.js_sbm__calc').trigger('click');
  });

  var _30_40 = '{!! $collage_head['sizes_30x40'] !!}';
  var _38x38 = '{!! $collage_head['sizes_38x38'] !!}';
  var _40x30 = '{!! $collage_head['sizes_40x30'] !!}';

  const sizes = {
    1.4: {
      default: "30x40", list:
        [
          _30_40
        ]
    },
    1: {
      default: "38x38", list:
        [
          _38x38
        ]
    },
    0.6: {
      default: "40x30", list: [
        _40x30
      ]
    }
  };

  var tool_delete_background = document.getElementById("delete_background");
  var tool_clear = document.getElementById("clear");
  var tool_undo = document.getElementById("undo");
  var tool_redo = document.getElementById("redo");
  var tool_photo_zoom_minus = document.getElementById("photo_zoom_minus");
  var tool_photo_zoom_plus = document.getElementById("photo_zoom_plus");
  var tool_turn_right = document.getElementById("turn_right");
  var tool_turn_left = document.getElementById("turn_left");
  var tool_cell_delete = document.getElementById("cell_delete");
  var tool_fullscreen = document.getElementById("fullscreen");
  var tool_loadPhotos_4 = document.getElementById("loadPhotos_4");
  var tool_img_delete = document.getElementById("img_delete");

  //---правки
  var cur_text_color = document.getElementById("cur_text_color");

  var cur_text_font = document.getElementById("cur_text_font");
  //---правки

  var canvas = document.getElementById("canvas");

  var editor = new PhotoEditor({ canvas });
  editor.ColorCell = '#d9b4b1';

  editor.onchange = function () {//ловим изменения в редакторе
    editor.Background ? tool_delete_background.classList.remove("disable") : tool_delete_background.classList.add("disable");

    editor.is_Redo() ? tool_redo.classList.remove("disable") : tool_redo.classList.add("disable");
    editor.is_Undo() ? tool_undo.classList.remove("disable") : tool_undo.classList.add("disable");

    if (editor.selection && editor.selection.cell && editor.selection.cell.resource) {
      tool_img_delete.classList.remove("disable");//правки123
      tool_photo_zoom_minus.classList.remove("disable");
      tool_photo_zoom_plus.classList.remove("disable");
      tool_turn_right.classList.remove("disable");
      tool_turn_left.classList.remove("disable");
    } else {
      tool_img_delete.classList.add("disable");//правки123
      tool_photo_zoom_minus.classList.add("disable");
      tool_photo_zoom_plus.classList.add("disable");
      tool_turn_right.classList.add("disable");
      tool_turn_left.classList.add("disable");
    }

    if (editor.selection) {
      tool_cell_delete.classList.remove("disable");
    } else {
      tool_cell_delete.classList.add("disable");
    }
  }

  editor.enable_drawing();
  // включить рисование. editor.disable_drawing(); отключить
  // editor.disable_drawing();
  var centimeter_height, centimeter_width;

  function resize() {

    var wrapper = canvas.parentElement.parentElement;
    var ratio = Math.min(Math.max(600, (wrapper.clientHeight + 150)) / centimeter_height, wrapper.clientWidth / centimeter_width);
    canvas.height = centimeter_height * ratio;
    canvas.width = centimeter_width * ratio;
  }

  window.addEventListener("resize", resize);

  function setSize(h, w) {
    var offset = 2.5;// отступ фона 2.5см.
    centimeter_height = h;
    centimeter_width = w;
    editor.offset_cell_y = (1 / centimeter_height) * offset;
    editor.offset_cell_x = (1 / centimeter_width) * offset;
    resize();
  }

  function setForm(ratio) {
    var item = sizes[ratio];
    var s_html = '';
    var act;
    var act_h;
    var act_w;

    document.querySelector(".cs-size").innerHTML = "<ul>" + item.list.map(function (value) {
      var i = 0;
      value.split(",").forEach(function (item) {

        i++;

        var cur_price = item.match(/[^[\]]+(?=])/g);
        cur_price = JSON.stringify(cur_price).replace('[', '').replace(']', '');
        cur_price = cur_price.replace('"', '').replace('"', '');
        var price_clear = cur_price.split("-");
        var size_clear = item.substring(0, item.indexOf('['));
        var size = size_clear.split("x");
        var w = parseInt(size[0]);
        var h = parseInt(size[1]);

        if (i == 1) {
          act = 'active';
          act_h = h;
          act_w = w;
          if (price_clear[1] == 'undefined') {
            act_price = cur_price;
          } else {
            act_price = price_clear[1];
          }
        } else {
          var act = '';
        }
        let calcObject = `<li>
          <a href='javascript:void(0)' class="cs-calc--item calcSize kviz-radio js-checkbox js_size" data-stock="2" data-size="${h}x${w}" data-price="${price_clear[1] * mult}"  onclick="setSize(${h},${w}); [].forEach.call( document.querySelector('.cs-size').querySelectorAll('a'),function(el){el.classList.remove('kviz-radio_active');}); this.classList.add('kviz-radio_active');">
            <div class="check"></div>
            <label>
              <span>${h}x${w} см - <del>${price_clear[0] * mult}€</del> <b>${price_clear[1] * mult}€</b></span>
              <input type="radio" name="size" value="${h}x${w} см - ${price_clear[0] * mult}€ ${price_clear[1] * mult}€">
            </label>
          </a></li>`;
        s_html += calcObject;

      })

    }).join(" ") + "</ul>";
    $('.cs-size').html('<ul>' + s_html + '</ul>');
  }

  var list_templates = document.querySelector(".cs-wrapper--form");
  list_templates.innerHTML = "<ul>" + templates.sort((a, b) => a.ratio - b.ratio).map(function (template) {
    return `<li><a class='cs-set' onclick="open_template('${template.grid}',${template.ratio});"><div class="selected-icon">
                                            <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                            </svg>
                                          </div></i><img src="${template.icon}" alt=""></a></li>`;
  }).join(" ") + "</ul>";

  function open_template(grid, ratio) {
    editor.open_template(grid);
    setForm(ratio);

    // если есть активный
    var act_size = $('.cs-size').find('a.active');
    if (act_size.length) {
      var open_template_size = $('.cs-size').find('a.active').data('size');
      var otz = open_template_size.split("x");
      var w = parseInt(otz[1]);
      var h = parseInt(otz[0]);
      setSize(h, w);
      // нет, выбираем первый
    } else {
      setTimeout(() => {
        $('.cs-size>ul').find('li').eq(0).children('a').trigger('click');
        var open_template_size = $('.cs-size>ul').find('li').eq(0).children().data('size');
        var otz = open_template_size.split("x");
        var w = parseInt(otz[1]);
        var h = parseInt(otz[0]);
        setSize(h, w);
      }, 200);
    }

  }

  open_template(templates[0].grid, templates[0].ratio);

  $('#fullscreen-close').on('click', function () {

    // debugger;
    let body = document.getElementById("PhotoEditor");
    let fullscreen = document.querySelector(".fullscreen");
    let flexbox = document.querySelector(".full-container");
    let control = document.querySelector('.cs-controls');
    let images = document.querySelector('#imgs2');
    let old_parentElement = $('#tab_screen1');

    $('body').removeClass('fixed');
    tool_fullscreen.classList.remove("disable");
    document.body.removeChild(fullscreen);
    old_parentElement.append($(body));
    flexbox.removeChild(control);
    if (window.matchMedia('(max-width: 1200px)').matches) {
      $('.cs-canvs_settings').after($(control));
    } else {
      $('.cs-tabs-column').after($(control));
    }

    if (imageBool) {
      flexbox.removeChild(images);
      $('.cs-ph-title').after($(images));
    }
    resize();
  })



  var imageBool;

  tool_fullscreen.onclick = function () {
    $('body').addClass('fixed');
    var control = document.querySelector('.cs-controls');
    var images = document.querySelector('#imgs2');
    let flexbox = document.createElement("div");
    flexbox.className = "full-container";
    tool_fullscreen.classList.add("disable");
    var body = document.getElementById("PhotoEditor");
    var fullscreen = document.createElement("div");
    fullscreen.className = "fullscreen";
    document.body.appendChild(fullscreen);

    var old_parentElement = body.parentElement;

    function close() {


      $('body').removeClass('fixed');
      tool_fullscreen.classList.remove("disable");
      document.body.removeChild(fullscreen);
      old_parentElement.appendChild(body);
      flexbox.removeChild(control);
      if (window.matchMedia('(max-width: 1200px)').matches) {
        $('.cs-canvs_settings').after($(control));
      } else {
        $('.cs-tabs-column--wrap').after($(control));
      }

      if (imageBool) {
        flexbox.removeChild(images);
        $('.cs-ph-title').after($(images));
      }
      resize();
    }

    var wrap = document.createElement("div");

    wrap.className = "wrap";
    wrap.onclick = close;
    fullscreen.appendChild(wrap);
    fullscreen.appendChild(flexbox);
    if ($('.product-download .dz-preview').length > 0) {
      flexbox.appendChild(images);
      imageBool = true;
    } else {
      imageBool = false;
    }
    flexbox.appendChild(body);
    flexbox.appendChild(control);
    resize();
  }

  tool_clear.onclick = function () {
    editor.clear();
  }

  tool_cell_delete.onclick = function () {
    if (editor.selection.cell) editor.selection.cell.resource = undefined;
    editor.selection.delete();
  }

  tool_img_delete.onclick = function () {
    if (editor.selection.cell) {
      editor.selection.cell.deleteImg();

    }
  }

  tool_undo.onclick = function () {
    editor.Undo();
  }

  tool_redo.onclick = function () {
    editor.Redo();
  }

  tool_photo_zoom_minus.onclick = function () {
    editor.selection.cell.photo_zoom(Math.max(1, editor.selection.cell.resource.zoom - (editor.selection.cell.resource.zoom * 0.1)));
  }

  tool_photo_zoom_plus.onclick = function () {
    editor.selection.cell.photo_zoom(editor.selection.cell.resource.zoom + (editor.selection.cell.resource.zoom * 0.1));
  }

  tool_turn_right.onclick = function () {
    editor.selection.cell.photo_rotate(editor.selection.cell.resource.rotate + Math.PI / 2);
  }

  tool_turn_left.onclick = function () {
    editor.selection.cell.photo_rotate(editor.selection.cell.resource.rotate - Math.PI / 2);
  }

  tool_delete_background.onclick = function () {
    editor.Background = undefined;
  }

  tool_loadPhotos_4.onclick = function () {
    var list = [];
    var dd_files = myDropzone.getAcceptedFiles();
    for (let index = 0; index < dd_files.length; index++) {
      const el = dd_files[index];
      var image = new Image();
      image.src = el.dataURL;
      list.push(
        { icon: GetIcon(image), original: image }
      )
    }

    editor.loadPhotos(list, false/*рандомно*/, false/*сравнивать соотношения размера*/, false/*развернуть фото по размеру*/);
  }

  let initialText = 'Ваш текст';

  document.getElementById("add_text").onclick = function () {
    editor.AddText("", cur_text_font.value, 25, cur_text_color.value);
  }

  cur_text_color.onchange = cur_text_color.oninput = function () {
    if (editor.selection && editor.selection.element instanceof Layer && editor.selection.element.type == "text") {
      editor.selection.element.color = cur_text_color.value;
    }
  }

  cur_text_font.onchange = cur_text_font.oninput = function () {
    cur_text_font.style.fontFamily = cur_text_font.value;

    if (editor.selection && editor.selection.element instanceof Layer && editor.selection.element.type == "text") {
      editor.selection.element.font = cur_text_font.value;
      editor.updateSizeText(editor.selection.element);
    }
  }

  $('.color-item-txt').click(function () {
    let color = $(this).attr('data-color');
    editor.selection.element.color = color;
  })

  function GetIcon(original) {
    var canvas = document.createElement("canvas");
    canvas.width = canvas.height = 80;
    var ctx = canvas.getContext('2d');
    var s = Math.max((canvas.width / original.width), (canvas.height / original.height));
    ctx.scale(s, s);
    ctx.drawImage(original, 0, 0);
    var r = new Image();
    r.src = canvas.toDataURL('image/png', 1.0);

    return r;
  }

  function loadImg(src, callback) {
    var img = {};
    img.original = new Image();
    img.original.src = src;
    img.original.onload = function () {
      img.icon = GetIcon(img.original);
      callback(img);
    }

    img.original.onerror = function () {
      callback();
    }
  }

  function InitSmiles(imgs) {
    imgs.forEach(function (item) {
      if (item.isOpen) {
        item.element.onclick = function () {
          var inputFile = document.createElement('input');
          inputFile.setAttribute('type', 'file');

          inputFile.setAttribute('multiple', 'multiple');
          inputFile.setAttribute('accept', 'image/*,image/heif,image/heic');


          inputFile.onchange = function () {

            let pos = imgs.indexOf(item);

            for (let file of inputFile.files) {
              if (pos < imgs.length) {
                let b = imgs[pos];

                var reader = new FileReader();

                reader.onload = function (e) {
                  loadImg(e.target.result, function (img) {
                    if (img) {
                      //b.element.src= img.icon.src;
                      b.element.src = img.original.src;
                      b.img = img;
                      b.isLoad = true;

                    } else {
                      b.isLoad = false;
                    }
                  });
                }

                reader.readAsDataURL(file);
                pos++;
              }

            }
          }


          inputFile.click();
        }
      }

      // DragAndDrop_img(item,"smile");
      editor.InitResource(item, "smile");

      if (item.element.src !== "") {
        loadImg(item.element.src, function (img) {
          if (img) {
            item.element.src = img.icon.src;
            item.img = img;
            item.isLoad = true;
          } else {
            item.isLoad = false;
          }
        });
      }
    });
  }

  function InitImgs(imgs, type, callback_add) {
    imgs.forEach(function (item) {
      if (item.isOpen) {
        item.element.onclick = function () {
          if (item.isLoad) {
            if (callback_add) {
              callback_add(item.img);
            }
          } else {
            var inputFile = document.createElement('input');
            inputFile.setAttribute('type', 'file');

            inputFile.setAttribute('multiple', 'multiple');
            inputFile.setAttribute('accept', 'image/*,image/heif,image/heic');
            inputFile.setAttribute('class', 'all_images_to_upl');


            inputFile.onchange = function () {

              let pos = imgs.indexOf(item);

              for (let file of inputFile.files) {
                if (pos < imgs.length) {
                  let b = imgs[pos];

                  var reader = new FileReader();

                  reader.onload = function (e) {
                    loadImg(e.target.result, function (img) {
                      if (img) {
                        b.element.querySelector("img").src = img.original.src;
                        b.element.classList.add("isLoad");
                        b.img = img;
                        b.isLoad = true;


                        b.element.querySelector(".delete").onclick = function (e) {
                          b.isLoad = false;
                          b.element.classList.remove("isLoad");
                          e.stopImmediatePropagation();
                        }

                      } else {
                        b.element.classList.remove("isLoad");
                        b.isLoad = false;
                      }
                    });
                  }

                  reader.readAsDataURL(file);
                  pos++;
                }

              }
              document.body.appendChild(inputFile);
              // endchange
            }


            inputFile.click();
          }
        }
      }
      // DragAndDrop_img(item,type);
      editor.InitResource(item, type);

      if (item.element.src !== "") {
        loadImg(item.element.src, function (img) {
          if (img) {
            item.element.src = img.icon.src;
            item.img = img;
            item.isLoad = true;

          } else {
            item.isLoad = false;

          }
        });
      }
    });
  }

  function set_radius(value) {
    editor.radius = (15 / 100) * value;
  }

  function set_between(value) {
    editor.between = ((0.03 / 100) * value) - 0.002;

  }

  var range_radius = document.getElementById("range_radius");

  set_radius(parseFloat(range_radius.value));

  range_radius.onchange = range_radius.oninput = function () {
    set_radius(parseFloat(range_radius.value));
  }

  var range_between = document.getElementById("range_between");

  set_between(parseFloat(range_between.value));

  range_between.onchange = range_between.oninput = function () {
    set_between(parseFloat(range_between.value));
  }

  if ($('#imgs').length) {
    var imgs = Object.values(document.getElementById("imgs").getElementsByTagName("div")).map(function (element) { return { element: element, isOpen: true }; });
    InitImgs(imgs, "img", function (img) {
      if (editor.selection && editor.selection.cell)
        editor.selection.cell.setImg(img);
    });
    console.log(imgs)
  }

  // var backgrounds = Object.values(document.getElementById("backgrounds").getElementsByTagName("div")).map(function(img){return {element : img,isOpen:true};});

  // InitImgs(backgrounds,"background",function(img){

  //       editor.Background = img;
  //       editor.onchange();
  //   });
  function getBase64Image(img) {
    var canvas = document.createElement("canvas");
    canvas.width = img.width;
    canvas.height = img.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    var dataURL = canvas.toDataURL("image/png");
    return dataURL;
  }


  var imgs = document.querySelectorAll('.cs-bg-img_item img'),
    len = imgs.length,
    counter = 0;

  [].forEach.call(imgs, function (img) {
    if (img.complete)
      incrementCounter();
    else
      img.addEventListener('load', incrementCounter, false);
  });

  function incrementCounter() {
    counter++;
    if (counter === len) {
      $('.cs-bg-img_item').each(function () {
        let img = $(this).find('img')
        let base64 = getBase64Image(img[0]);
        img.attr('src', base64);

      })
      $('.cs-bg-img_item img').on('click', function () {

        let image = this;
        let nImage = new Image();
        nImage.src = image.src;
        let img = { original: nImage, icon: nImage }
        editor.setBackground(img);
      })
    }
  }


  var smiles = Object.values(document.getElementById("smiles").getElementsByTagName("img")).map(function (img) { return { element: img, isOpen: img.src == "" }; });
  InitSmiles(smiles);

  var TestOut = function () {//важно! изображения которые используются в редакторе не должны нарушать права
    var img = document.createElement("img");
    img.src = editor.out(/*width,height можно указать желаемый размер, isOffset включить отступ*/);
    document.body.appendChild(img);
  }

  // new
  var cur_text_font = document.getElementById("cur_text_font");

  var selectsFonts = [];


  function addFont(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, false);
    xhr.send();

    if (xhr.status == 200) {
      styleElement = document.createElement("style");
      styleElement.textContent = xhr.responseText;
      document.body.appendChild(styleElement);
      var fonts = {};

      for (var i = 0; i < styleElement.sheet.cssRules.length; ++i) {
        // fontFamily = styleElement.sheet.cssRules[i].style.fontFamily;
        fontFamily = styleElement.sheet.cssRules[i].style.getPropertyValue("font-family");
        fonts[fontFamily] = fontFamily;
        selectsFonts.push(fontFamily);
      }


      Object.values(fonts).forEach(function(font, i){
                    font = font.replace(/"/g, '');
                    cur_text_font.innerHTML += `<option value="${font}" style="font-family:${font}">${font}</option>`;
      });
    }
  }



  //добавляем шрифты по url
  addFont('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300&display=swap');
  addFont('https://fonts.googleapis.com/css2?family=Modak&display=swap');
  addFont('https://fonts.googleapis.com/css2?family=Fondamento&display=swap');
  addFont('https://fonts.googleapis.com/css2?family=Merienda+One&display=swap');
  addFont('https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap');
  addFont('https://fonts.googleapis.com/css2?family=Covered+By+Your+Grace&display=swap');





selectsFonts = [...new Set(selectsFonts)];

console.log(selectsFonts);
$('.font').on('click', function() {
  selectsFonts.forEach(function(font, i){
                  font = font.replace(/"/g, '');
                      console.log('first:' + i)
                  $('.select2-results__option').each(function(j, el) {
                      if (i === j) {
                          $(this).css('font-family', font);
                      }
              })
    });
})

$('.select2-selection__rendered').on('DOMSubtreeModified', function(){
  console.log('changed');
});
  //всплывающиее окна активного инструмента
  var windows = document.querySelectorAll(".tools .tool .window");
  for (var i = 0; i < windows.length; ++i) {
    var win = windows[i];

    win.addEventListener('click', function () {
      win.parentNode.blur();
    }, false);

    win.parentNode.onfocus = function () {
      var parent_rect = win.parentNode.getBoundingClientRect();
      var win_rect = win.getBoundingClientRect();
      if (parent_rect.y + parent_rect.height > window.innerHeight - win_rect.height) {
        if (parent_rect.y - win_rect.height > 0) {
          var y = parent_rect.y - win_rect.height;
        } else {
          y = window.innerHeight - win_rect.height;
        }
      } else {
        y = parent_rect.y + parent_rect.height;
      }

      if (parent_rect.x + parent_rect.width > window.innerWidth - win_rect.width) {
        if (parent_rect.x - win_rect.width > 0) {
          var x = parent_rect.x - win_rect.width;
        } else {
          x = window.innerWidth - win_rect.width;
        }
      } else {
        x = parent_rect.x + parent_rect.width;
      }

      win.style.left = x + "px";
      win.style.top = y + "px";
    }
  }

  //accordion меню
  var accordions = document.querySelectorAll(".PhotoEditor .accordion");
  function initAccordion(accordion) {
    var accordion_headers = accordion.querySelectorAll(".accordion_header");

    var old_active = accordion.querySelector(".accordion_active");

    var old_parent = accordion.parentNode;

    var h = accordion.clientHeight;

    if (h !== 0) {
      for (var i = 0; i < accordion_headers.length; ++i) {
        let header = accordion_headers[i];

        h -= header.clientHeight;
      }


      for (var i = 0; i < accordion_headers.length; ++i) {
        let header = accordion_headers[i];

        header.onclick = function (e) {
          if (header !== old_active) {
            if (old_active) old_active.classList.remove("accordion_active");
            header.classList.add("accordion_active");
            old_active = header;
          } else {
            header.classList.remove("accordion_active");
            old_active = undefined;
          }
          e.stopImmediatePropagation();
        }

        let body = header.nextElementSibling;
        if (body) {
          body.style.setProperty('--scrollHeight', h + "px");
        }
      }
    }


  }

  for (var i = 0; i < accordions.length; ++i) {
    initAccordion(accordions[i]);
  }
  $('.color-item, .color-item-txt').each(function () {
    let color = $(this).data('color');
    $(this).css('background', color);
  })
  $('.color-item').click(function () {
    let color = $(this).data('color');
    editor.changeBackgroundColor(color);
  })
  $('.color-item_own input').change(function () {
    let color = $(this).val();
    editor.changeBackgroundColor(color);
  })

  $('#cur_text_font').select2({
    minimumResultsForSearch: -1,
    width: '100%',
    height: '100%'
  });


  $(".cs-bg-add input").on("change", function () {
    let input = $(this);
    console.log(input)
    var reader = new FileReader();
    let container = $(this).closest(".product-download").find('.cs-bg-container');
    if (container.find(".cs-bg-limage").length == 3) {
      return false;
    }
    reader.onload = function () {
      container.append(
        `
      <div class="cs-bg-limage">
        <div class="close-bg"></div>
        <img width="109" height="109" src="${reader.result}" />
      </div>
      `
      );
      let target = container.find('.cs-bg-limage');
      target.on('click', function () {
        let image = $(this).find('img');
        let nImage = new Image();
        nImage.src = image[0].src;
        let img = { original: nImage, icon: nImage }
        editor.setBackground(img);
      })

      let close = target.find('.close-bg');
      close.on('click', function () {
        $(this).parent().remove();
      })
    };
    reader.readAsDataURL(this.files[0]);



  });
  $('.slider-tabs').on('click', 'a', function() {


  resize()


  })
</script>
