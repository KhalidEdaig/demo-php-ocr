<?php
function find_date($string)
{

  try {
    $shortenize = function ($string) {
      return substr($string, 0, 3);
    };

    // Define month name:
    $month_names = array(
      "january",
      "february",
      "march",
      "april",
      "may",
      "june",
      "july",
      "august",
      "september",
      "october",
      "november",
      "december",
      //Fr
      "janvier",
      "évrier",
      "mars",
      "avril",
      "mai",
      "juin",
      "juillet",
      "août",
      "septembre",
      "octobre",
      "novembre",
      "décembre"
    );
    $short_month_names = array_map($shortenize, $month_names);

    // Define day name
    $day_names = array(
      "monday",
      "tuesday",
      "wednesday",
      "thursday",
      "friday",
      "saturday",
      "sunday",
      //Fr
      "lundi",
      "mardi",
      "mercredi",
      "jeudi",
      "vendredi",
      "samedi",
      "dimenche"
    );
    $short_day_names = array_map($shortenize, $day_names);

    // Define ordinal number
    $ordinal_number = ['st', 'nd', 'rd', 'th'];

    $date_find = "";
    $day = "";
    $month = "";
    $year = "";

    // Match dates: 01/01/2012 or 30-12-11 or 1 2 1985
    preg_match('/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/', $string, $matches);

    if ($matches) {
      $date_find = $matches[0];
      if ($matches[1])
        $day = $matches[1];
      if ($matches[2])
        $month = $matches[2];
      if ($matches[3])
        $year = $matches[3];
    }

    // Match dates: Sunday 1st March 2015; Sunday, 1 March 2015; Sun 1 Mar 2015; Sun-1-March-2015
    preg_match('/(?:(?:' . implode('|', $day_names) . '|' . implode('|', $short_day_names) . ')[ ,\-_\/]*)?([0-9]?[0-9])[ ,\-_\/]*(?:' . implode('|', $ordinal_number) . ')?[ ,\-_\/]*(' . implode('|', $month_names) . '|' . implode('|', $short_month_names) . ')[ ,\-_\/]+([0-9]{4})/i', $string, $matches);

    if ($matches) {
      $date_find = $matches[0];
      if (empty($day) && $matches[1])
        $day = $matches[1];

      if (empty($month) && $matches[2]) {
        $month = array_search(strtolower($matches[2]),  $short_month_names);

        if (!$month)
          $month = array_search(strtolower($matches[2]),  $month_names);

        $month = intval($month) + 1;
      }

      if (empty($year) && $matches[3])
        $year = $matches[3];
    }

    // Match dates: March 1st 2015; March 1 2015; March-1st-2015
    preg_match('/(' . implode('|', $month_names) . '|' . implode('|', $short_month_names) . ')[ ,\-_\/]*([0-9]?[0-9])[ ,\-_\/]*(?:' . implode('|', $ordinal_number) . ')?[ ,\-_\/]+([0-9]{4})/i', $string, $matches);
    if ($matches) {
      $date_find = $matches[0];
      if (empty($month) && $matches[1]) {
        $month = array_search(strtolower($matches[1]),  $short_month_names);

        if (!$month)
          $month = array_search(strtolower($matches[1]),  $month_names);

        $month = intval($month) + 1;
      }

      if (empty($day) && $matches[2])
        $day = $matches[2];

      if (empty($year) && $matches[3])
        $year = $matches[3];
    }



    // Match month name:
    if (empty($month)) {
      preg_match('/(' . implode('|', $month_names) . ')/i', $string, $matches_month_word);
      if ($matches_month_word && $matches_month_word[1])
        $month = array_search(strtolower($matches_month_word[1]),  $month_names);

      // Match short month names
      if (empty($month)) {
        preg_match('/(' . implode('|', $short_month_names) . ')/i', $string, $matches_month_word);
        if ($matches_month_word && $matches_month_word[1])
          $month = array_search(strtolower($matches_month_word[1]),  $short_month_names);
      }

      $month = intval($month) + 1;
    }

    // Match 5th 1st day:
    if (empty($day)) {
      preg_match('/([0-9]?[0-9])(' . implode('|', $ordinal_number) . ')/', $string, $matches_day);
      if ($matches_day && $matches_day[1])
        $day = $matches_day[1];
    }

    // Match Year if not already setted:
    if (empty($year)) {
      preg_match('/[0-9]{4}/', $string, $matches_year);
      if ($matches_year && $matches_year[0])
        $year = $matches_year[0];
    }
    if (!empty($day) && !empty($month) && empty($year)) {
      preg_match('/[0-9]{2}/', $string, $matches_year);
      if ($matches_year && $matches_year[0])
        $year = $matches_year[0];
    }


    // Day leading 0
    if (1 == strlen($day))
      $day = '0' . $day;

    // Month leading 0
    if (1 == strlen($month))
      $month = '0' . $month;

    // Check year:
    if (2 == strlen($year) && $year > 20)
      $year = '20' . $year;
    else if (2 == strlen($year) && $year < 20)
      $year = '20' . $year;

    $date = array(
      'year'  => $year,
      'month' => $month,
      'day'   => $day
    );
    // Return false if nothing found:
    if (empty($year) && empty($month) && empty($day)) {
      return false;
    } else if (empty($date['year']) || empty($date['month']) || empty($date['day'])) {
      return false;
    } else {
      if (checkdate(intval($date['month']), intval($date["day"]), intval($date["year"]) && intval($year) > 1000)) {
        return $date_find;
        return $date["day"] . "-" . $date['month'] . "-" . $date["year"];
      } else false;
    }
  } catch (Exception $e) {
    return $e->getMessage();
  }
}
