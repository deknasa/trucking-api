<?php
$curlHandle = curl_init("http://localhost/trucking-apii/public/api/suratpengantarapprovalinputtrip/updateapproval");

$curlResponse = curl_exec($curlHandle);
curl_close($curlHandle);


?>