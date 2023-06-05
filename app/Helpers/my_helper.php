<?php

function UTCToTimezone($timezone)
{
    $map = array(
        'UTC+00:00' => 'Africa/Abidjan',
        'UTC+01:00' => 'Africa/Algiers',
        'UTC+02:00' => 'Africa/Blantyre',
        'UTC+03:00' => 'Africa/Djibouti',
        'UTC+04:00' => 'Asia/Baku',
        'UTC+05:00' => 'Asia/Aqtobe',
        'UTC+06:00' => 'Asia/Bishkek',
        'UTC+07:00' => 'Asia/Jakarta',
        'UTC+08:00' => 'Asia/Makassar',
        'UTC+09:00' => 'Asia/Jayapura',
        'UTC+10:00' => 'Australia/Sydney',
        'UTC+11:00' => 'Pacific/Noumea',
        'UTC+12:00' => 'Pacific/Fiji',
        'UTC-01:00' => 'Atlantic/Cape_Verde',
        'UTC-02:00' => 'America/Noronha',
        'UTC-03:00' => 'America/Sao_Paulo',
        'UTC-04:00' => 'America/Manaus',
        'UTC-05:00' => 'America/New_York',
        'UTC-06:00' => 'America/Mexico_City',
        'UTC-07:00' => 'America/Denver',
        'UTC-08:00' => 'America/Los_Angeles',
        'UTC-09:00' => 'Pacific/Gambier',
        'UTC-10:00' => 'Pacific/Honolulu',
        'UTC-11:00' => 'Pacific/Midway',
    );
    return isset($map[$timezone]) ? $map[$timezone] : '';


}

if (!function_exists('escapeLike')) {
    function escapeLike(string $string)
    {
        $search = ['%', '_', '[', "'"];
        $replace = ['|%', '|_', '|[',"''"];

        return str_replace($search, $replace, $string);
    }
}
