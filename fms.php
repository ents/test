<?php
require_once("functions.php");

function sendSms($text)
{
    $ch = curl_init("https://whotrades.com/api/internal/systems/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'method' => 'getFinamTenderSystemFactory.getSmsSender.sendSms',
        'params' => ['79160549864', $text],
    ]));
    $result = curl_exec($ch);
}

function testPlace($noPlace)
{
    $ch = curl_init("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment/appointment_schedule_view/?site_id=2&select=&district_id=&select=&document_id=364&select=&operation_id=165&select=");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIE, "session=78e79191f5716b1bb9cb9f0e5d64e1d2f6f29c08%7E576ac6f2466edjw5M; _ym_uid=1466444272166665534; _ym_isad=2; user_region=77");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36 OPR/38.0.2220.31");

	$t = microtime(true);
	$result = curl_exec($ch);
	$time = microtime(true) - $t;
	file_put_contents("timing.log", date("r") . ": " . sprintf("%.2f", $time) . "\n", FILE_APPEND);

    if ($err = curl_error($ch)) {
        sendSms('Error: #' . curl_errno($ch). ": " . $err);
        sleep(600);
    }

	$result = preg_replace('~<div class="b-captcha-image">.*?</div>~', '', $result);

	$md5 = md5($result);

	if ($md5 != $noPlace) {
		file_put_contents("html/res-".date("d-H-i-s").".html", $result);

        return $md5;
    }

    return false;
}

$noPlace = '40a0a19842c60ae396fbcee16addd1c7';

for (;;) {
    if ($res = testPlace($noPlace)) {
        $noPlace = $res;
		sendSms('New place: ' . date("H:i:s"));
		sleep(60);
    }

    echo ".";
    sleep(5);
}
