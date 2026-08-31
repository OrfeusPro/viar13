<?php

return [
    // Legacy order artwork is served from production, without local copies.
    'order_media_base_url' => env('ADMIN_ORDER_MEDIA_BASE_URL', 'https://viarcanvas.com'),

    // Keep external invoice emails disabled until Filament invoice actions pass UAT.
    // When disabled, the complete mailable is rendered through Laravel's log mailer.
    'invoice_email_enabled' => (bool) env('ADMIN_INVOICE_EMAIL_ENABLED', false),

    // Keep payment emails and CRM webhooks isolated while Filament payment actions are under UAT.
    'payment_notifications_enabled' => (bool) env('ADMIN_PAYMENT_NOTIFICATIONS_ENABLED', false),

    // Keep one-recipient admin emails disabled until the Filament compose action passes UAT.
    'recipient_email_enabled' => (bool) env('ADMIN_RECIPIENT_EMAIL_ENABLED', false),

    // Keep review-request emails disabled until the Filament product column passes UAT.
    'review_request_enabled' => (bool) env('ADMIN_REVIEW_REQUEST_ENABLED', false),

    // Keep client-chat email and CRM notifications disabled until chat UAT is complete.
    'client_chat_notifications_enabled' => (bool) env('ADMIN_CLIENT_CHAT_NOTIFICATIONS_ENABLED', false),
];
