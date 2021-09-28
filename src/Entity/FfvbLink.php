<?php

namespace App\Entity;

class FfvbLink {

    public $ffvbLink = '';

    public $cal_saison = '';
    public $cal_codent = '';
    public $cal_codpoule = '';


    /**
     * @param string $ffvbLink
     */
    public function __construct(string $ffvbLink)
    {
        $query = parse_url($ffvbLink, PHP_URL_QUERY);
        parse_str($query, $params);
        $this->ffvbLink = $ffvbLink;

        $this->cal_saison =  $params['saison'];
        $this->cal_codent =  $params['codent'];
        $this->cal_codpoule =  $params['poule'];
    }

}