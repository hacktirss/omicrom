<?php
include_once ("softcoatl/SoftcoatlHTTP.php");
include_once 'data/MysqliDb.php';
/**
 * mysqlUtils
 * © 2017, Softcoatl
 * @author Rolando Esquivel
 * @since 05 Jan 2017
 * @version 1.0
 */

/**
 * getConnection Gets a new data base connection object
 * @param type $schemaName Schema Name
 * @param type $hostName Host URL
 * @param type $user Database user
 * @param type $password Database password
 * @return \mysqli Data base object
 * @throws Exception
 */
function getConnection() {
    $dbc = \com\softcoatl\utils\Configuration::get();
    $dbConn = new mysqli($dbc->host, $dbc->username, $dbc->pass);
    if ($dbConn->connect_errno>0) {
        if (mysqli_connect_errno()) {
            throw new Exception("Error conectando con base de datos <br/>" . urldecode(mysqli_connect_error()));
        }
    }
    if (!$dbConn->select_db($dbc->database)) {
        if (mysqli_errno($dbConn)) {
            throw new Exception("Error seleccionando base de datos <br/>" . urldecode(mysqli_error($dbConn)));
        }
    }
    if (!$psSetLocale = $dbConn->prepare("SET lc_time_names = 'es_MX'")
            || !$psSetLocale->execute()) {
        if (mysqli_errno($dbConn)) {
            throw new Exception("Error configurando base de datos <br/>" . urldecode(mysqli_error($dbConn)));
        }
    }
    if (!$dbConn->set_charset($dbc->charset)) {
        if (mysqli_errno($dbConn)) {
            throw new Exception("Error configurando base de datos <br/>" . urldecode(mysqli_error($dbConn)));
        }
    }
    return $dbConn;
}//getConnection


function newMysqliDb() {
    $dbc = \com\softcoatl\utils\Configuration::get();
    $db = new MysqliDb(Array(
        'host' => $dbc->host,
        'username' => $dbc->username,
        'password' => $dbc->pass,
        'db' => $dbc->database,
        'port' => $dbc->port,
        'prefix' => 'my_',
        'charset' => $dbc->charset));
    
    $db->autoReconnect = false;
    
    return $db;
}
