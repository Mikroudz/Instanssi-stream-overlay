<?php
  header('Content-Type: application/json');

  function getjson($url) {
    global $api_url;
    $json = file_get_contents($api_url . $url);
    if (!empty($json)) {
      return $json;
    } else {
      return "404";
    }
  }

  function sortDate($a, $b){
    return strtotime($a['date']) - strtotime($b['date']);
  }

  $api_url = 'https://instanssi.org/api/v1';
  $program_arr = array();
  $event = $_GET['event'];
  $limit = $_GET['limit'] ?: 4;
  $type = $_GET['type'] ?: 0;
  $types_arr = [
    '/programme_events/?event='. $event .'&format=json',
    '/compos/?event='. $event .'&format=json',
    '/sponsors/?event='.$event .'&format=json'
  ];
  $array_out = array();


  switch ($type) {
    case 0: //ohjelmalista
    $obj_prog = json_decode(getjson($types_arr[0]));
    $obj_compo = json_decode(getjson($types_arr[1]));

    foreach ($obj_prog as $val) {
      array_push($program_arr,
        [
          'title' => $val->title,
          'date' => $val->start
        ]);
    }
    foreach ($obj_compo as $val) {
      if(!empty($val->adding_end)){
        array_push($program_arr, [
          'title' => $val->name . ": ilmottautuminen päättyy",
          'date' => $val->adding_end,
        ]);
      }
      if(!empty($val->compo_start)){
        array_push($program_arr, [
          'title' => $val->name . ": kompo alkaa",
          'date' => $val->compo_start,
        ]);
      }
      if(!empty($val->voting_start)){
        array_push($program_arr, [
          'title' => $val->name . ": äänestys alkaa",
          'date' => $val->voting_start,
        ]);
      }
      if(!empty($val->voting_end)){
        array_push($program_arr, [
          'title' => $val->name . ": äänestys loppuu",
          'date' => $val->voting_end,
        ]);
      }
    }
    usort($program_arr, "sortDate");
    $array_out = array_slice($program_arr, 0, $limit);
      break;
    case 1: //sponsorit
    $obj_sponsors = json_decode(getjson($types_arr[2]));
    foreach ($obj_sponsors as $val ) {
      if(!empty($val->logo_scaled_url)){
        array_push($array_out, [
          'logo_img_url' => $val->logo_scaled_url
        ]);
      } else {
        array_push($array_out, [
          'logo_img_url' => "Logoja ei ooo :("
        ]);
      }
    }
      break;
    case 2:

      break;
    default:

      break;
  }
  echo json_encode($array_out);
 ?>
