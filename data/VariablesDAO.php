<?php

/*
 * VariablesDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
class VariablesDAO {

    public static function getVariable($vName) {
        $conn = getConnection();
        $sql = "SELECT ".$vName." FROM variables";
        $variable = "";

        if (($query = $conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $variable = $rs[$vName];
        }
        $conn->close();

        return $variable;
    }
}
