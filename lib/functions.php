<?php
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
}



function message($message)
{
    echo date("r") . ": $message\n";
}

function error($error)
{
    echo date("r") . ": [ERROR] : $error\n";
}


function sendSms($text)
{
    $ch = curl_init("https://whotrades.com/api/internal/systems/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'method' => 'getFinamTenderSystemFactory.getSmsSender.sendSms',
        'params' => ['79160549864', $text],
    ]));

    curl_exec($ch);
}


