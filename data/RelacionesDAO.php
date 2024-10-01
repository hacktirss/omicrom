<?php

/*
 * RelacionesDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('RelacionesVO.php');

class RelacionesDAO {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * Parses result set into VO
     * @param array $rs
     * @return RelacionesVO
     */
    private function parseRS($rs) {
        $relacion = new RelacionesVO();
        $relacion->setFolio($rs['rfolio']);
        $relacion->setTipoRelacion($rs['tiporelacion']);
        $relacion->setUuid($rs['ruuid']);
        return $relacion;
    }

    public function getRelacion($id) {
        $relacion = new RelacionesVO();
        $sql = "
            SELECT F.id, IFNULL(F.tiporelacion,  '') tiporelacion, IFNULL(R.id,  '') rfolio, IFNULL(R.uuid,  '') ruuid
            FROM fc F
            LEFT JOIN fc R ON R.id = F.relacioncfdi
            WHERE F.id = " . $id;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $relacion = $this->parseRS($rs);
        }
        return $relacion;
    }//getRelacion

    public function getRelacionNC($id) {
        $relacion = new RelacionesVO();
        $sql = "
            SELECT F.id, IFNULL(F.tiporelacion,  '') tiporelacion, IFNULL(R.id,  '') rfolio, IFNULL(R.uuid,  '') ruuid
            FROM nc F
            LEFT JOIN fc R ON R.id = F.relacioncfdi
            WHERE F.id = " . $id;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $relacion = $this->parseRS($rs);
        }
        return $relacion;
    }//getRelacion
}//RelacionesDAO
