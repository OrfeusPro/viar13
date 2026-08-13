@php $page["title"] = __("pages.faq_bread_title"); @endphp
@include(env('THEME_RESOURCES') . 'pages.faq.breads',["page" => $page])

@include(env('THEME_RESOURCES') . 'pages.faq.item',["faqs" => $faqs])

@include(env('THEME_RESOURCES') . 'pages.index.services2', ["class" => "services spt"])
@include(env('THEME_RESOURCES') . 'pages.gallery.zpart_viarcanvas_is')
