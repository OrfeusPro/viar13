<?php

namespace TCG\Voyager\Traits;

trait AlertsMessages
{
    protected array $alerts = [];

    protected function alert(array $alert): void
    {
        $this->alerts[] = $alert;
    }
}
