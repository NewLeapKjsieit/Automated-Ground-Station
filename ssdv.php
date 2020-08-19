<?php
// set error reporting and locale
error_reporting(E_ALL - E_WARNING - E_NOTICE);
// ###################################################
// ####### TODO: Change to your own locale ###########
// ###################################################
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu');
// read script parameters by first trying $_POST, then $_GET
$source = read_env_string('source', '');
$timestamp = read_env_string('timestamp', '');
$image_data = str_replace (' ', '', read_env_string('image', ''));
// initializations for mysqli instance and error message
$mysqli = NULL;
$error_msg = NULL;
// sanity checks on input data
do {
if (strlen($source) == 0) { $error_msg = 'Field source not set!'; break; }
if (strlen($source) > 50) { $error_msg = 'Field source is invalid!'; break; }
if (strlen($timestamp) == 0) { $error_msg = 'Field timestamp not set!'; break; }
if (! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z$/', $timestamp)) {
$error_msg = 'Field timestamp is invalid!'; break; }
if (strlen($image_data) == 0) { $error_msg = 'Field image not set!'; break; }
} while (false);
// show error and exit if an error ocurred
if ($error_msg)
error_exit($mysqli, $error_msg);

// create mysqli object and connect to database
// ###################################################
// ####### TODO: please enter own credentials ########
// ###################################################
$mysqli = new mysqli('localhost', 'id14245482_admin', 'Password@123', 'id14245482_nliapf');
if ($mysqli->connect_error)
error_exit($mysqli, "unable to connect to database");

// format timestamp to be compatible with MySQL TIMESTAMP
$ts = str_replace(array('T', 'Z'), array(' ', ''), $timestamp);

// create file to save image data
$received_time = strtotime($ts); //unix timestamp
$image_file_name = $received_time.".bin";
$myfile = fopen("Image Data/"."$image_file_name", "w") or die("Unable to open file!");
fwrite($myfile, $image_data);
fclose($myfile);

// convert supplied frame from hex to binary representation
//$frame_bin = hex2bin($frame);
// prepare insert query
$stmt = $mysqli->prepare("INSERT INTO SSDVMessages (RecvTime, Callsign, ImageFileName) VALUES (?, ?, ?)");
if ($stmt === false)
error_exit($mysqli, "unable to prepare insert statement");
do {
// bind parameters to insert query
if (! $stmt->bind_param('sss', $ts, $source, $image_file_name)) { $error_msg = 'unable to bind params'; break; }

// execute insert query
if (! mysqli_stmt_execute($stmt)) { $error_msg = 'executing insert query failed'; break; }

// check if only one row was affected
if ($stmt->affected_rows != 1) { $error_msg = 'wrong number of rows affected'; break; }
} while (false);
$stmt->close();

// show error and exit if an error ocurred
if ($error_msg)
error_exit($mysqli, $error_msg);

// we're fine, so output OK
echo "OK";
// close database connection
$mysqli->close();
// show error and exit, sends "Bad Request" header using HTTP status code 400
function error_exit($mysqli, $error_msg) {
// send Bad Request (400) header
header("Bad Request", true, 400);
// close database connection if it was successfully opened
if (is_object($mysqli)) {
if (is_null($mysqli->connect_error))
$mysqli->close();
}
// output error message
echo "Error: $error_msg";
exit();
}
// read integer from environment, first trying $_POST, then $_GET
function read_env_int($var, $default) {
 $result = $default;
 if (isset($_POST[$var]))
 $result = intval($_POST[$var]);
 else
 if (isset($_GET[$var]))
 $result = intval($_GET[$var]);
 return $result;
}
// read decoded string from environment, first trying $_POST, then $_GET
function read_env_string($var, $default) {
 $result = $default;
 if (isset($_POST[$var]))
 $result = rawurldecode($_POST[$var]);
 else
 if (isset($_GET[$var]))
 $result = rawurldecode($_GET[$var]);
 return $result;
}
?>