<?php
$curlHandle = curl_init("http://localhost/trucking-laravel/public/api/remainder-expstnk");

$curlResponse = curl_exec($curlHandle);
curl_close($curlHandle);


?>