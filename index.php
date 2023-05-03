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
    <div class="container mt-5">
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