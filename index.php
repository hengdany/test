<?php


foreach( range(date('Y'), date("Y",strtotime("+10 year"))) as $year) {
    $seasons[] = $year.'/'.((int)$year+1);
}

$regions = [
    "AUVERGNE-RHÔNE-ALPES"            => "LIRA",
    "BOURGOGNE-FRANCHE-COMTEBRETAGNE" => "LIBOUR",
    "BRETAGNE"                        => "LIBR",
    "CENTRE-VAL DE LOIRE"             => "LICE",
    "CORSE"                           => "LICO",
    "GRAND EST"                       => "LILO",
    "GUADELOUPE"                      => "LIGU",
    "GUYANE"                          => "LIGY",
    "HAUTS-DE-FRANCE"                 => "LIFL",
    "ILE-DE-FRANCE"                   => "LIIDF",
    "LA REUNION"                      => "LIRE",
    "MARTINIQUE"                      => "LIMART",
    "MAYOTTE"                         => "LIMY",
    "NORMANDIE"                       => "LILBNV",
    "NOUVELLE AQUITAINE"              => "LIAQ",
    "OCCITANIE"                       => "LILR",
    "PAYS DE LA LOIRE"                => "LIPL",
    "PROVENCE-ALPES-CÔTE D’AZUR"      => "LICA"
];

$championnats = [
    "NATIONAL"                => "N",
    "PRE-NATIONAL"            => "PN",
    "REGIONAL"                => "R",
];

$genre = [
    "MASCULIN"               => "M",
    "FEMININ"                => "F",
];

$poule = [
    "A", "B", "C", "D", "E", "F"
];

$headerCSVGoogle = [
    "Subject",
    "Start Date",
    "Start Time",
    "End Date",
    "End Time",
    "All Day Event",
    "Description",
    "Location"
];

$headerCSVFFVB = [
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

$headerGoogleToFFVB = [
    "Subject"       => "Match Jo EQA_nom VS EQB_nom",
    "Start Date"    => "Date",
    "Start Time"    => "Heure",
    "End Date"      => "Date",
    "End Time"      => "Heure", //+ 2h
    "All Day Event" => "False",
    "Description"   => "Arbitre". "Arb1",
    "Location"      => "Salle"
];


// TODO :
// on submit, mettre le lien en session
// récupérer le csv via vbspo_calendrier_export.php
// lister toutes les équipes possibles
// afficher un select, "quelle équipe ?"
// selon l'équipe convertir le CSV d'entrée avec le CSV google de sortie
// bouton "télécharger le calendrier Google" et mémo en fin de page "pour importer le calendrier google  https://support.google.com/calendar/answer/37118?hl=fr&co=GENIE.Platform%3DDesktop#zippy=%2Ccr%C3%A9er-ou-modifier-un-fichier-csv"

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="description" content=""/>
    <meta name="author" content=""/>
    <title>Mets ça sur mon agenda</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico"/>
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
<!-- Form-->
<section class="text-center">
    <div class="container px-5 my-5">
        <h1 class="mb-5">Converti le calendrier FFVB en agenda google</h1>
        <form id="contactForm" data-sb-form-api-token="API_TOKEN">
            <div class="form-floating mb-3">
                <input class="form-control" id="newField" type="text" placeholder="https://www.ffvbbeach.org/ffvbapp/resu/vbspo_calendrie..." data-sb-validations="required" />
                <label for="newField">Lien FFVB</label>
                <div class="invalid-feedback" data-sb-feedback="newField:required">New Field is required.</div>
            </div>
            <div class="d-none" id="submitErrorMessage">
                <div class="text-center text-danger mb-3">Error sending message!</div>
            </div>
            <div class="float-end">
                <button class="btn btn-primary " id="submitButton" type="submit"><i class="bi bi-download"></i>
                    Télécharger le calendrier Google
                </button>
            </div>
        </form>
        <br/>
        <br/>
        <hr>
        <h2>Trouvez votre championnat :</h2>
        <form id="contactForm" data-sb-form-api-token="API_TOKEN">
            <div class="form-floating mb-3">
                <select class="form-select" id="saison" aria-label="Saison">';

foreach($seasons as $season) {
    echo '<option value="'.$season.'">'.$season.'</option>';
}

echo '
                </select>
                <label for="saison">Saison</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="region" aria-label="Region">';

foreach($regions as $region => $code) {
    echo '<option value="'.$code.'">'.$region.'</option>';
}

echo '
                </select>
                <label for="region">Region</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="championnat" aria-label="Championnat">';

foreach($championnats as $championnat => $code) {
    echo '<option value="'.$code.'">'.$championnat.'</option>';
}

echo '
                </select>
                <label for="championnat">Championnat</label>
            </div>
             <div class="float-end">
                <button class="btn btn-primary " id="submitButton" type="submit">
                    acceder au calendrier FFVB
                </button>
            </div>
        </form>
        <br/>
    </div>
</section>

<footer class="my-5 pt-5 text-muted text-center text-small">
    <p class="text-muted small mb-4 mb-lg-0 mb-1">avec <3 par <a href="https://github.com/reynadan">Daniel Reynaud</a> 2021. Aucun droit reservé.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>';