<?php

namespace App\Services\AltGeneration;

use App\Services\AltGeneration\Exceptions\ImagePayloadException;
use DOMDocument;
use Symfony\Component\Process\Process;

class SvgRasterizer
{
    public function dataUrl(string $path): string
    {
        if (!is_file($path) || !is_readable($path) || filesize($path) > 12000000) {
            throw new ImagePayloadException('SVG needs a readable local file (maximum 12 MB).');
        }
        $svg = file_get_contents($path);
        if (stripos($svg, '<!ENTITY') !== false) {
            throw new ImagePayloadException('SVG entity declarations are not supported.');
        }
        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        $previous = libxml_use_internal_errors(true);
        try {
            $valid = $document->loadXML($svg, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (!$valid || !$document->documentElement || $document->documentElement->localName !== 'svg') {
            throw new ImagePayloadException('Invalid SVG image.');
        }
        // No filename/base URI is passed to librsvg. Send only the root element,
        // without external DTDs; reject references outside this image.
        foreach ($document->getElementsByTagName('*') as $element) {
            $element->removeAttributeNS('http://www.w3.org/XML/1998/namespace', 'base');
            foreach ($element->attributes as $attribute) {
                if ($attribute->localName === 'href' && !preg_match('#^(?:\#|data:image/(?:png|jpeg|webp|gif);base64,)#i', trim($attribute->value))) {
                    throw new ImagePayloadException('SVG external image references are not supported.');
                }
            }
        }
        $svg = $document->saveXML($document->documentElement);
        if (preg_match('/@import|url\(\s*[\x22\x27]?(?!\#)[^\s\x22\x27)]/i', $svg)) {
            throw new ImagePayloadException('SVG external CSS references are not supported.');
        }
        $process = new Process([(string) config('frontend_alt.svg_converter', 'rsvg-convert'),
            '--format=png', '--width=1024', '--height=1024', '--keep-aspect-ratio']);
        $process->setInput($svg);
        $process->setTimeout(20);
        try {
            $process->mustRun();
        } catch (\Throwable $exception) {
            throw new ImagePayloadException('SVG conversion failed. Install librsvg2-bin or configure ALT_SVG_CONVERTER.', 0, $exception);
        }
        $png = $process->getOutput();
        if (substr($png, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            throw new ImagePayloadException('SVG converter did not return PNG data.');
        }
        return 'data:image/png;base64,' . base64_encode($png);
    }
}
