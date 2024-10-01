<?php
/**
 * PDOConnection
 *  Database connection using PDO library.
 * XXXXXXXXXX®
 * © 2019, Softcoatl
 * http://www.softcoatl.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2019
 */
namespace com\softcoatl\utils;

class PDOConnection {

    private static $options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);

    /**
     * getConnection Gets a new data base connection object
     * @return \PDO Data base object
     * @throws Exception
     */
    static function getConnection() {

        $dbc = Configuration::get();
        $dsn = $dbc->driver . ":host=" . $dbc->host . ";dbname=" . $dbc->database . ( array_key_exists("charset", $dbc) ? ";charset=" . $dbc->charset : "" );
        
        try {
            $dbConn = new \PDO($dsn, $dbc->username, $dbc->pass, PDOConnection::$options);
            $dbConn->exec("SET lc_time_names = 'es_MX'");
        } catch(\PDOException $pdoe) {
            error_log($pdoe->getMessage());
            throw new \Exception("Error conectando con la base de datos", $pdoe->getCode(), $pdoe);
        }

        return $dbConn;
    }//getConnection
}

