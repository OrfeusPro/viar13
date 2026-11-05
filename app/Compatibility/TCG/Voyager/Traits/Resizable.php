<?php

namespace TCG\Voyager\Traits;

trait Resizable
{
    public function thumbnail(string $type, string $attribute = 'image'): ?string
    {
        $image = $this->getAttribute($attribute);
        if (! is_string($image) || $image === '') {
            return $image;
        }

        $extension = pathinfo($image, PATHINFO_EXTENSION);
        if ($extension === '') {
            return $image;
        }

        $base = substr($image, 0, -strlen($extension) - 1);

        return $base.'-'.$type.'.'.$extension;
    }
}
