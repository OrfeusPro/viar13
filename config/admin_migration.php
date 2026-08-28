<?php

return [
    // Keep external invoice emails disabled until Filament invoice actions pass UAT.
    // When disabled, the complete mailable is rendered through Laravel's log mailer.
    'invoice_email_enabled' => (bool) env('ADMIN_INVOICE_EMAIL_ENABLED', false),

    // Keep payment emails and CRM webhooks isolated while Filament payment actions are under UAT.
    'payment_notifications_enabled' => (bool) env('ADMIN_PAYMENT_NOTIFICATIONS_ENABLED', false),
];
