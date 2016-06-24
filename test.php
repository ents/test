<?php

for (;;) {
	$text = `./go.sh`;

	file_put_contents("res/" . date("d-H-i-s") . ".txt", $text);

	echo ".";

	sleep(30);
}
