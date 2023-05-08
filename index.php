<?php

use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToText\Pdf;

require 'vendor/autoload.php';
require 'find-date.php';
require 'find-facture.php';
require 'find-client.php';
require 'find-siret.php';

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
                        ->addOptions(['f 1'])
                        ->text();
                }

                if ($fileRead) {
                    $dataTableExtract = [];
                    function removeSpace($v)
                    {
                        return preg_replace('/\s\s+/', '    ', trim($v));
                    }
                    $textExtracted =  array_map("removeSpace", preg_split("/\r\n|\n|\r/", $fileRead));
                    $textExtracted = array_filter($textExtracted, function ($txt) {
                        return $txt ?? true;
                    });


                    foreach ($textExtracted as $key => $value) {
                        if (strlen($value) > 0) {
                            if ($date = find_date($value))
                                $dataTableExtract[$key . ' date'] = $value;

                            if ($facture = find_facture($value))
                                $dataTableExtract[$key . ' - facture n°'] = $facture;

                            if ($client = find_client($value, $dataTableExtract))
                                $dataTableExtract[$key . ' - client'] = $client;

                            if ($siret = find_siret($value))
                                $dataTableExtract[$key . ' - siret'] = $siret;
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
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <h1 class="display-4 text-center">Lire le texte des images ou pdf</h1>
            </div>
        </div>
        <div class="row mt-5">

            <div class="col-sm-12 mx-auto">
                <div class="jumbotron">

                    <?php if ($_POST) : ?>
                        <p class="lead">
                        <pre><?= print_r($dataTableExtract); ?></pre>
                        </p>
                        <hr class="my-4">
                        <p class="lead">
                        <pre><?= print_r($textExtracted); ?></pre>
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