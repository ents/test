<?php
require_once('init.php');

$cookieFile = $_SERVER['argv'][1];
$date = $_SERVER['argv'][2];

$parser = new Parser($cookieFile);
$speech = new Speech();

message("Start monitoring date $date");
for (;;) {
	$hours = $parser->getAvailableHours($date);
	if (empty($hours['hours'])) {
		sleep(rand(5, 10));
		continue;
	}

	$parser->refreshCaptcha();
	$audio = $parser->getAudioContent();

	if ($audio) {
		$list = $speech->recognizeBin($audio);

		foreach ($list as $captcha) {
			$result = $parser->registerPerson(
				$hours['hours'][array_rand($hours['hours'])],
				$date,
				$captcha
			);

			var_export($result);

			if (empty($result['errors'])) {
				log("Registration success! " . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
				sendSms("Registration success: " . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
				return;
			}

			if (!empty($result['errors']) && empty($result['errors']['captcha'])) {
				message("Captcha recognized successful, but other error found");
				$json = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
				if (mb_strlen($json) < 200) {
					$mess = "Response: $json";
				} else {
					$mess = "See details at log";
				}
				sendSms("Registration failed, captcha no error. $mess");
				return;
			}

		}

		error("Captcha recognized failed");
	}
}
