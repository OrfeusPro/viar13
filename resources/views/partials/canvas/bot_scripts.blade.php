<script>
    var _30_40 = '{!! $canvas_head['sizes_30x40'] !!}';
    var _38x38 = '{!! $canvas_head['sizes_38x38'] !!}';
    var _40x30 = '{!! $canvas_head['sizes_40x30'] !!}';
    var _60x30 = '{!! $canvas_head['sizes_60x30'] !!}';

    const sizes = [
        {default:"30x40",list:
            [
                _30_40
            ]
        },
        {default:"38x38",list:
            [
                _38x38
            ]
        },
        {default:"40x30",list:
            [
                _40x30
            ]
        },
        {default:"60x30",list:
            [
                _60x30
            ]
        }
    ];

    // tools
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

    function setForm(index)
    {
        var item = sizes[index];

        var s_html = '';
        var act;
        var act_h;
        var act_w;
        var act_depth;
        var act_price;

        document.getElementById("sizes").innerHTML = "<ul>"+ item.list.map(function(value){
                var i =0;

                 value.split(",").forEach(function(item){
                    i++;
                    var cur_price = item.match(/[^[\]]+(?=])/g);
                    var cur_price = JSON.stringify(cur_price).replace('[','').replace(']','');
                    cur_price = cur_price.replace('"','').replace('"','');

                    var price_clear = cur_price.split("-");
                    console.log(price_clear[0]);
                    console.log(price_clear[1]);


                    var size_clear = item.substring(0, item.indexOf('['));
                    var size = size_clear.split("x");

                    var w = parseInt(size[0]);
                    var h = parseInt(size[1]);
                    var depth = h * 0.07;

                    if(i == 1){
                        act = 'active';
                        act_h = h;
                        act_w = w;
                        act_depth = depth;
                        if(price_clear[1] == 'undefined'){
                        act_price = cur_price;
                        }else{
                        act_price = price_clear[1];
                        }
                    }else{
                        var act = '';
                    }

                    console.log(price_clear[1]);


                    if(price_clear[1] === undefined){

                        s_html+= `<li>
                            <a class="calcSize js_size ${act}"
                            data-size="${w}x${h}"
                            data-price="${cur_price*mult}"
                            href="javascript:void(0)" onclick="setSize(${h},${w},${depth});
                            [].forEach.call( document.getElementById('sizes').querySelectorAll('a'),function(el){
                                el.classList.remove('active');});
                                this.classList.add('active');">
                                <strong>${w}<span>{{ $int_globs['cm'] }}</span></strong> х <strong>${h}
                                <span>{{ $int_globs['cm'] }}</span></strong> - ${cur_price*mult} &euro;
                            </a></li>`;
                    }else{
                    s_html+= `<li>
                            <a class="calcSize js_size ${act}"
                            data-size="${w}x${h}"
                            data-price="${price_clear[1]*mult}"
                            href="javascript:void(0)" onclick="setSize(${h},${w},${depth});
                            [].forEach.call( document.getElementById('sizes').querySelectorAll('a'),function(el){
                                el.classList.remove('active');});
                                this.classList.add('active');">
                                <strong>${w}<span>{{ $int_globs['cm'] }}</span></strong> х <strong>${h}
                                <span>{{ $int_globs['cm'] }}</span></strong> -
                                <strike>${price_clear[0]*mult} &euro;</strike>
                                <strong style="color:#e2761d;">${price_clear[1]*mult}</strong> &euro;
                            </a></li>`;
                    }

                });
            }).join(" ")+"</ul>";

            $('#sizes').html('<ul>'+s_html+'</ul>');

            if(act != ''){
                setSize(act_h,act_w,act_depth);
                calcTotalPrice(act_w+'x'+act_h,act_price);
            }

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

                    inputFile.setAttribute('multiple', 'multiple');
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
            });
    }


    var imgs = Object.values(document.getElementById("imgs").getElementsByTagName("img")).map(function(img)
    {
        return {element : img,isOpen:true};
    });

    InitImgs(imgs,"img");

    $('a.calcForm.active').trigger('click');

</script>
