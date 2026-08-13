<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="{{ asset('css/loadplay.css') }}">
    <script src="{{ asset('js/PhotoEditor2.js') }}"></script>
    <script src="{{ asset('js/loadplay.js') }}"></script>
</head>

<body>
    <script>
        var queryString = location.search.substring(1);
        var Param = {};
        var pairs = queryString.split('&');
        for (var i in pairs) {
            var split = pairs[i].split('=');
            Param[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
        }

        function download(file, blob) {
            var a = window.document.createElement('a');
            a.href = window.URL.createObjectURL(blob);
            a.download = file;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function dataURItoBlob(dataURI) {
            var byteString = atob(dataURI.split(',')[1]);
            var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            var blob = new Blob([ab], {
                type: mimeString
            });
            return blob;
        }

        if (Param['project']) {
            var project = JSON.parse(atob(Param['project']));

            if (project.type == "collage") {
                var editor = new PhotoEditor({
                    canvas: document.createElement("canvas")
                });

                const CmPixels = 25;

                const offset = 2.5;

                editor.openProject(project.project);

                editor.addEventListener("onload", function() {

                    var w = (project.size.centimeter_width + (offset * 2)) * CmPixels;
                    var h = (project.size.centimeter_height + (offset * 2)) * CmPixels;

                    var dataURI = editor.out(w, h);

                    download("source.jpg", dataURItoBlob(dataURI));

                    console.log("ok", w, h);
                });

            }
        }

    </script>
</body>

</html>
