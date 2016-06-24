<?php
class Parser
{
    public $sessionId;
    public $cookieFile;
    private $prevMd5 = null;

    public function __construct($cookieFile, $sessionId = 'f49b1b8c81389765c6139c53e2e47abad2b976eb%7E576c1e5f2d63f3oFu')
    {
        $this->sessionId = $sessionId;
        $this->cookieFile = $cookieFile;
    }

    private function createCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIE, "_ym_uid=1466444272166665534; _ym_isad=2; user_region=77");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36");

        message("Ch for $url created");

        return $ch;
    }

    public function testPlace()
    {
        $ch = $this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment/appointment_schedule_view/?site_id=2&select=&district_id=&select=&document_id=364&select=&operation_id=165&select=");

        $t = microtime(true);
        $result = curl_exec($ch);
        $time = microtime(true) - $t;
        file_put_contents("timing.log", date("r") . ": " . sprintf("%.2f", $time) . "\n", FILE_APPEND);

        if ($err = curl_error($ch)) {
            error('Error: #' . curl_errno($ch). ": " . $err);
            sleep(600);
        }

        $result = preg_replace('~<div class="b-captcha-image">.*?</div>~', '', $result);

        $md5 = md5($result);

        if (!$this->prevMd5) {
            $this->prevMd5 = $md5;
        }

        if ($md5 != $this->prevMd5) {
            $this->prevMd5 = $md5;
            message("New content found with md5=$md5");
            file_put_contents("html/res-".date("d-H-i-s").".html", $result);

            return $md5;
        }

        return false;
    }

    public function getAudioContent()
    {
        $ch = $this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/captcha/audio");
        $url = curl_exec($ch);

        if ($err = curl_error($ch)) {
            error('Error during get audio url: #' . curl_errno($ch). ": " . $err);

            return null;
        }

        $ch = $this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai$url");
        $audio =  curl_exec($ch);
        if ($err = curl_error($ch)) {
            error('Error during get audio content: #' . curl_errno($ch). ": " . $err);

            return null;
        }

        if (false !== strpos($audio, '<!DOCTYPE html>')) {
            error('HTML got during get audio content');

            return null;
        }

        return $audio;
    }


    public function refreshCaptcha()
    {
        curl_exec($this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/captcha/?0." . rand(10**17, 10**18) . rand(10, 99)));
    }

    public function registerPerson(
        $time,
        $date,
        $captcha,
        $lastname = 'Науменко',
        $firstname = 'Артем',
        $pasport = 'ER310018',
        $address = 'Москва, ул. Милашенкова 3к1, кв 186',
        $phone = '+7 916 054 9864',
        $email = 'entsupml@gmail.com'
    ) {
        //curl "https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment/" \
        // -H "Pragma: no-cache"\
        // -H "Origin: https://xn--b1ab2a0a.xn--b1aew.xn--p1ai" \
        // -H "Accept-Encoding: deflate"\
        // -H "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4" \
        // -H "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36" \
        // -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"\
        // -H "Accept: application/json, text/javascript, */*; q=0.01"\
        // -H "Cache-Control: no-cache"\
        // -H "X-Requested-With: XMLHttpRequest"\
        // -H "Cookie: session=69e3bf392c5731d1c4c3591f5e6056df0d11bd58"%"7E576bc398e015a2Ikz; _ym_uid=1466680217906376890; _ym_isad=2; user_region=77"\
        // -H "Connection: keep-alive"\
        // -H "Referer: https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment"\
        // --data 'site_id=2&select=&district_id=&select=&document_id=364&select=&operation_id=165&select=&division_id=20&select=&time=&date=&lastname=%D0%9D%D0%B0%D1%83%D0%BC%D0%B5%D0%BD%D0%BA%D0%BE&firstname=%D0%90%D1%80%D1%82%D0%B5%D0%BC&patronymic=&pdoctype=1&select=&pdocnumber=ER310018&address=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%2C+%D1%83%D0%BB.+%D0%9C%D0%B8%D0%BB%D0%B0%D1%88%D0%B5%D0%BD%D0%BA%D0%BE%D0%B2%D0%B0+3%D0%BA1%2C+%D0%BA%D0%B2+186&phone=%2B7+916+054+9864&email=entsupml% 40gmail.com &captcha=site_id=2&select=&district_id=&select=&document_id=364&select=&operation_id=165&select=&division_id=20&select=&time=&date=&lastname=%D0%9D%D0%B0%D1%83%D0%BC%D0%B5%D0%BD%D0%BA%D0%BE&firstname=%D0%90%D1%80%D1%82%D0%B5%D0%BC&patronymic=&pdoctype=1&select=&pdocnumber=ER310018&address=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%2C+%D1%83%D0%BB.+%D0%9C%D0%B8%D0%BB%D0%B0%D1%88%D0%B5%D0%BD%D0%BA%D0%BE%D0%B2%D0%B0+3%D0%BA1%2C+%D0%BA%D0%B2+186&phone=%2B7+916+054+9864&email=entsupml% 40gmail.com&captcha='`php -r 'echo urlencode("ПУСИЮ");'`\
        // | jq .

        $ch = $this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Pragma: no-cache',
            'Origin: https://xn--b1ab2a0a.xn--b1aew.xn--p1ai',
            'Accept-Encoding: deflate',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
        ]);
        curl_setopt($ch, CURLOPT_REFERER, 'https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $request = [
            'site_id' => '2',
            'district_id' => '',
            'document_id' => '364',
            'operation_id' => '165',
            'division_id' => '20',
            'time' => $time,
            'date' => $date,
            'lastname' => $lastname,
            'firstname' => $firstname,
            'patronymic' => '',
            'pdoctype' => '1',
            'select' => '',
            'pdocnumber' => $pasport,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'captcha' => $captcha,
        ];

        message("Request: " . json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));

        $response = curl_exec($ch);

        if ($err = curl_error($ch)) {
            error('Error during get audio content: #' . curl_errno($ch). ": " . $err);

            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error()) {
            $filename = 'res/' . md5($response) . ".txt";
            file_put_contents($filename, $response);
            error("Invalid json receiver by registering person, saved to $filename");
        }

        return $data;
    }

    public function getAvailableHours($date)
    {
        //return ['hours' => ['']];
        $ch = $this->createCurl("https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment/appointment_hours/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Pragma: no-cache',
            'Origin: https://xn--b1ab2a0a.xn--b1aew.xn--p1ai',
            'Accept-Encoding: deflate',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
        ]);
        curl_setopt($ch, CURLOPT_REFERER, 'https://xn--b1ab2a0a.xn--b1aew.xn--p1ai/services/appointment');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'site_id' => '2',
            'district_id' => '',
            'document_id' => '364',
            'operation_id' => '165',
            'division_id' => '20',
            'time' => '',
            'date' => $date,
            'lastname' => '',
            'firstname' => '',
            'patronymic' => '',
            'pdoctype' => '1',
            'select' => '',
            'pdocnumber' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'captcha' => '',
        ]));

        $t = microtime(true);
        $response = curl_exec($ch);
        $time = sprintf("%.3f", microtime(true) - $t);

        message("Time for /services/appointment/appointment_hours/: $time s");

        if ($err = curl_error($ch)) {
            error('Error during get audio content: #' . curl_errno($ch). ": " . $err);

            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error()) {
            $filename = 'res/' . md5($response) . ".txt";
            file_put_contents($filename, $response);
            error("Invalid json receiver by registering person, saved to $filename");
        }

        return $data;
    }
}