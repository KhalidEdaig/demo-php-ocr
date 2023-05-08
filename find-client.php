<?php
function find_client($string, $dataTableExtract)
{
  $str = strtolower($string);
  if (
    (str_contains($str, "m.")
      || str_contains($str, "m")
      || str_contains($str, "mme.")
      || str_contains($str, "mme")
      || str_contains($str, "assuré")
      || str_contains($str, "monsieur")
      || str_contains($str, "madame")
      || str_contains($str, "client")
    )
    &&
    strlen($str) <= 100
  ) {

    if (
      in_array(preg_split("/[\s,]+/", $str)[0], [
        "m.",
        "m",
        "mme.",
        "mme",
        "monsieur",
        "madame",
        "assuré",
        "client"
      ]) && !in_array($string, array_values($dataTableExtract))
    ) {
      return $string;
    } else return false;
  }
}
