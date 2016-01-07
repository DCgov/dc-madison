<?php

return [
    'APP_DEBUG'         => getenv('APP_DEBUG') ?: false,
    'DB_DRIVER'         => 'mysql',
    'DB_HOST'           => 'localhost',
    'DB_DATABASE'       => 'madison',
    'DB_USERNAME'       => 'homestead',
    'DB_PASSWORD'       => 'secret',

    'MAIL_DRIVER'       => 'smtp',
    'MAIL_HOST'         => 'mailtrap.io',
    'MAIL_PORT'         => '2525',
    'MAIL_USERNAME'     => 'null',
    'MAIL_PASSWORD'     => 'null',
    'MAIL_FROM_ADDRESS' => 'user@mail.com',
    'MAIL_FROM_NAME'    => 'user',

    'ADMIN_EMAIL'       => 'admin@example.com',
    'ADMIN_PASSWORD'    => 'secret',

    'FB_CLIENT_ID'      => '',
    'FB_CLIENT_SECRET'  => '',
    'TW_CLIENT_ID'      => '',
    'TW_CLIENT_SECRET'  => '',
    'LI_CLIENT_ID'      => '',
    'LI_CLIENT_SECRET'  => '',

    // This should be set to whatever domain your local Madison instance is
    // running on so the session cookie will be set appropriately
    'COOKIE_DOMAIN'     => 'madison.local',

    //This is the uservoice javascript file hash in the UV uv.src embed code
    //Used as such: uv.src = '//widget.uservoice.com/' + uservoiceHash + '.js';
    'USERVOICE'         => '',

    //Your Google Analytics account (eg. 'UA-00000000-0')
    'GA'                => '',

];
