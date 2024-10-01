<?php

require_once ("softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

if (filter_input(INPUT_GET, 'sQuery', FILTER_SANITIZE_STRING) !== null
        && filter_input(INPUT_GET, 'sField', FILTER_SANITIZE_STRING) !== null
        && filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING) !== null) {

    $jsonResult = array();

    foreach($_GET as $key=>$value){
        error_log($key . ' => ' . $value);
    }

    $sQuery     = filter_input(INPUT_GET, 'sQuery', FILTER_SANITIZE_STRING);
    $sField     = filter_input(INPUT_GET, 'sField', FILTER_SANITIZE_STRING);
    $sText      = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);
    error_log(str_replace("&#39;", "'", $sQuery));
    $sQuery = str_replace("&#39;", "'", $sQuery);
    $sField = str_replace("&#39;", "'", $sField);
    $query = $sQuery . " AND " . $sField . " LIKE '%" . str_replace(' ', '%', $sText) . "%' ORDER BY " . $sField;
    error_log($query);

    $connection = utils\IConnection::getConnection();

    $result  = $connection->query($query);

    if (!$result) {
        die('Invalid query: ' . $query . ' ' . $connection->error);
    }

    while($rg=$result->fetch_array()) {
        $jsonResult[] = $rg;
    }

    $jsonString = json_encode(array('suggestions'=>$jsonResult));
    error_log($jsonString);

    if ($jsonString==null) {
        error_log(json_last_error());
    }

    echo $jsonString;
}// if valid parameters
?>
