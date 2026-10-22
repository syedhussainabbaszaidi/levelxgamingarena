<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public site contact
    |--------------------------------------------------------------------------
    |
    | The business phone number stored in Admin → Settings is the primary
    | WhatsApp number. The value below is the fallback used before the
    | business record exists (fresh install) or when it has no phone number.
    | Use the international format without spaces or a leading plus.
    |
    */

    'whatsapp_number' => env('SITE_WHATSAPP_NUMBER', '923422631057'),

    'whatsapp_message' => env(
        'SITE_WHATSAPP_MESSAGE',
        'Hi, I would like to inquire about your gaming zone and activities.'
    ),

];
