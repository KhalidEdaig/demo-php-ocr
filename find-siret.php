<?php
function find_siret($string)
{
  $str = preg_replace("/\s+/", "", strtolower($string));

  $getOnlyNumbers = preg_replace("/\D/", "", $str);
  if (
    (str_contains($str, "siret")
      || str_contains($str, "siren")
      || str_contains($str, "n°siren")
      || str_contains($str, "n°siret"))
    && (strlen($getOnlyNumbers) >= 14)
  ) {
    return  $string;
  } else return false;
}
