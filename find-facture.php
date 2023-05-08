<?php
function find_facture($string)
{
  $dataSets = [
    "Facture N° : DUPLICATA", "FAC-000",
    "Facture N°F",
    "N° de facture",
    "FAC-00000003663",
    "Facture N°F452074270 du 03/03/2023 - Établie par Pauline M - FACTURE ACQUITTÉE",
    "Facture de vente n° 393737",
    "N°4004202304730400020",
    "Facture N° : 188652 DUPLICATA    59510 Hem"
  ];
  foreach ($dataSets as $dataSet) {
    similar_text(trim($string), trim($dataSet), $percent);
    if ($percent > 70) {
      if (in_array(trim($string), $dataSets)) {
        array_push($dataSets, trim($string));
      }
      return  $string;
    } else false;
  }
}
