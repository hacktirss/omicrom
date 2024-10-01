<?php

/**
 * Description of MySQLConnection
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
namespace com\softcoatl\utils;

class MySQLConnection {
    /**
     * getConnection Gets a data base connection 
     * @param type $schemaName Schema Name
     * @param type $hostName Host URL
     * @param type $user Database user
     * @param type $password Database password
     * @return \mysql Data base object
     * @throws Exception
     */
    static function getConnection() {

        $dbc = Configuration::get();

        if (!($dbConn = mysql_connect($dbc->host, $dbc->username, $dbc->pass))) {
            error_log(mysql_error());
            die("Error solicitando conexion.");
        }
        if (!mysql_select_db($dbc->database, $dbConn)) {
            error_log(mysql_error());
            die("No se pudo conectar a la BD");
        }
        
        mysql_set_charset($dbc->charset, $dbConn);
        
        return $dbConn;
    }//getConnection
}
