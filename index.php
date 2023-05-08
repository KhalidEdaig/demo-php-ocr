<?php

use thiagoalessio\TesseractOCR\TesseractOCR;

require 'vendor/autoload.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $file_name = $_FILES['file']['name'];
        $tmp_file = $_FILES['file']['tmp_name'];
        if (!session_id()) {
            session_start();
            $unq = session_id();
        }
        $file_name = $unq . '_' . time() . '_' . str_replace(array('!', "@", '#', '$', '%', '^', '&', ' ', '*', '(', ')', ':', ';', ',', '?', '/' . '\\', '~', '`', '-'), '_', strtolower($file_name));
        $extension = preg_replace('/^.*\.([^.]+)$/D', '$1', $file_name);
        if (move_uploaded_file($tmp_file, 'uploads/' . $file_name)) {

            try {
                if ($extension !== 'pdf') {
                    $fileRead = (new TesseractOCR('uploads/' . $file_name))
                        ->lang('eng', 'fr')
                        ->run();
                } else {
                    $parser = new \Smalot\PdfParser\Parser();

                    $pdf = $parser->parseFile('uploads/' . $file_name);

                    $fileRead = $pdf->getText();
                    $dataInfo = [];
                    $array = preg_split("/\r\n|\n|\r/", $fileRead);
                    $dataSets = ["Facture N° : DUPLICATA", "FAC-000", "Facture N°F", "N° de facture", "FAC-00000003663", "Facture N°F452074270 du 03/03/2023 - Établie par Pauline M - FACTURE ACQUITTÉE", "Facture de vente n° 393737", "N°4004202304730400020"];
                    $siretDataSets = ["Siret", "N°Siren / Siret :", "Siret:", "N°Siren / Siret :"];
                    $dateDataSets = ["/01/", "/02/", "/03/", "/04/", "/05/", "/06/", "/07/", "/08/", "/09/", "/10/", "/11/", "/12/"];
                    $dates = array();
                    foreach ($array as $key => $value) {

                        foreach ($dataSets as $dataSet) {
                            similar_text(trim($value), trim($dataSet), $percent);
                            if (strlen($value) > 0 && $percent > 70) {
                                if (in_array(trim($value), $dataSets)) {
                                    array_push($dataSets, trim($value));
                                }
                                foreach (explode(" ", $value) as $word) {
                                    $str = preg_replace("/\s+/", "", strtolower($word));
                                    $getOnlyNumbers = preg_replace("/\D/", "", $str);
                                    if (strlen($getOnlyNumbers) >= 6 && !str_contains(trim($value), "/")) {
                                        $dataInfo['numero'] = $word;
                                    }
                                }
                            }
                        }

                        foreach ($siretDataSets as $dataSet) {
                            foreach (explode(" ", $value) as $word) {
                                $str = preg_replace("/\s+/", "", strtolower($word));
                                $getOnlyNumbers = preg_replace("/\D/", "", $str);
                                if (strlen($getOnlyNumbers) >= 14) {
                                    similar_text(trim($value), trim($dataSet), $percent);
                                    if ($percent > 30) {
                                        if (in_array(trim($value), $siretDataSets)) {
                                            array_push($siretDataSets, trim($value));
                                        }
                                        $dataInfo['siret'] = $getOnlyNumbers;
                                    }
                                }
                            }
                        }

                        foreach ($dateDataSets as $dataSet) {
                            if (str_contains(trim($value), trim($dataSet))) {
                                $txt = strtolower(trim($value));
                                if (str_contains($txt, "le") || str_contains($txt, "facture")) {
                                    foreach (explode(" ", $value) as $word) {
                                        if (strlen(trim($word)) >= 8 && str_contains(trim($word), trim($dataSet))) {
                                            $dataInfo['date'] = trim($word);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    var_dump($dataInfo);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo "<p class='alert alert-danger'>File failed to upload.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo php OCR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="row mt-5">
            <div class="col-sm-8 mx-auto">
                <div class="jumbotron">
                    <h1 class="display-4">Lire le texte des images ou pdf</h1>
                    <p class="lead">


                        <?php if ($_POST) : ?>

                    <pre>
                                <?= $fileRead ?>
                            </pre>
                <?php endif; ?>


                </p>
                <hr class="my-4">
                </div>
            </div>
        </div>
        <div class="row col-sm-8 mx-auto">
            <div class="card mt-5">
                <div class="card-body">


                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">

                            <label for="filechoose">Choisir le fichier</label>

                            <input type="file" name="file" class="form-control-file" id="filechoose">

                            <button class="btn btn-success mt-3" type="submit" name="submit">Télécharger</button>

                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
</body>

</html>