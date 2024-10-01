<?php

/*
 * MetodoDePagoDAO
 * common®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since ago 2017
 */

include_once ('mysqlUtils.php');
include_once ('MetodoDePagoVO.php');

class MetodoDePagoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    public function retrieve($clave) {
        $mdp = new MetodoDePagoVO();
        $sql = "SELECT * FROM cfdi33_c_mpago WHERE clave = '".$clave."'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $mdp->setClave($rs['clave']);
            $mdp->setDescripcion($rs['descripcion']);
        }
        error_log($mdp);
       return $mdp;
    }//retrieve
}//MetodoDePago
