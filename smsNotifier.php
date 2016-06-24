<?php
require_once("init.php");

$cookieFile = $_SERVER['argv'][1];
$parser = new Parser($cookieFile);

for (;;) {
    if ($res = $parser->testPlace()) {
        $noPlace = $res;
		sendSms('New place: ' . date("H:i:s"));
		sleep(60);
    }

    echo ".";
    sleep(5);
}
