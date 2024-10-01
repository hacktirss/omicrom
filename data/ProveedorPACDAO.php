<?php

/*
 * ProveedorPACDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('ProveedorPACVO.php');

class ProveedorPACDAO {

    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    public function getActive() {
        $pac = new ProveedorPACVO();
        $sql = "SELECT * FROM proveedor_pac WHERE activo = 1";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $pac->setId_pac($rs['id_pac']);
            $pac->setClave_pac($rs['clave_pac']);
            $pac->setNombre_pac($rs['nombre_pac']);
            $pac->setUrl_webservice($rs['url_webservice']);
            $pac->setUsuario($rs['usuario']);
            $pac->setPassword($rs['password']);
            $pac->setClave_aux($rs['clave_aux']);
            $pac->setClave_aux2($rs['clave_aux2']);
            $pac->setActivo($rs['activo']);
            $pac->setPruebas($rs['pruebas']);
            $pac->setPrioridad($rs['prioridad']);
        }
        return $pac;
    }
}
