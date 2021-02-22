<?php

namespace App\Model;

class Curler
{

    /**
     * Returns the results of a curl GET request
     * @param $url
     * @return iterable|null
     */
    static function getCurlResult($url): ?iterable
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            if ($output) {
                return json_decode($output, true);
            } else {
                return null;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
