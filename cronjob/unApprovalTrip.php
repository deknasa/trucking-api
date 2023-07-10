<?php
$curlHandle = curl_init('http://localhost/trucking-api/public/api/suratpengantarapprovalinputtrip/updateapproval');

$curlResponse = curl_exec($curlHandle);
curl_close($curlHandle);


?>