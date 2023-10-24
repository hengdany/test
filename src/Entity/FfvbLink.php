<?php

namespace App\Entity;

class FfvbLink {

    public $cal_saison = '';
    public $cal_codent = '';
    public $cal_codpoule = '';

    /**
     * @param string $ffvbLinkSubmitted
     * @return FfvbLink
     */
    static function createFromLink(string $ffvbLinkSubmitted) 
    {
        $path = parse_url($ffvbLinkSubmitted, PHP_URL_PATH);

        $ffvbLink = new FfvbLink();

        // Regionnaux
        if($path === '/ffvbapp/resu/vbspo_calendrier.php') {
            $query = parse_url($ffvbLinkSubmitted, PHP_URL_QUERY);
            parse_str($query, $params);

            $ffvbLink->cal_saison   = $params['saison'];
            $ffvbLink->cal_codent   = $params['codent'];
            $ffvbLink->cal_codpoule = $params['poule'];
        }

        // Nationnaux
        /**
         * Retrieve params in url page
         *  /SAISON/index_POULE/
         * eg : https://www.ffvbbeach.org/ffvbapp/resu/seniors/2021-2022/index_ema.htm
         */
        if(str_starts_with($path, '/ffvbapp/resu/seniors/')) {
            if (preg_match('/seniors\/(.*?)\/index/', $path, $match) === 1) {
                $ffvbLink->cal_saison = str_replace('-', '/', $match[1]);
            }
            if (preg_match('/index_(.*?)\.htm/', $path, $match) === 1) {
                $ffvbLink->cal_codpoule = strtoupper($match[1]);
            }
            $ffvbLink->cal_codent = 'ABCCS';
        }

        return $ffvbLink;
    }

}