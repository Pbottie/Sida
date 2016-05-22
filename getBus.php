<?php

  error_reporting(E_ALL);
  $key= include 'key.php';

  $content = file_get_contents("http://api.sl.se/api2/realtimedepartures.json?key={$key}&siteid=9140&timewindow=60");
  echo $content;
  ?>
