<?php

namespace App\Entity;

class FfvbCSV {

    const LINK_DOWNLOAD_CSV = 'http://www.ffvbbeach.org/ffvbapp/resu/vbspo_calendrier_export.php';

    const HEADER_CSV_FFVB = [
        "Entité",
        "Jo",
        "Match",
        "Date",
        "Heure",
        "EQA_no",
        "EQA_nom",
        "EQB_no",
        "EQB_nom",
        "Set",
        "Score",
        "Total",
        "Salle",
        "Arb1",
        "Arb2"
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
     * CSV, string format
     *
     * @var string
     */
    private $gcalCsv;

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

    public function convertFFVBCsvToGcal()
    {
        // place Header
        $this->gcalCsv = implode(",", self::HEADER_CSV_GOOGLE) . PHP_EOL;

        foreach($this->ffvbCsv as $index => $row) {
            $conversionTable = [
                "Subject"       => "Match ". $row['Jo'] ." ". $row['EQA_nom'] ." VS ". $row['EQB_nom'],
                "Start Date"    => date("d/m/Y", strtotime($row['Date'])),
                "Start Time"    => $row['Heure'],
                "End Date"      => date("d/m/Y", strtotime($row['Date'])),
                "End Time"      => date('H:i', strtotime($row['Heure'].'+2 hours')),
                "All Day Event" => "FALSE",
                "Description"   => "Arbitre ".  $row['Arb1'],
                "Location"      => $row['Salle']
            ];
            $this->gcalCsv .= implode(",", $conversionTable) . PHP_EOL;
        }
    }

    /**
     * @return string
     */
    public function getGcalCsv(): string
    {
        return $this->gcalCsv;
    }
}