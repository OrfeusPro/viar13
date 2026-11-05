<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    protected function getPasswordFormComponent(): Component
    {
        // Mask before Alpine initializes, including when its runtime fails to load.
        return parent::getPasswordFormComponent()
            ->extraInputAttributes(['type' => 'password']);
    }
}
