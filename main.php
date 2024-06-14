<?php 

ini_set('display_errors', 0);
ini_set('memory_limit', '100M');

header("Access-Control-Allow-Origin: *");

date_default_timezone_set('Asia/Jakarta');

$now1 = date('Y-m-d H:i:s');
$today1 = date('Y-m-d');
$today2 = date('Y_m_d');
$logFolderPath = "log";

function shutdown() {
  $ee = error_get_last();
  if ($ee != null) {
    echo "Sorry your page is too heavy to load";
  }
}

register_shutdown_function('shutdown');
ini_set('max_execution_time', 30);


function logUserActivity() {
    global $now1, $today2, $logFolderPath;

    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
    $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'N/A';
    $requestParameters = $_REQUEST;
    $sessionData = isset($_SESSION) ? $_SESSION : array();
    $cookiesData = isset($_COOKIE) ? $_COOKIE : array();
    $logEntry = date('Y-m-d H:i:s') . " - IP: $ipAddress, Referrer: $referrer, Query String: $queryString, Parameters: " . json_encode($requestParameters) . ", Session: " . json_encode($sessionData) . ", Cookies: " . json_encode($cookiesData) . PHP_EOL;
    // $logFilePath = $logFolderPath  . DIRECTORY_SEPARATOR . 'user_activity_log.txt';
    $logFilePath = $logFolderPath  . DIRECTORY_SEPARATOR . "log-{$today2}.txt";
    file_put_contents($logFilePath, $logEntry, FILE_APPEND);
}



function fetch1($url) {
  $response = @file_get_contents($url, false, $context);
  return $response;
}



logUserActivity();

$url = @$_GET['url'];

$content = '';
if($url == '') {
  $file1 = fopen("welcome.html", "r") or die("Unable to open file!");
  $content = fread($file1, filesize("welcome.html"));
  fclose($file1);
} else {

  $url2 = parse_url($url);
  $path = trim( $url2['path'], "/" );
  // $file_path = "hsk/hsk_1_vocabulary_list.txt";
  // $file_path = "/hsk/hsk_1_vocabulary_list.txt";
  $file_path = $path;
  $is_local_file = false;
  // try {
  // $file1 = fopen($file_path, "r") or die("Unable to open file!");

  $is_local_file = file_exists($file_path);

  if($is_local_file) {
    $file1 = fopen($file_path, "r");
    $content = fread($file1, filesize($file_path));
    fclose($file1);
  } else {
    $content = fetch1($url);
  }
  // $is_local_file = true;
  // } catch (Exception $e) {
  // }
}

echo $content;


