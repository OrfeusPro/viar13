
  @include(env('THEME_RESOURCES') . 'pages.collage.header_js')

  <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>

  <script>
    
    window.onload = function() {
      
      $.ajax({
        type: "GET",
        url: '/collage-js',
        success: function(data){
            $('.script').append(data);
            $('.collage__formalization .collage-formalization-row, .collage__formalization .formalization__block--bottom').css('opacity', 1);
            $('.collage-loading').fadeOut();
        }
      })
    }
  </script>
  <script src="{{ ver_asset(env('THEME').'js/script_canvas.js') }}?v=0.03" defer=""></script>
  <script data-defdel="{{ asset(env('THEME').'js/fancybox.js') }}"></script>
  <script data-defdel="{{ asset(env('THEME').'js/swal.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/before-after.min.js') }}"></script>
  <script src="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="{{ ver_asset(env('THEME').'js/main.js') }}"></script>


  <!--  -->

  <script type="text/javascript">
    window.onShowInterior = function () {
      return {
        size: {
          h: centimeter_height,
          w: centimeter_width
        },
        src: editor.out(editor.canvas.width, editor.canvas.height, false)
      };
    }
  </script>
  

  <script src="{{ ver_asset(env('THEME').'js/jquery.mask.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jquery.matchHeight.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jcf.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jcf.radio.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jcf.range.cs.js') }}"></script>

  <script src="{{ ver_asset(env('THEME').'js/jcf.checkbox.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jquery.collapse_storage.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/jquery.collapse.min.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/collage-constructor.min.js?v2') }}"></script> 

  <script src="{{ ver_asset(env('THEME').'spinner/jm.spinner.js') }}"></script>
  <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/interior.css') }}"> 
  <script src="{{ ver_asset(env('THEME').'js/interior.js') }}"></script>
  <script src="{{ ver_asset(env('THEME').'js/dropzone/dropzone.min.js') }}"></script>

  <div class="script">

  </div>




  <script src="{{ ver_asset(env('THEME').'js/email-decode.min.js') }}" data-cfasync="false"></script>
  <script src="{{ ver_asset(env('THEME').'js/rocket-loader.min.js') }}" data-cf-settings="73915442719c06acdf329ab4-|49" defer=""></script>




  
  
  