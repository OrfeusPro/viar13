// if(typeof tinymce != undefined){
//     tinymce.settings.extended_valid_elements = 'iframe,span[style|id|nam|class|lang]input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]';
//     tinymce.settings.valid_elements = 'div[*],p[*],span[*],ul[*],li[*],ol[*],hr,br,img[*],i[*],em,table[*],tr[*],td[*],th[*],sup[*],sub[*],strong[*],b,h1[*],h2[*],h3[*],h4[*],h5[*],h6[*],small[*],a[*], svg,path';
//     tinymce.settings.valid_children = '+li[span|p|div]';
// }

// console.log(tinymce);


$.ajaxSetup({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
});


// fix for multilevel admin menu support

    $('html .panel-collapse').on('hide.bs.collapse', function(e) {
        var target = $(event.target);
        if (!target.is('a')) {
            target = target.parent();
        }
        if (!target.hasClass('collapsed')) {
            return;
        }
        e.stopPropagation();
        e.preventDefault();
    });

if(typeof no_mce_image === 'undefined'){
    var config_list = "link, image, code, table, textcolor, lists";
}else{
    var config_list = "link, code, table, textcolor, lists";
}

// fix for tinymce custom html tags support
// function tinymce_init_callback(editor) {
//     try {
//         editor.remove();
//     } catch (error) {
//         console.log(error);
//     }
//     editor = null;
//
//
//     tinymce.init({
//         menubar: false,
//         selector: "textarea.richTextBox",
//         skin_url:
//             $('meta[name="assets-path"]').attr("content") +
//             "?path=js/skins/voyager",
//         min_height: 600,
//         resize: "vertical",
//         plugins: config_list,
//         extended_valid_elements:
//             "iframe,span[style|id|nam|class|lang]input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]",
//         file_browser_callback: function (field_name, url, type, win) {
//             $("#upload_file").trigger("click");
//         },
//         image_title: true,
//         automatic_uploads: true,
//         images_upload_url: '/admin/upload/tinyimage',
//         file_picker_types: 'image',
//         images_upload_credentials: true,
//         toolbar:
//             "formatselect | bold italic strikethrough | forecolor backcolor | link image media | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | ltr rtl | removeformat | code",
//         image_caption: true,
//         image_title: true,
//         convert_urls: false,
//         relative_urls: false,
//         remove_script_host: false,
//         content_css: "/admin_assets/timynce_css/style.css",
//         valid_elements:
//             "div[*],p[*],span[*],ul[*],li[*],ol[*],hr,br,img[*],i[*],em,table[*],tr[*],td[*],th[*],sup[*],sub[*],strong[*],b,h1[*],h2[*],h3[*],h4[*],h5[*],h6[*],small[*],a[*], svg,path",
//         valid_children: "+li[span|p|div]",
//         content_style:
//             ".mce-annotation { background: #fff0b7; } .tc-active-annotation {background: #ffe168; color: black; }",
//         external_plugins: {},
//     });
//
// }
//
// tinymce_init_callback();

// admin change category type select => change child select
$("[name='id_type']").on("change", function() {
    var cur_val = $(this).val();

    $.ajax({
        type: 'POST',
        url: '/admin/set_sub_cats',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            cat: cur_val
        }
    }).then(function (data) {
        var cat_select  = $("select[name='id_category']");
        cat_select.html('').select2();

        data.forEach(el => {
            cat_select.append(`<option value="${el.id}">${el.name}</option>`);
        });

        cat_select.trigger('change');

    });

});

// admin add custom audio upload
// if ($("[name='a_player']").length){
//     // check for create
//     var url_split = location.protocol + '//' + location.host + location.pathname
//     var parts = url_split.split('/');
//     var page_slug = parts[parts.length - 1];
//     if(page_slug == 'edit'){
//         $("[name='a_player']").parent().append(`
//             <form class="js_upl_audio_rev" style="display:flex;align-items:center;">
//                 <input type='file' required name='audio_upload' accept='audio/mpeg,mpga,mp3,wav'>
//                 <button type="button" class="js_up_click">Загрузить</button>
//             </form>
//         `);
//     }else{
//         $("[name='a_player']").parent().append(`<p>Оставить аудио-отзыв можно при редактировании созданного отзыва</p>`);
//     }
// };

// admin uplaod custom rev
$(document).on('click', '.js_up_click', function(event){
    event.preventDefault();

    var formData = new FormData();
    var form = $(this).closest('form');
    var lang = form.siblings('.js-language-label').text();

    var item = $('.form-edit-add').attr('action');
    var item_split = item.lastIndexOf("/");
    var item_id = item.substring(item_split+1);


    formData.append('audio_upload', $('input[name=audio_upload]')[0].files[0]);
    formData.append('lang', lang);
    formData.append('item_id', item_id);

    $.ajax({
        url: '/admin/upload_audio_rev',
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData
    }).then(function (data) {

        if(data.file_name){
            form.prev("[name='a_player']").val(data.file_name);
            $('.form-edit-add .panel-footer .btn-primary').trigger('click');
        }
    });

});

$('.datepicker').datetimepicker({
  format: 'DD-MM-yyyy'
});

// $("[name='a_player']").on("click", function(){
//     $(this).parent().append("<input type='file' name='js_upl_autdio'>");
// });



