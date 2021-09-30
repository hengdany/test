<?php

namespace App\Entity;

class FfvbLink {

    public $cal_saison = '';
    public $cal_codent = '';
    public $cal_codpoule = '';

    /**
     * @param string $ffvbLink
     */
    public function __construct(string $ffvbLink)
    {
        $path = parse_url($ffvbLink, PHP_URL_PATH);

        // Regionnaux
        if($path === '/ffvbapp/resu/vbspo_calendrier.php') {
            $query = parse_url($ffvbLink, PHP_URL_QUERY);
            parse_str($query, $params);
            $this->ffvbLink = $ffvbLink;

            $this->cal_saison =  $params['saison'];
            $this->cal_codent =  $params['codent'];
            $this->cal_codpoule =  $params['poule'];
        }

        // Nationnaux
        /**
         * Retrieve params in url page
         *  /SAISON/index_POULE/
         * eg : https://www.ffvbbeach.org/ffvbapp/resu/seniors/2021-2022/index_ema.htm
         */
        if(str_starts_with($path, '/ffvbapp/resu/seniors/')) {
            if (preg_match('/seniors\/(.*?)\/index/', $path, $match) === 1) {
                $this->cal_saison = str_replace('-', '/', $match[1]);
            }
            if (preg_match('/index_(.*?)\.htm/', $path, $match) === 1) {
                $this->cal_codpoule = strtoupper($match[1]);
            }
            $this->cal_codent = 'ABCCS';
        }
    }

}