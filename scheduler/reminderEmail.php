<?php
// $curlHandle = curl_init("http://localhost/trucking-laravel/public/api/remainder-expstnk");
// $curlHandleOlimesin = curl_init("http://localhost/trucking-laravel/public/api/reminder-olimesin");
// $curlHandlesaringanhawa = curl_init("http://localhost/trucking-laravel/public/api/reminder-saringanhawa");

// $curlResponse = curl_exec($curlHandle);
// $curlResponseOlimesin = curl_exec($curlHandleOlimesin);
// $curlResponsesaringanhawa = curl_exec($curlHandlesaringanhawa);
// curl_close($curlHandle);
// curl_close($curlResponseOlimesin);
// curl_close($curlResponsesaringanhawa);


$json_data = file_get_contents('.\fileurl.json');

$data = json_decode($json_data);

// Inisialisasi beberapa handle cURL
$curlHandles[] = curl_init($data->app_url."public/api/reminder-expstnk");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-spk");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-olimesin");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-saringanhawa");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-perseneling");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-oligardan");
$curlHandles[] = curl_init($data->app_url."public/api/reminder-servicerutin");

// Set opsi shared di antara handles
// Contohnya, opsi dapat diatur dengan curl_setopt()
// Misalnya curl_setopt($curlHandle1, CURLOPT_RETURNTRANSFER, true);

// Inisialisasi multi-handle
$multiCurlHandle = curl_multi_init();

// Tambahkan setiap handle ke multi-handle
foreach ($curlHandles as $handle) {
    curl_multi_add_handle($multiCurlHandle, $handle);
}

// Jalankan request secara simultan
$running = null;
do {
    curl_multi_exec($multiCurlHandle, $running);
} while ($running > 0);

// Ambil respons dari setiap handle
foreach ($curlHandles as $handle) {
    $curlResponse = curl_multi_getcontent($handle);
    // Lakukan sesuatu dengan respons di sini
    echo $curlResponse;
    
    // Hapus handle dari multi-handle
    curl_multi_remove_handle($multiCurlHandle, $handle);
}

// Tutup multi-handle
curl_multi_close($multiCurlHandle);

?>