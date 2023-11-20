<?php

use Illuminate\Support\Str;

$path = getenv('PASSWORD_FILE');

$json_data = file_get_contents($path);

$data = json_decode($json_data);

$tasmdnTrucking = $data->trucking->tasmdn;
$tasjktTrucking = $data->trucking->tasjkt;
$tassbyTrucking = $data->trucking->tassby;
$tasbtgTrucking = $data->trucking->tasbtg;

$local = $data->trucking->local;

$tasmdnEmkl = $data->emkl->tasmdn;
$tasjktEmkl = $data->emkl->tasjkt;
$tassbyEmkl = $data->emkl->tassby;
$tasbtgEmkl = $data->emkl->tasbtg;


return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'web.transporindo.com'),
            'port' => env('DB_PORT', '1450'),
            'database' => env('DB_DATABASE', 'trucking'),
            'username' => env('DB_USERNAME', 'sa'),
            'password' => $local,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv2' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_DUA', 'web.transporindo.com'),
            'port' => env('DB_PORT_DUA', '1450'),
            'database' => env('DB_DATABASE_DUA', 'trucking'),
            'username' => env('DB_USERNAME_DUA', 'sa'),
            'password' => env('DB_PASSWORD_DUA', 'sa'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],         

        'sqlsrvmnd' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_BTG', 'tasbtg.kozow.com'),
            'port' => env('DB_PORT_BTG', '1450'),
            'database' => env('DB_DATABASE_BTG', 'TVPTTransporindoAgungSejahtera0001'),
            'username' => env('DB_USERNAME_BTG', 'sa'),
            'password' => $tasmdnTrucking,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],        

        'sqlsrvmks' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_MKS', 'tasmks.kozow.com'),
            'port' => env('DB_PORT_MKS', '1451'),
            'database' => env('DB_DATABASE_MKS', 'TVPTTransporindoAgungSejahteraMks0001'),
            'username' => env('DB_USERNAME_MKS', 'sa'),
            'password' => $tasmdnTrucking,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],      

        'sqlsrvsby' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_SBY', 'tassby.kozow.com'),
            'port' => env('DB_PORT_SBY', '1451'),
            'database' => env('DB_DATABASE_SBY', 'TVPTTransporindoAgungSejahteraSby0001'),
            'username' => env('DB_USERNAME_SBY', 'sa'),
            'password' => $tasmdnTrucking,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],      

        'sqlsrvmdn' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_MDN', 'tasmdn.kozow.com'),
            'port' => env('DB_PORT_MDN', '1460'),
            'database' => env('DB_DATABASE_MDN', 'TVPTTransporindoAgungSejahtera0001'),
            'username' => env('DB_USERNAME_MDN', 'sa'),
            'password' => $tasmdnTrucking,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],        

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
