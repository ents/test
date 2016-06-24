<?php
class Speech
{
    public $key;

    public function __construct($key = 'da55f455-a50f-4154-8eb6-bcfea5faeb9d')
    {
        $this->key = $key;
    }

    private function generateRandomSelection($min, $max, $count)
    {
        $result=array();
        if($min>$max) return $result;
        $count=min(max($count,0),$max-$min+1);
        while(count($result)<$count) {
            $value=rand($min,$max-count($result));
            foreach($result as $used) if($used<=$value) $value++; else break;
            $result[]=dechex($value);
            sort($result);
        }
        shuffle($result);
        return $result;
    }

    public function recognizeBin($data) {
        $uuid = $this->generateRandomSelection(0,30,64);
        $uuid = implode($uuid);
        $uuid = substr($uuid,1,32);
        $curl = curl_init();
        $url = 'https://asr.yandex.net/asr_xml?'.http_build_query(array(
                'key'=>$this->key,
                'uuid' => $uuid,
                'topic' => 'notes',
                'lang'=>'ru-RU'
            ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: audio/x-wav'));
        $response = curl_exec($curl);
        $err = curl_errno($curl);
        curl_close($curl);
        if ($err) {
            error("Curl error at yandex request #$err: " . curl_error($curl));

            return [];
        }

        $response .= preg_replace('~им в~sui', 'м', $response);

        if (preg_match_all('~<variant confidence="(-?[\d.]+)">(.*?)</variant>~sui', $response, $ans)) {
            $result = array_unique($ans[2]);

            $result = array_filter($result, function($text) {
                return preg_match('~^[\dабвгдежиклмнпрстуэюя ]+$~sui', $text);
            });

            $result = array_map(function($text){
                return mb_strtoupper(
                    implode(
                        "",
                        array_map(
                            function($str){
                                return mb_substr($str, 0, 1, 'utf8');
                            },
                            explode(' ', $text)
                        )
                    )
                );
            }, $result);

            $result = array_values(array_filter($result, function($text){
                return mb_strlen($text) == 5;
            }));

            return $result;
        }

        error("Can parse yandex response: $response");

        return [];
    }
}