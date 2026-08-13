// fix for multilevel admin menu support
$('.panel-collapse').on('hide.bs.collapse', function(e) { 
    console.log('1');
    
    if($(event.target).parent().hasClass('collapsed')) { e.stopPropagation(); e.preventDefault(); }
 });

// fix for tinymce custom html tags support
function tinymce_init_callback(editor) {
    editor.remove();
    editor = null;
    tinymce.init({
        menubar: false,
        selector: "textarea.richTextBox",
        skin_url:
            $('meta[name="assets-path"]').attr("content") +
            "?path=js/skins/voyager",
        min_height: 600,
        resize: "vertical",
        plugins: "link, image, code, table, textcolor, lists",
        extended_valid_elements:
            "span[style|id|nam|class|lang]input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]",
        file_browser_callback: function (field_name, url, type, win) {
            $("#upload_file").trigger("click");
        },
        toolbar:
            "formatselect | bold italic strikethrough | forecolor backcolor | link image media | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | ltr rtl | removeformat | code",
        convert_urls: false,
        image_caption: true,
        image_title: true,
        content_css: "/admin_assets/timynce_css/style.css",
        valid_elements:
            "div[*],p[*],span[*],ul[*],li[*],ol[*],hr,br,img[*],i[*],em,table[*],tr[*],td[*],th[*],sup[*],sub[*],strong[*],b,h1[*],h2[*],h3[*],h4[*],h5[*],h6[*],small[*],a[*], svg,path",
        valid_children: "+li[span|p|div]",
        content_style:
            ".mce-annotation { background: #fff0b7; } .tc-active-annotation {background: #ffe168; color: black; }",
        external_plugins: {},
    });

    console.log("x2");
}

// admin change category type select => change child select
$("[name='id_type']").on("change", function() {
    var cur_val = $(this).val();

    $.ajax({
        type: 'POST',
        url: '/admin/set_sub_cats',
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
