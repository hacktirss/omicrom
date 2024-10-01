<?php

/**
 * IConnection
 *  Database connection using mysqli library
 * XXXXXXXXXX®
 * © 2019, Softcoatl
 * http://www.softcoatl.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jan 2017
 */

namespace com\softcoatl\utils;

class IConnection {

    /**
     * getConnection Gets a new data base connection object
     * @param type $schemaName Schema Name
     * @param type $hostName Host URL
     * @param type $user Database user
     * @param type $password Database password
     * @return \mysqli Data base object
     * @throws Exception
     */
    public static function getConnection() {

        $dbc = Configuration::get();

        $dbConn = new \mysqli($dbc->host, $dbc->username, $dbc->pass, $dbc->database);

        if ($dbConn->connect_errno > 0) {
            if ($dbConn->connect_errno) {
                throw new \Exception("Error conectando con base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        if (!$dbConn->query("SET lc_time_names = 'es_MX'")) {
            if ($dbConn->error) {
                throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        if (property_exists($dbc, "charset") && !$dbConn->set_charset($dbc->charset)) {
            if ($dbConn->error) {
                throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        return $dbConn;
    }

    public static function getConnectionRepository() {

        $dbc = Configuration::getRepository();

        $dbConn = new \mysqli($dbc->host, $dbc->username, $dbc->pass, $dbc->database);

        if ($dbConn->connect_errno > 0) {
            if ($dbConn->connect_errno) {
                throw new \Exception("Error conectando con base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        if (!$dbConn->query("SET lc_time_names = 'es_MX'")) {
            if ($dbConn->error) {
                throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        if (property_exists($dbc, "charset") && !$dbConn->set_charset($dbc->charset)) {
            if ($dbConn->error) {
                throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
            }
        }
        return $dbConn;
    }

    public static function execSql($sql) {
        $object = array();
        $mysqli = getConnection();
        try {
            if (($query = $mysqli->query($sql)) && ($rs = $query->fetch_assoc())) {
                $object = $rs;
            }
        } catch (\Exception $ex) {
            error_log($ex);
        } finally {
            if ($mysqli->errno > 0) {
                error_log($mysqli->error);
                error_log($sql);
            }
            $mysqli->close();
            return $object;
        }
    }

    /**
     * Devuelve array con registros
     * @param string $sql
     * @param IConnection $connecion
     * @return array
     */
    public static function getRowsFromQuery($sql, $connecion = null) {
        $object = array();
        $mysqli = $connecion == null ? getConnection() : $connecion;
        try {
            if (($query = $mysqli->query($sql))) {
                while (($rs = $query->fetch_array())) {
                    $object[] = $rs;
                }
            }
        } catch (\Exception $ex) {
            error_log($ex);
        } finally {
            if ($mysqli->errno > 0) {
                error_log($mysqli->error);
                error_log($sql);
            }
            if(is_null($connecion)){
                $mysqli->close();
            }
            return $object;
        }
    }

}
