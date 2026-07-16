<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/bilibiliAcconut.php';

$url = 'https://api.bilibili.com/x/space/bangumi/follow/list?type=1&follow_status=0&pn=1&ps=1&vmid=' . rawurlencode($UID);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0 Safari/537.36',
    'Referer: https://www.bilibili.com/',
    'Cookie: ' . $Cookie,
));

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(array(
        'error' => '获取追番总数失败',
        'detail' => $curlError,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$result = json_decode($response, true);
$total = $result['data']['total'] ?? null;

if (!is_numeric($total)) {
    http_response_code(502);
    echo json_encode(array(
        'error' => 'B 站接口未返回追番总数',
        'code' => $result['code'] ?? null,
        'message' => $result['message'] ?? '',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('total' => (int) $total), JSON_UNESCAPED_UNICODE);
