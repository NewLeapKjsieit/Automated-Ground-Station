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
$frame = str_replace (' ', '', read_env_string('frame', ''));
$locator = read_env_string('locator', '');
$longitude = read_env_string('longitude', '');
$latitude = read_env_string('latitude', '');
$tnc_port = read_env_int ('tncPort', 0);
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
if (strlen($frame) == 0) { $error_msg = 'Field frame not set!'; break; }
if (strlen($frame) > 500) { $error_msg = 'Field frame is invalid!'; break; }
if (preg_match('/[^0-9a-fA-F]/', $frame)) { $error_msg = 'Field frame is invalid!'; break; }
if (strlen($locator) == 0) { $error_msg = 'Field locator not set!'; break; }
if ($locator != 'longLat') { $error_msg = 'Field locator is invalid!'; break; }
if (strlen($longitude) == 0) { $error_msg = 'Field longitude not set!'; break; }
if (strlen($longitude) > 20) { $error_msg = 'Field longitude is invalid!'; break; }
if (! preg_match('/^(\+|-)?[0-9]{1,3}\.[0-9]{1,10}([eE]|[wW])$/', $longitude)) { $error_msg = 'Field longitude is
invalid!'; break; }
if (strlen($latitude) == 0) { $error_msg = 'Field latitude not set!'; break; }
if (strlen($latitude) > 20) { $error_msg = 'Field latitude is invalid!'; break; }
if (! preg_match('/^(\+|-)?[0-9]{1,3}\.[0-9]{1,10}([nN]|[sS])$/', $latitude)) { $error_msg = 'Field latitude is
invalid!'; break; }
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
// convert supplied frame from hex to binary representation
$frame_bin = hex2bin($frame);
// prepare insert query
$stmt = $mysqli->prepare("INSERT INTO ExternalMessages (RecvTime, CreatedByIP, CreatedBy, Longitude, Latitude, Frame, TNCPort) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false)
error_exit($mysqli, "unable to prepare insert statement");
do {
// bind parameters to insert query
if (! $stmt->bind_param('ssssssi', $ts, $_SERVER['REMOTE_ADDR'], $source, $longitude, $latitude, $frame_bin,
$tnc_port)) { $error_msg = 'unable to bind params'; break; }
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