<?php

$day_show_logs = 14;
$nowtime = time();
$table = $null;
$folders_json = '/usr/user_account_locked/log';

$array     = array();
$array_num = 0;

for ( $x = 0; $x < $day_show_logs; $x++ ) {
  // Перечисляем весь диапазон дней который задан в переменной $day_show_logs
  $data = $null;
  // Формируем путь и имя до файла
  $json_file = "$folders_json/account_locked_".date("Y_m_d", ( $nowtime - (86400*$x) ) ).".log";
  // Подгружаем содержимое файла
  $file_json = file_get_contents( $json_file );
  // Убираем последний символ из файла. а именно  ,
  $json = substr($file_json,0,-1);
  // Конвертируем в массив
  $data = json_decode( "[".$json."]" );

  foreach ( $data as $lines ) {
    $array[ $array_num ]->TimeCreated         = $lines->TimeCreated;
    $array[ $array_num ]->TimeCreatedUnixTime = ( new DateTime($lines->TimeCreated) )->getTimestamp();   // echo date("Y_m_d    H:i:s", $TimeCreatedUnixTime);
    $array[ $array_num ]->ComputerName        = $lines->ComputerName;
    $array[ $array_num ]->Id                  = $lines->Id;
    $array[ $array_num ]->MachineName         = $lines->MachineName;
    $array[ $array_num ]->Task                = $lines->Task;
    $array[ $array_num ]->ProviderName        = $lines->ProviderName;
    $array[ $array_num ]->UserName            = $lines->UserName;
    $array[ $array_num ]->LogName             = $lines->LogName;
    $array[ $array_num ]->Message             = $lines->Message;
    $array[ $array_num ]->LevelDisplayName    = $lines->LevelDisplayName;
    $array[ $array_num ]->DomainName          = $lines->DomainName;
    $array[ $array_num ]->DomainDC            = $lines->DomainDC;
    $array[ $array_num ]->TaskDisplayName     = $lines->TaskDisplayName;
    // echo "______ " . $array_num . " ______ " . $array[ $array_num ]->DomainDC . " ______<p>";
    $array_num++;
    }
  }

// Сортировка по TimeCreatedUnixTime
function arraysort( $x , $y ) {
  return ( $x->TimeCreatedUnixTime < $y->TimeCreatedUnixTime );
  }

uasort($array,'arraysort');

foreach ($array as $key => $value) {
  $TimeCreated = $array[ $key ]->TimeCreated;
  $ComputerName = $array[ $key ]->ComputerName;
  $Id = $array[ $key ]->Id;
  $MachineName = $array[ $key ]->MachineName;
  $Task = $array[ $key ]->Task;
  $ProviderName = $array[ $key ]->ProviderName;
  $UserName = $array[ $key ]->UserName;
  $LogName = $array[ $key ]->LogName;
  $Message = $array[ $key ]->Message;
  $LevelDisplayName = $array[ $key ]->LevelDisplayName;
  $DomainName = $array[ $key ]->DomainName;
  $DomainDC = $array[ $key ]->DomainDC;
  $TaskDisplayName = $array[ $key ]->TaskDisplayName;

  $string = "
  <tr>
    <td> $TimeCreated</td>
    <td> $DomainName </td>
    <td> $UserName </td>
    <td> $ComputerName </td>
    <td> $DomainDC </td>
    <td> $MachineName </td>
    <td> $Message </td>
   </tr>
  ";
  $table[] = $string;
  }
?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>ad-locked</title>
    <link rel="shortcut icon" href="logo3.png" type="image/png">
    <meta http-equiv="Refresh" content="60" />
    <link rel="stylesheet" href="style3.0.css">
  </head>
  <body bgcolor="#f0faff">
    <div id="fixWidth" class="index">
      <div class="headerWrapper">
        <div id="header">
          <!-- <img src="logo3.png" alt=""  class="logo" /> -->
          <div class="officeAtmBlock"><span class="officeAtm"><font size=+2>account locked</font><br></span></div>
        </div>
      </div>

    <table width="100%">
      <thead>
        <tr>
        <th width="7%" align="middle"> <div class="block-disc2">Time</div> </th>
        <th width="2%" align="middle"> <div class="block-disc2">DomainName</div> </th>
        <th width="4%" align="middle"> <div class="block-disc2">UserName</div> </th>
        <th width="4%" align="middle"> <div class="block-disc2">ComputerName</div> </th>
        <th width="2%" align="middle"> <div class="block-disc2">DC</div> </th>
        <th width="4%" align="middle"> <div class="block-disc2">DC</div> </th>
        <th width="40%" align="middle"> <div class="block-disc2">Message</div> </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach( $table as $row  ) { echo $row; }; ?>
      </tbody>
    </table>
  </body>
</html>
