<?php
include_once ("softcoatl/SoftcoatlHTTP.php");


function getConnection($host, $user, $password) {
    $dbc = \com\softcoatl\utils\Configuration::get();
    $dbConn = new mysqli($dbc->host, $dbc->username, $dbc->pass);

    if ($dbConn->connect_errno 
            || !$dbConn->select_db("omicrom")
            || !($psSetLocale = $dbConn->prepare("SET lc_time_names = 'es_MX'"))
            || !$psSetLocale->execute()) {
        throw new Exception("Error de obteniendo conexión: (" . $dbConn->connect_errno . ") " . $dbConn->connect_error);
    }
    return $dbConn;
}

/**
 * 
 * @param \mysqli $omiConn
 * @param type $sqlExp
 * @param type $paramString
 * @param type $queryParameters
 * @return \mysqli_stmt
 * @throws Exception
 */
function preparedStatement($omiConn, $sqlExp, $paramString, $queryParameters) {
    $psValues = array();
    $psFlags = "";

    error_log("PSSQL " . $sqlExp);
    if (!($preparedSmt = $omiConn->prepare($sqlExp))) {
        throw new Exception("Error de conexión ps: (" . $omiConn->errno . ") " . $omiConn->error);
    }

    $parameters = explode(",", $paramString);
    for ($i = 0; $i < count($parameters); $i++) {
        $psFlags = "s" . $psFlags;
    }
    foreach ($parameters as $parameter) {
        array_push($psValues, filter_var($queryParameters[$parameter], FILTER_SANITIZE_STRING));
    }
    error_log("PSSQL " . print_r($psValues, true));

    $preparedSmt->bind_param($psFlags, ...$psValues);

    return $preparedSmt;
}   

function getFieldNames($rsMetaData) {
    $fieldNames = array();
    foreach ($rsMetaData as $key => $value) {
        foreach ($value as $key1 => $value1) {
            array_push($fieldNames, $value1);
            break;
        }
    }
    return $fieldNames;
}

function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}
