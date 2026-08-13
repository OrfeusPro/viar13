const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/js/app.js', 'public/js')
//     .sass('resources/sass/app.scss', 'public/css');

mix.styles([
    'public/css/home.css',
], 'public/css/home_combine.css')
.version();


// global styles
mix.styles([
    'public/css/style.css',
    'public/css/mod.css',
    'public/js/dropzone/dropzone.min.css',
    'public/js/dropzone/basic.min.css',
    'public/spinner/jm.spinner.css',
], 'public/css/combine.css')
.version();

// global scripts
mix.scripts([
    'public/js/jquery3.min.js',
    'public/js/jquery.mask.min.js',
    'public/js/jquery.matchHeight.min.js',
    'public/js/script.min.js',
    'public/js/dropzone/dropzone.min.js',
    'public/spinner/jm.spinner.js',
], 'public/js/combine.js')
.version();

// canvas styles
mix.styles([
    'public/css/canvas.css',
    'public/css/canvas2.css',
    'public/css/icons_tools1.css',
    'public/css/interior.css',
], 'public/css/combine_canvas.css')
.version();

// canvs scripts
mix.scripts([
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jcf.range.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/templates.js',
    'public/js/canvas.min.js',
    'public/js/interior.js',
], 'public/js/combine_canvas.js')
.version();

// collage scripts
mix.scripts([
    'public/js/jquery3.min.js',
    'public/js/jquery.mask.min.js',
    'public/js/jquery.matchHeight.min.js',
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/script.min.js',
    'public/js/PhotoEditor.js',
    'public/js/PhotoEditor.js',
    'public/js/collage-constructor.min.js',
    'public/js/collage-templates.js',
    'public/spinner/jm.spinner.js',
    'public/js/interior.js',
], 'public/js/collage_combine.js')
.version();

// collage styles
mix.styles([
    'public/css/collage-constructor.css',
    'public/css/collage-constructor-2.css',
    'public/css/PhotoEditor.css',
    'public/css/icons_tools1.css',
], 'public/css/collage_combine.css')
.version();

// family scripts
mix.scripts([
    'public/js/jquery3.min.js',
    'public/js/jquery.mask.min.js',
    'public/js/jquery.matchHeight.min.js',
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jcf.range.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/modular-pictures.min.js',
    'public/js/templates.js',
    'public/js/collage-templates.js',
    'public/js/interior.js',

], 'public/js/family_combine.js')
.version();


// gallery scripts
mix.scripts([
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jcf.range.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/gallery-modular-one.min.js',
    'public/js/interior_2.js',

], 'public/js/gallery_combine.js')
.version();

// graph portrait scripts
mix.scripts([
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jcf.range.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/templates.js',
    'public/js/canvas.min.js',
    'public/js/modular-pictures-canvas.js',
    'public/js/modular-pictures-generator.js',

], 'public/js/graph_combine.js')
.version();


// graph portrait child
mix.scripts([
    'public/js/jquery.min.js',
    'public/js/jquery.mask.min.js',
    'public/js/jquery.matchHeight.min.js',
    'public/js/BeerSlider.min.js',
    'public/js/slick.min.js',
    'public/js/script.min.js',
    'public/js/graphic-interior.min.js',

], 'public/js/graph_child_combine.js')
.version();


// modular pictures
mix.scripts([
    'public/js/jcf.min.js',
    'public/js/jcf.file.min.js',
    'public/js/jcf.radio.min.js',
    'public/js/jcf.checkbox.min.js',
    'public/js/jquery.collapse_storage.min.js',
    'public/js/jquery.collapse.min.js',
    'public/js/slick.min.js',
    'public/js/modular-pictures.min.js',
    'public/js/templates.js',
    'public/js/modular-generator.js',

], 'public/js/modular_combine.js')
.version();