# Cropper.js 1.5.11

Local distributable used by the Filament media library. This version matches
the original Voyager package-lock.json. JavaScript and CSS are unchanged.

Source: https://github.com/fengyuanchen/cropperjs/tree/v1.5.11
License: MIT (see LICENSE).

EXIF display rotation, wheel/pinch zoom, double-click drag-mode switching and
the eight crop handles use library defaults, as in Voyager. The server crops
the original pixel matrix, also as in Voyager; it does not apply EXIF rotation
before cropping. Existing legacy preview/server differences are preserved.
The standalone handler accepts areas outside the source image, with black
padding as in Voyager. The BREAD handler keeps its bounds validation. Both
handlers limit decoded/output memory.
