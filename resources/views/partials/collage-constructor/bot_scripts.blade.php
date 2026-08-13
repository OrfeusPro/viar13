<script>
    window.onShowInterior = function() {
        return {
            size: {
                h: centimeter_height,
                w: centimeter_width
            },
            src: editor.out(editor.canvas.width, editor.canvas.height, false)
        };
    }
</script>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
<script src="{{ asset('js/jquery.matchHeight.min.js') }}"></script>
<script src="{{ asset('js/jcf.min.js') }}"></script>
<script src="{{ asset('js/jcf.file.min.js') }}"></script>
<script src="{{ asset('js/jcf.radio.min.js') }}"></script>
<script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
<script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
<script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
<script src="{{ asset('js/slick.min.js') }}"></script>
<script src="{{ asset('js/script.min.js') }}"></script>
<script src="{{ asset('js/PhotoEditor.js') }}"></script>
<script src="{{ asset('js/collage-constructor.min.js') }}?v2"></script>
<script async src="{{ asset('js/collage-templates.js?v3') }}"></script>
<script src="{{ asset('spinner/jm.spinner.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/interior.css') }}">
<script src="{{ asset('js/interior.js') }}"></script>
<script src="{{ asset('js/dropzone/dropzone.min.js') }}"></script>
