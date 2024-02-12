<?php


// Buat instance dari objek WScript.Shell
$wshShell = new COM('WScript.Shell');

// Tentukan spesifikasi kunci registri yang ingin dibaca
$key = 'HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\TimeZoneInformation';

$valueName = 'ActiveTimeBias';

// Gabungkan path kunci dan nama nilai
$valuePath = $key . '\\' . $valueName;

$value = $wshShell->RegRead($valuePath);

// Konversi nilai UTC offset dari menit ke detik
$utc_offset = -$value * 60;

// Tampilkan nilai UTC offset dalam format yang lebih mudah dibaca
$etcValue =  'UTC' . ($utc_offset < 0 ? '-' : '+') . gmdate('H:i', abs($utc_offset));

// Konversi nilai zona waktu Windows ke zona waktu IANA
$timezone_iana = UTCToTimezone($etcValue);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => $timezone_iana,

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'id_ID',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        // Add 
        Intervention\Image\ImageServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Js' => Illuminate\Support\Js::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        'Image' => Intervention\Image\Facades\Image::class,
    ],

    /**
     * To define API_URL. So that, it can
     * be called for every API Request.
     */
    'jwt_key' => env('JWT_KEY', 'ddwadu918412g4bkuaukawf91824g12uvakvaskuvuaf'),
    'jwt_alg' => env('JWT_ALG', 'HS256'),
    'jwt_exp' => env('JWT_EXP', '30'),
    'api_url' => env('API_URL', 'http://tassby.kozow.com:8888/emkl-backend/public/api/'),
    'server_mdn' => env('MDN_SERVER', "https://tasmdn.kozow.com:8074/"),
    'server_jkt' => env('JKT_SERVER', "http://tasjkt.kozow.com:8074/"),
    'server_sby' => env('SBY_SERVER', "http://tassby.kozow.com:8074/"),
    'server_mks' => env('MKS_SERVER', "http://tasmks.kozow.com:8074/"),
    'server_btg' => env('BTG_SERVER', "http://tasbtg.kozow.com:8074/"),
    'server_tnl' => env('TNL_SERVER', "http://localhost/"),
    'user_api' => env('USER_API', "ADMIN"),
    'pass_api' => env('PASSWORD_API', "RFV$*)123wsx"),
    'password_tnl' => env('PASSWORD_TNL', "RFV$*)123wsx"),
    'web_url' => env('WEB_URL', 'http://localhost/trucking/'),
    'ipinfo_token' => env('IPINFO_TOKEN', '54d41850c96dc3'),
    'url_tnl' => env('URL_TNL', "http://tasjkt.kozow.com:8074/truckingtnl-api/public/api/"),
    'pic_url_mdn' => env('PIC_URL_MDN', 'https://tasmdn.kozow.com:8073/Gambar/'),
    'pic_url_sby' => env('PIC_URL_SBY', 'http://tassby.kozow.com:8073/Gambar/'),
    'pic_url_mks' => env('PIC_URL_MKS', 'http://tasmks.kozow.com:8073/Gambar/'),
    'pic_url_btg' => env('PIC_URL_BTG', 'http://tasbtg.kozow.com:8073/Gambar/'),

    'client_id_mdn' => env('CLIENT_ID_MDN', '9aabd36f-ef75-4f0c-ab07-98e1fab647d9'),
    'client_secret_mdn' => env('CLIENT_SECRET_MDN', 'hmQU1A1dPgovUCydqyZu9tM8h5zev2HGIiShVrud'),
    'client_id_sby' => env('CLIENT_ID_SBY', '9aabf793-5231-4d07-a95f-d7757b8cf0d2'),
    'client_secret_sby' => env('CLIENT_SECRET_SBY', 'IG8iDnJICkWqJ98i1ApKEgS1aqgdvqODj8TpgNoy'),
    'client_id_mks' => env('CLIENT_ID_MKS', '9aade10f-fa50-4aee-bbbc-350c63218c49'),
    'client_secret_mks' => env('CLIENT_SECRET_MKS', 'xYu2dBEF13MegGKgnsrc1cVokg0iAWAuDubeMfow'),
    'client_id_btg' => env('CLIENT_ID_BTG', '9aafe29d-38b8-4359-9b9d-fca36bbd59e4'),
    'client_secret_btg' => env('CLIENT_SECRET_BTG', 'drbZs4mbOwHWBI48eBuFoeoYp1OLA6HMrHvh4eiR'),

    'url_token_mdn' => env('URL_TOKEN_MDN', 'https://tasmdn.kozow.com:8074/stok-api/public/api/oauth/token'),
    'url_token_sby' => env('URL_TOKEN_SBY', 'http://tassby.kozow.com:8074/stok-api/public/api/oauth/token'),
    'url_token_mks' => env('URL_TOKEN_MKS', 'http://tasmks.kozow.com:8074/stok-api/public/api/oauth/token'),
    'url_token_btg' => env('URL_TOKEN_BTG', 'http://tasbtg.kozow.com:8074/stok-api/public/api/oauth/token'),
    'url_token_jkt' => env('URL_TOKEN_JKT', 'http://tasjkt.kozow.com:8074/truckingdummy-api/public/api/token'),
    'url_token_jkttnl' => env('URL_TOKEN_JKTTNL', 'http://tasjkt.kozow.com:8074/truckingtnldummy-api/public/api/token'),

    'url_post_konsol_mdn' => env('URL_POST_KONSOL_MDN', 'https://tasmdn.kozow.com:8074/stok-api/public/api/stok/updateStok'),
    'url_post_konsol_sby' => env('URL_POST_KONSOL_SBY', 'http://tassby.kozow.com:8074/stok-api/public/api/stok/updateStok'),
    'url_post_konsol_mks' => env('URL_POST_KONSOL_MKS', 'http://tasmks.kozow.com:8074/stok-api/public/api/stok/updateStok'),
    'url_post_konsol_btg' => env('URL_POST_KONSOL_BTG', 'http://tasbtg.kozow.com:8074/stok-api/public/api/stok/updateStok'),
    'url_post_konsol_jkt' => env('URL_POST_KONSOL_JKT', 'http://tasjkt.kozow.com:8074/truckingdummy-api/public/api/stok/updatekonsolidasi'),
    'url_post_konsol_jkttnl' => env('URL_POST_KONSOL_JKTTNL', 'http://tasjkt.kozow.com:8074/truckingtnldummy-api/public/api/stok/updatekonsolidasi'),
    'kode_cabang' => env('KODE_CABANG', 'jkttnl'),
    'api_tnl' => env('API_TNL', "http://tasjkt.kozow.com:8074/truckingtnldummy-api/public/api/"),


];
