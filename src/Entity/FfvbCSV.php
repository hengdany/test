<?php

namespace App\Entity;

class FfvbCSV {

    const LINK_DOWNLOAD_CSV = 'http://www.ffvbbeach.org/ffvbapp/resu/vbspo_calendrier_export.php';

    const ENTITY           = 'Entité';
    const JOUR             = 'Jo';
    const MATCH            = 'Match';
    const DATE             = 'Date';
    const HEURE            = 'Heure';
    const EQUIPE_A_NUMERO  = 'EQA_no';
    const EQUIPE_A_NOM     = 'EQA_nom';
    const EQUIPE_B_NUMERO  = 'EQB_no';
    const EQUIPE_B_NOM     = 'EQB_nom';
    const SET              = 'Set';
    const SCORE            = 'Score';
    const TOTAL            = 'Total';
    const SALLE            = 'Salle';
    const ARBITRE_1        = 'Arb1';
    const ARBITRE_2        = 'Arb2';

    const HEADER_CSV_FFVB = [
       self::ENTITY,
       self::JOUR,
       self::MATCH,
       self::DATE,
       self::HEURE,
       self::EQUIPE_A_NUMERO,
       self::EQUIPE_A_NOM,
       self::EQUIPE_B_NUMERO,
       self::EQUIPE_B_NOM,
       self::SET,
       self::SCORE,
       self::TOTAL,
       self::SALLE,
       self::ARBITRE_1,
       self::ARBITRE_2
    ];

    const HEADER_CSV_GOOGLE = [
        "Subject",
        "Start Date",
        "Start Time",
        "End Date",
        "End Time",
        "All Day Event",
        "Description",
        "Location"
    ];

    /**
     * @var FfvbLink
     */
    private $ffvbLink;

    /**
     * CSV, associative array format
     *
     * @var array
     */
    private $ffvbCsv;

    /**
     * @param FfvbLink $ffvbLink
     */
    public function __construct(FfvbLink $ffvbLink)
    {
        $this->ffvbLink = $ffvbLink;
    }

    /**
     * Set FFVB calendar CSV, associative array formatted
     * ex : ffvbCalendar[10]['Entité'] = 10th row entity
     */
    public function setFFVBCalendar() {

        $data = array(
            'cal_saison' => $this->ffvbLink->cal_saison,
            'cal_codent' => $this->ffvbLink->cal_codent,
            'cal_codpoule' => $this->ffvbLink->cal_codpoule
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $ffvbCSVRaw = file_get_contents(self::LINK_DOWNLOAD_CSV, false, $context);

        // Convert encoding to UTF-8
        $ffvbCSVDecoded = mb_convert_encoding($ffvbCSVRaw, 'UTF-8',
            mb_detect_encoding($ffvbCSVRaw, 'UTF-8, ISO-8859-1', true));

        $ffvbCSVDecodedRows = explode("\n",$ffvbCSVDecoded);
        $ffvbCSVAssociatives = array();
        foreach($ffvbCSVDecodedRows as $row) {
            $ffvbCSVAssociatives[] = str_getcsv($row, ';');
        }

        $headers = array_shift($ffvbCSVAssociatives);
        $ffvbCsv    = array();

        foreach($ffvbCSVAssociatives as $row) {

            // prevent unequal length of header
            $combination = array();
            $count= 0;
            foreach ($headers as $header)
            {
                $val = '';
                if (isset ($row[$count]))
                {
                    $val = $row[$count];
                }
                $combination += array($header => $val);
                $count ++;
            }

            $ffvbCsv[] = $combination;
        }

        $this->ffvbCsv = $ffvbCsv;
    }

    /**
     * @return array
     */
    public function getAllTeams(): array
    {
        $teamsDuplicated = array_column($this->ffvbCsv, 'EQA_nom');
        $teamsRaw = array_unique($teamsDuplicated);
        return array_filter($teamsRaw);
    }

    /**
     * Remove games of other teams from the CSV
     *
     * @param string $teamName
     */
    public function keepGamesByTeam(string $teamName): void
    {
        foreach($this->ffvbCsv as $index => $row) {
            if($row['EQA_nom'] === $teamName || $row['EQB_nom'] === $teamName) {
                continue;
            }
            unset($this->ffvbCsv[$index]);
        }
    }

    public function getIcs()
    {
        $ics = "BEGIN:VCALENDAR\n";
        $ics .= "VERSION:2.0\n";
        $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";

        foreach($this->ffvbCsv as $index => $row) {

            $date = date("Ymd", strtotime($row[self::DATE]));
            $startTime = date('His', strtotime($row[self::HEURE]));

            if($row[self::DATE] === '0000-00-00') {
                // Get the first year of the season
                $saison = explode('/', $this->ffvbLink->cal_saison);
                $date = date("Ymd", strtotime($saison[0].'-09-01'));
            }

            if($row[self::HEURE] === '00:00') {
                $startTime = date('His', strtotime('20:00'));
            }

            $endTime = date('His', strtotime($startTime.'+2 hours'));

            $ics .= "BEGIN:VEVENT\n";
            $ics .= "X-WR-TIMEZONE:Europe/Paris\n";
            $ics .= "DTSTART:".$date."T".$startTime."\n";
            $ics .= "DTEND:".$date."T".$endTime."\n";
            $ics .= "SUMMARY:"."Match ". $row[self::JOUR] ." ". $row[self::EQUIPE_A_NOM] ." VS ". $row[self::EQUIPE_B_NOM]."\n";
            $ics .= "LOCATION:".$row[self::SALLE]."\n";
            $ics .= "DESCRIPTION:"."Arbitre ".  $row[self::ARBITRE_1]."\n";
            $ics .= "END:VEVENT\n";

        }
        $ics .= "END:VCALENDAR\n";
        return $ics;
    }
}