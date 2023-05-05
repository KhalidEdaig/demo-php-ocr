<?php

use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToText\Pdf;

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
                    // $parser = new \Smalot\PdfParser\Parser();

                    // $pdf = $parser->parseFile('uploads/' . $file_name);

                    // $fileRead = $pdf->getText();

                    $fileRead = (new Pdf())
                        ->setPdf('uploads/' . $file_name)
                        ->setOptions(['layout', 'r 96'])
                        ->text();
                }

                if ($fileRead) {
                    $dataInfo = [];
                    function removeSpace($v)
                    {
                        return trim($v);
                    }
                    $array =  array_map("removeSpace", preg_split("/\r\n|\n|\r/", $fileRead));
                    $array = array_filter($array, function ($txt) {
                        return $txt ?? true;
                    });

                    $dataSets = [
                        "Facture N° : DUPLICATA", "FAC-000",
                        "Facture N°F",
                        "N° de facture",
                        "FAC-00000003663",
                        "Facture N°F452074270 du 03/03/2023 - Établie par Pauline M - FACTURE ACQUITTÉE",
                        "Facture de vente n° 393737",
                        "N°4004202304730400020"
                    ];
                    $siretDataSets = [
                        "Siret",
                        "N°Siren / Siret :",
                        "Siret:",
                        "N°Siren / Siret :"
                    ];
                    $dateDataSets = [""];
                    foreach ($array as $key => $value) {
                        if (strlen($value) > 0) {
                            foreach ($dataSets as $dataSet) {
                                $facture = similar_text(trim($value), trim($dataSet), $percent);
                                if ($percent > 70) {
                                    if (in_array(trim($value), $dataSets)) {
                                        array_push($dataSets, trim($value));
                                    }
                                    $dataInfo['facture n°'] = $value;
                                }
                            }
                        }
                        // foreach ($siretDataSets as $dataSet) {
                        //     foreach (explode(" ", $value) as $word) {
                        //         $str = preg_replace("/\s+/", "", strtolower($word));
                        //         $getOnlyNumbers = preg_replace("/\D/", "", $str);
                        //         if (strlen($getOnlyNumbers) >= 14) {
                        //             $facture = similar_text(trim($value), trim($dataSet), $percent);
                        //             $result = ($facture * 100) / strlen($value);
                        //             if ($percent > 30) {
                        //                 if (in_array(trim($value), $siretDataSets)) {
                        //                     array_push($siretDataSets, trim($value));
                        //                 }
                        //                 $dataInfo['siret'] = $getOnlyNumbers;
                        //                 // break;
                        //             }
                        //         }
                        //     }
                        // }
                        $str = preg_replace("/\s+/", "", strtolower($value));

                        $getOnlyNumbers = preg_replace("/\D/", "", $str);
                        if ((str_contains($str, "siret") || str_contains($str, "siren")) & (strlen($getOnlyNumbers) >= 14)) {
                            $dataInfo['siret' . $key] = $value;
                        }

                        $str = strtolower($value);
                        if (
                            (str_contains($str, "m.")
                                || str_contains($str, "m")
                                || str_contains($str, "mme.")
                                || str_contains($str, "mme")
                                || str_contains($str, "assuré")
                                || str_contains($str, "monsieur")
                                || str_contains($str, "madame")
                            )
                            &&
                            strlen($str) <= 100
                        ) {

                            if (in_array(preg_split("/[\s,]+/", $str)[0], [
                                "m.",
                                "m",
                                "mme.",
                                "mme",
                                "monsieur",
                                "madame",
                                "assuré"
                            ])) {
                                $dataInfo['client' . $key] = $value;
                            }
                        }
                    }
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
                    <?php if ($_POST) : ?>
                        <p class="lead">
                        <pre><?= print_r($dataInfo); ?></pre>
                        </p>
                        <hr class="my-4">
                        <p class="lead">
                        <pre><?= print_r($array); ?></pre>
                        </p>
                        <hr class="my-4">
                        <pre><?= $fileRead ?></pre>
                        <p class="lead">
                        </p>
                    <?php endif; ?>

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