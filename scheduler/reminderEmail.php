<?php
$curlHandle = curl_init("http://localhost/trucking-laravel/public/api/remainder-expstnk");
$curlHandleOlimesin = curl_init("http://localhost/trucking-laravel/public/api/reminder-olimesin");
$curlHandlesaringanhawa = curl_init("http://localhost/trucking-laravel/public/api/reminder-saringanhawa");

$curlResponse = curl_exec($curlHandle);
$curlResponseOlimesin = curl_exec($curlHandleOlimesin);
$curlResponsesaringanhawa = curl_exec($curlHandlesaringanhawa);
curl_close($curlHandle);
curl_close($curlResponseOlimesin);
curl_close($curlResponsesaringanhawa);


?>