<?php 

ini_set('display_errors', 0);
ini_set('memory_limit', '100M');

header("Access-Control-Allow-Origin: *");

function shutdown() {
  $ee = error_get_last();
  if ($ee != null) {
    echo "Sorry your page is too heavy to load";
  }
}

register_shutdown_function('shutdown');
ini_set('max_execution_time', 30);


function fetch1($url) {
  $response = file_get_contents($url);
  return $response;
}

$url = @$_GET['url'];

$content = '';
if($url == '') {
  $file1 = fopen("welcome.html", "r") or die("Unable to open file!");
  $content = fread($file1, filesize("welcome.html"));
  fclose($file1);
} else {
  $content = fetch1($url);
}

echo $content;


