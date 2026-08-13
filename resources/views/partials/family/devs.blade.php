<script>
         let form_data = new FormData();
        if($('.js_zone').length){
                Dropzone.autoDiscover = false;

                var myDropzone = new Dropzone(".js_zone", {
                addRemoveLinks: true,
                maxFilesize: 20,
                maxThumbnailFilesize: 20,
                maxFiles: 100,
                previewTemplate: document.querySelector('#template-preview').innerHTML,
                thumbnailWidth: 107,
                thumbnailHeight: 84,
                url: "/ajax_file_upload_handler/",
                autoProcessQueue: false,
                acceptedFiles: ".jpeg,.jpg,.png,.heic,.heif",
                init: function () {
                    var thisDropzone = this;
                    this.on('addedfile', function(file){
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            var image = new Image();
                            image.src = event.target.result;
                            image.onload = function() {
                                DragAndDrop_img({isLoad:true,element:file.previewElement,img:{icon:image,original:image}},"img");
                            };
                        };
                        reader.readAsDataURL(file);
                    })
                },
                })

            }

</script>

            <script>

            var size_price = 0;
            var size_size;
            var price_holst = 0;
            var dost_price  = 0;
            var pack_price = 0;

            // размер
            $(document).on('click', '.js_size', function(e){
                var size_price = $(this).data('price');
                var size_size = $(this).data('size');
                CalcTotalPrice();
            });

            // холст
            $(document).on('click', '.js__canv_radio .jcf-radio', function(e){
                CalcTotalPrice();
            });

            // упаковка
            $(document).on('click', '.comments .jcf-radio', function(e){
                CalcTotalPrice();
            });

            // сроки доставки
            $(document).on('click', '.dost_items label', function(e){
                setTimeout(() => {
                    CalcTotalPrice();
                }, 200);
            });

            $(document).on('change', '.js_pick_items input', function () {
                CalcTotalPrice();
            });

            var ram_price = 0;
            var rp = 0;
            var CalcTotalPrice = function()
            {
                size_price = $('.js_size.active').data('price');
                pack_price = $('.js_pick_items input:checked').val();

                dost_price = $('.dost_items .jcf-checked').find('input').val();

                // holst add
                let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
                let cur_sizes_add_coef = size_price * canvasTypePrice;

                let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

                if(cur_sizes_add == 0.00){
                    cur_sizes_add  = size_price;
                }
                // endholst

                var ram_price = $('.ramu-item.active').data('price');

                if(typeof ram_price === 'undefined'){
                    rp = 0;
                }else{
                        if($('.js_size.active'.length)){
                            var cur_size  = $('.js_size.active').data('size');
                            var cur_size_calc = cur_size.split("x");
                            var s1 = parseInt(cur_size_calc[0]);
                            var s2 = parseInt(cur_size_calc[1]);

                            rp = ((s1+s1+s2+s2)/100)*parseInt(ram_price);
                            if(rp != 0 && rp != undefined && rp != null){
                                $('.ramm__pr').remove();
                                var rm_txt = $('body').data('for_ram');
                                $('#totalSum').append("<span class='ramm__pr'>+"+Math.round(rp)+"€ "+rm_txt+"</span>");
                                $('.tabs-item1 .sum').append("<span class='ramm__pr'>+"+Math.round(rp)+"€ "+rm_txt+"</span>");
                            }

                        }else{
                            rp  = 0;
                        }
                }
                var total  = parseFloat(pack_price)+parseFloat(dost_price)+parseFloat(rp);

                total = parseFloat(total)+parseFloat(cur_sizes_add);

                total = total.toFixed(2);

                var total_without_rp = parseFloat(total) - parseFloat(rp);
                var total_without_rp = total_without_rp.toFixed(2);

                if(isNaN(total_without_rp)) return;


                $('.js_summ').attr('data-price', total);
                $('.js_summ').text(parseFloat(total_without_rp).toFixed(2));
                $('#totalPrice').text(parseFloat(total_without_rp).toFixed(2));
            }


            $(document).on('click', '.js_add_fam, .js_add_basket_module', function(e){
                e.preventDefault();
                CalcTotalPrice();

                $('.js_spinner').show(0);

                var price = $('.js_summ').data('price');

                var sz = $('.active.calcSize').data('size');

                if(typeof sz === 'undefined'){
                    $('.popup-inv-size-orig').addClass('active');
                    $('.js_spinner').hide(0);
                    return;
                }

                var sizes = sz.split('x');

                editor.isOffset = true;
                var canvas_img = editor.out(sizes[0]*25,sizes[1]*25, false);

                form_data.append('name', $('.js_page_name').text());
                form_data.append('price', price);
                form_data.append('image_offset', canvas_img);
                form_data.append('is_uploaded_img', 1);

                form_data.append('size', $('.js_size.active').data('size'));
                form_data.append('holst_id', $(".canvas-items .jcf-checked").parent().data('id'));
                form_data.append('userComment', $('#userComment').val());
                form_data.append('pack', $('.comments-item .jcf-checked').children('input').data('name'));
                form_data.append('dost_time', $('.dost_items .jcf-label-active input').val());


                if(ram_price>0){
                    form_data.append('ram_id', $('.ramu-item.active').find('input').val());
                }

                // all_images_to_upl
                // var dd_files = myDropzone.getAcceptedFiles();
                // for (let x = 0; x < dd_files.length; x++){
                //     form_data.append('orig_images[]', dd_files[x]);
                // }


                $.ajax({
                    type: 'post',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:$('#routes_links').data('bask_add_construct'),
                    data: form_data,
                    success: function (response) {
                        if (response) {
                            $('.js_spinner').hide(0);
                            var data = jQuery.parseJSON(response);
                            if (data['Error']) {
                                $('#err_msgs>div').text(price_err_text);
                                $('.popup-inv-size').addClass('active');
                            } else if (data['success']) {
                                $('.popup-bask-add').addClass('active');
                                // seo
                                var total_seo__price = $('.js_summ').data('price');
                                var page_name = $('.js_page_name').text();
                                window.dataLayer = window.dataLayer || [];
                                dataLayer.push({
                                'ecommerce': {
                                    'currencyCode': 'EUR',
                                    'add': {
                                    'actionField': {'list': page_name},
                                    'products': [{
                                        'name': page_name,
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


                                var cur_count = $('#smallCart').text();
                                cur_count = parseInt(cur_count);
                                cur_count++;
                                $('#smallCart').text(cur_count);
                            }
                        }
                    },
                    error: function (error) {
                    if (error.responseText) {
                        let response = JSON.parse(error.responseText);
                        $.each(response.errors, function (key, value) {
                            alert(value[0])
                        });
                    } else {
                        alert('Server error');
                    }
                    $('.js_spinner').hide(0);

                    }
                });


            });

            // tab2

            $(document).on('click', '.ramu-item', function(e){
                ram_price = $(this).data('price');
                CalcTotalPrice();
            });
    </script>



    <script>
        $('.slider-tabs li a').on('click', function(e){
            var tab = $(this).data('tabs');

            if(tab == 'tabs-item2'){
                var int_i = $('.interior-item.active').data('item');
                $('.range__box').eq(int_i).show();
            }
        });

        $(document).on('click', '.interior-item, .ramu-item', function(e){
            var img_border = $('.ramu-item.active').find('img').attr('src');
            var bi_full = 'url('+img_border+') 65 68 / 5em stretch';
        });

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


        var tool_undo = document.getElementById("undo");
        var tool_redo = document.getElementById("redo");
        var tool_photo_zoom_minus = document.getElementById("photo_zoom_minus");
        var tool_photo_zoom_plus = document.getElementById("photo_zoom_plus");
        var tool_turn_right = document.getElementById("turn_right");
        var tool_turn_left = document.getElementById("turn_left");
        var tool_cell_delete = document.getElementById("cell_delete");



        var canvas = document.getElementById("canvas");





        var editor = new Canvas3D(canvas);
        editor.ColorCell = '#d2afac';
        editor.Background_color = "#e1e9ec";




        function resize()
        {
            var mn = canvas.parentElement.clientWidth;
            canvas.height = mn;
            canvas.width  = mn;
            editor.setSize(canvas.height,canvas.width);
        }


        var old_clientWidth, old_clientHeight;

        setInterval(function()
        {
            if (canvas.parentElement.clientWidth !== old_clientWidth || canvas.parentElement.clientHeight !== old_clientHeight)
            old_clientHeight = canvas.parentElement.clientHeight;
            old_clientWidth = canvas.parentElement.clientWidth;
            resize();
        },1);



        window.addEventListener("resize",resize,true);


        resize();



        editor.open_template("W3sicDEiOnsieCI6MC42NjY2NjY2NjY2NjY2NjY2LCJ5IjowLjMzMzMzMzMzMzMzMzMzMzJ9LCJwMiI6eyJ4IjoxLCJ5IjoxfX0seyJwMSI6eyJ4IjowLCJ5IjowLjY2NjY2NjY2NjY2NjY2NjV9LCJwMiI6eyJ4IjowLjMzMzMzMzMzMzMzMzMzMzMsInkiOjF9fSx7InAxIjp7IngiOjAuMzMzMzMzMzMzMzMzMzMzMywieSI6MC40OTk5OTk5OTk5OTk5OTk3fSwicDIiOnsieCI6MC42NjY2NjY2NjY2NjY2NjY2LCJ5IjoxfX0seyJwMSI6eyJ4IjowLjMzMzMzMzMzMzMzMzMzMzMsInkiOjB9LCJwMiI6eyJ4IjowLjY2NjY2NjY2NjY2NjY2NjYsInkiOjAuNDk5OTk5OTk5OTk5OTk5N319LHsicDEiOnsieCI6MC42NjY2NjY2NjY2NjY2NjY2LCJ5IjowfSwicDIiOnsieCI6MSwieSI6MC4zMzMzMzMzMzMzMzMzMzMyfX0seyJwMSI6eyJ4IjowLCJ5IjowfSwicDIiOnsieCI6MC4zMzMzMzMzMzMzMzMzMzMzLCJ5IjowLjY2NjY2NjY2NjY2NjY2NjV9fV0=");


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

        editor.radius = 0;
        editor.between = 0.01;
        editor.isOffset = true;

        // function setSize(centimeter_h,centimeter_w,centimeter_depth)
        // {
        //     var mx = Math.max(centimeter_h,centimeter_w);
        //     var h = (1 / mx) * centimeter_h;
        //     var w = (1 / mx) * centimeter_w;
        //     var depth = (1 / mx) * centimeter_depth;
        //     editor.setSizeBox(h,w,depth);
        //     resize();
        // }
        function setSize(centimeter_h,centimeter_w,centimeter_depth)
                {

                    size_centimeter_h = centimeter_h;
                    size_centimeter_w = centimeter_w;
                    var mx = Math.max(centimeter_h,centimeter_w);
                    var h = (1 / mx) * centimeter_h;
                    var w = (1 / mx) * centimeter_w;
                    var depth = (1 / mx) * centimeter_depth;
                    editor.setSizeBox(h,w,depth);
                    resize();
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
            r.src = canvas.toDataURL('image/png', 1.0);

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

        var inputFile;
        function InitImgs(imgs,type)
        {

            imgs.forEach(function(item){
                    if (item.isOpen)
                    {
                        item.element.onclick = function()
                        {
                            inputFile = document.createElement('input');
                            inputFile.setAttribute('type', 'file');

                            inputFile.setAttribute('multiple', 'multiple');
                            inputFile.setAttribute('accept', 'image/*,image/heif,image/heic');
                            inputFile.setAttribute('class', 'all_images_to_upl');


                            inputFile.onchange = function()
                            {
                                let pos = imgs.indexOf(item);

                                for (let file of inputFile.files)
                                {
                                    if (pos < imgs.length)
                                    {
                                        let b = imgs[pos];

                                        var reader = new FileReader();

                                        reader.onload = function (e)
                                        {
                                            loadImg(e.target.result,function(img){
                                                    if (img)
                                                    {
                                                        b.element.src= img.icon.src;
                                                        b.img = img;
                                                        b.isLoad = true;

                                                    }else
                                                    {
                                                        b.isLoad = false;
                                                    }
                                                });
                                        }

                                        reader.readAsDataURL(file);


                                        pos++;
                                    }

                                }
                                document.body.appendChild( inputFile );
                                // endchange
                            }


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
                });
        }




        var imgs = Object.values(document.getElementById("imgs").getElementsByTagName("img")).map(function(img)
        {
            return {element : img, isOpen:true};
        }
        );

        InitImgs(imgs,"img");




        //       document.getElementById("out").onclick = function()
        //       {//важно! изображения которые используются в редакторе не должны нарушать права
        //           var img = document.createElement("img");
        //           img.src = editor.out(/*width,height можно указать желаемый размер*/);
        //           document.body.appendChild(img);
        //       }

        function get_var(var_name){
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                    var pair = vars[i].split("=");
                    if(pair[0] == var_name){return pair[1];}
            }
            return(false);
        }

        var var_param = get_var("item");
        if (var_param !== '' && var_param != false) {
            editor.open_template(templates[var_param].grid);
        }

    </script>
