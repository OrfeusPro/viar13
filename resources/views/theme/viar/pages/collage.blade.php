 @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.breads')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.slider')

 <div>
     @php
         $name=__('collage_new.c_slider_title');
  $strippedName = strip_tags($name);
  $cleanName = str_replace('"', '', $strippedName);
  $s_items = $item->getMedia('our_works_new');
     @endphp
     <meta itemprop="name" content="{{ $cleanName }}" />




    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.why_screen')


    <!-- advantage and popular combine -->
    <div class="general__screen collage_pop-advant">
        {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.advantage_screen') --}}
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.popular_screen')
    </div>

    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.order_screen')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.f_order_screen')
    {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.doubt_screen') --}}
    {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.love_screen') --}}
    {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.generator_info') --}}
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.generator')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.why')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.index.services2')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about', ['info_block' => "hidden"])
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.index.faq9')

 </div>

 @include(  'partials.schema_reviews')



