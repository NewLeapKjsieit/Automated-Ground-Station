<?php

// set error reporting and locale
error_reporting(E_ALL - E_WARNING - E_NOTICE);
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu');

// read script parameters by first trying $_POST, then $_GET
$rType = read_env_int('rType', '');
$timestamp = read_env_string('timestamp', '');
$callsign = read_env_string('callsign', '');
$locator = read_env_string('locator', '');
$longitude = read_env_string('longitude', '');
$latitude = read_env_string('latitude', '');
$sat_latitude = read_env_string('satLatitude', '');
$sat_longitude = read_env_string('satLongitude', '');
$temp1 = read_env_int ('temp1', '');
$temp2 = read_env_int ('temp2', '');
$mag1 = read_env_int ('mag1', '');
$mag2 = read_env_int ('mag2', '');
$mag3 = read_env_int ('mag3', '');
$gyro1 = read_env_int ('gyro1', '');
$gyro2 = read_env_int ('gyro2', '');
$gyro3 = read_env_int ('gyro3', '');
$light1 = read_env_int ('light1', '');
$light2 = read_env_int ('light2', '');
$light3 = read_env_int ('light3', '');
$light4 = read_env_int ('light4', '');
$light5 = read_env_int ('light5', '');
$light6 = read_env_int ('light6', '');
$power1 = read_env_int ('power1', '');
$power2 = read_env_int ('power2', '');
$power3 = read_env_int ('power3', '');
$power4 = read_env_int ('power4', '');
$power5 = read_env_int ('power5', '');
$bVolt = read_env_int ('bVolt', '');
$bSOC = read_env_int ('bSOC', '');

// initializations for mysqli instance and error message
$mysqli = NULL;
$error_msg = NULL;

// sanity checks on input data

do {
    if (strlen($timestamp) == 0) { $error_msg = 'Field timestamp not set!'; break; }
    if (! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z$/', $timestamp)) {
    $error_msg = 'Field timestamp is invalid!'; break; }
    if (strlen($callsign) == 0) { $error_msg = 'Field callsign not set!'; break; }
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
    if (strlen($sat_longitude) == 0) { $error_msg = 'Field sat_longitude not set!'; break; }
    if (strlen($sat_longitude) > 20) { $error_msg = 'Field sat_longitude is invalid!'; break; }
    if (! preg_match('/^(\+|-)?[0-9]{1,3}\.[0-9]{1,10}([eE]|[wW])$/', $sat_longitude)) { $error_msg = 'Field sat_longitude is
    invalid!'; break; }
    if (strlen($sat_latitude) == 0) { $error_msg = 'Field sat_latitude not set!'; break; }
    if (strlen($sat_latitude) > 20) { $error_msg = 'Field sat_latitude is invalid!'; break; }
    if (! preg_match('/^(\+|-)?[0-9]{1,3}\.[0-9]{1,10}([nN]|[sS])$/', $sat_latitude)) { $error_msg = 'Field sat_latitude is
    invalid!'; break; }
    if (strlen($temp1) == 0) { $error_msg = 'Field temp1 not set!'; break; }
    if (strlen($temp2) == 0) { $error_msg = 'Field temp2 not set!'; break; }
    if (strlen($mag1) == 0) { $error_msg = 'Field mangnetometer1 not set!'; break; }
    if (strlen($mag2) == 0) { $error_msg = 'Field mangnetometer2 not set!'; break; }
    if (strlen($mag3) == 0) { $error_msg = 'Field mangnetometer3 not set!'; break; }
    if (strlen($gyro1) == 0) { $error_msg = 'Field gyrosensor1 not set!'; break; }
    if (strlen($gyro2) == 0) { $error_msg = 'Field gyrosensor2 not set!'; break; }
    if (strlen($gyro3) == 0) { $error_msg = 'Field gyrosenso3 not set!'; break; }
    if (strlen($light1) == 0) { $error_msg = 'Field lightsensor1 not set!'; break; }
    if (strlen($light2) == 0) { $error_msg = 'Field lightsensor2 not set!'; break; }
    if (strlen($light3) == 0) { $error_msg = 'Field lightsensor3 not set!'; break; }
    if (strlen($light4) == 0) { $error_msg = 'Field lightsensor4 not set!'; break; }
    if (strlen($light5) == 0) { $error_msg = 'Field lightsensor5 not set!'; break; }
    if (strlen($light6) == 0) { $error_msg = 'Field lightsensor6 not set!'; break; }
    if (strlen($power1) == 0) { $error_msg = 'Field power1 not set!'; break; }
    if (strlen($power2) == 0) { $error_msg = 'Field power2 not set!'; break; }
    if (strlen($power3) == 0) { $error_msg = 'Field power3 not set!'; break; }
    if (strlen($power4) == 0) { $error_msg = 'Field power4 not set!'; break; }
    if (strlen($power5) == 0) { $error_msg = 'Field power5 not set!'; break; }
    if (strlen($bVolt) == 0) { $error_msg = 'Field battery_voltage not set!'; break; }
    if (strlen($bSOC) == 0) { $error_msg = 'Field battery_SOC not set!'; break; }
    } while (false);

// show error and exit if an error ocurred

if ($error_msg)
error_exit($mysqli, $error_msg);

// create mysqli object and connect to database
$mysqli = new mysqli('localhost', 'id14245482_admin', 'Password@123', 'id14245482_nliapf');
if ($mysqli->connect_error)
error_exit($mysqli, "unable to connect to database");

// format timestamp to be compatible with MySQL TIMESTAMP
$ts = str_replace(array('T', 'Z'), array(' ', ''), $timestamp);

$stmt = $mysqli->prepare("INSERT INTO SensorData (RecvTime, CallSign, Longitude, Latitude, SatLongitude, SatLatitude, Temp1, Temp2, Mag1, Mag2, Mag3, Gyro1, Gyro2, Gyro3, Light1, Light2, Light3, Light4, Light5, Light6, Power1, Power2, Power3, Power4, Power5, BVoltage, BSOC) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false)
error_exit($mysqli, "unable to prepare insert statement");
do {
// bind parameters to insert query
if (! $stmt->bind_param('ssssssssssssssssssssssssssi', $ts, $callsign, $longitude, $latitude, $sat_longitude,
$sat_latitude, $temp1, $temp2, $mag1, $mag2, $mag3, $gyro1, $gyro2, $gyro3, $light1, $light2, $light3, $light4, $light5, $light6,
$power1, $power2, $power3, $power4, $power5, $bVolt, $bVolt)) { $error_msg = 'unable to bind params'; break; }

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