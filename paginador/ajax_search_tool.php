<?php

require_once ("softcoatl/SoftcoatlHTTP.php");

$request = com\softcoatl\utils\Request::instance();

if ($request->has("sTable") && $request->has("query") && $request->has("sSearch")) {

    $jsonResult = array();

    $sTable     = $request->get("sTable");
    $sText      = $request->get("query");
    $sSearch    = $request->get("sSearch");
    $sCondition = $request->get("sCondition");

    $query = "SELECT DISTINCT " .  $sSearch . " data, " .  $sSearch . " value FROM " . $sTable . " WHERE " . $sSearch . " REGEXP '" . str_replace(' ', '.*', $sText) . "' " . (empty($sCondition) ? "" : " AND " . $sCondition) . " ORDER BY " . $sSearch;
    error_log($query);

    $connection = com\softcoatl\utils\IConnection::getConnection();

    if (($result  = $connection->query($query))) {

        while($rg=$result->fetch_array()) {
            $jsonResult[] = $rg;
        }

        if ($connection) {
            $connection->close();
        }
    }
    $jsonString = json_encode(array('suggestions'=>$jsonResult));
    error_log($jsonString);

    if ($jsonString==null) {
        error_log(json_last_error());
    }

    echo $jsonString;
}// if valid parameters
?>
