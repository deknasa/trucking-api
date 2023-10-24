<?php
$curlHandle = curl_init("http://localhost/trucking-laravel/public/api/remainder-expstnk");
$curlHandleOlimesin = curl_init("http://localhost/trucking-laravel/public/api/reminder-olimesin");

$curlResponse = curl_exec($curlHandle);
$curlResponseOlimesin = curl_exec($curlHandleOlimesin);
curl_close($curlHandle);
curl_close($curlResponseOlimesin);


?>