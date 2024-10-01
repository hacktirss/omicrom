<?php

/**
 * Description of ManProDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('ManProVO.php');

class ManProDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "man_pro";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ManProVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ManProVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "dispensario,"
                . "posicion,"
                . "manguera,"
                . "dis_mang,"
                . "producto,"
                . "isla,"
                . "activo,"
                . "factor,"
                . "enable,"
                . "proteccion,"
                . "cpu,"
                . "m,"
                . "presente,"
                . "manf,"
                . "lc_emr3,"
                . "back,"
                . "tanque,"
                . "totalizadorV,"
                . "totalizador$,"
                . "vigencia_calibracion"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE())";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssss",
                    $objectVO->getDispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getDis_mang(),
                    $objectVO->getProducto(),
                    $objectVO->getIsla(),
                    $objectVO->getActivo(),
                    $objectVO->getFactor(),
                    $objectVO->getEnable(),
                    $objectVO->getProteccion(),
                    $objectVO->getCpu(),
                    $objectVO->getM(),
                    $objectVO->getPresente(),
                    $objectVO->getManf(),
                    $objectVO->getLc_emr3(),
                    $objectVO->getBack(),
                    $objectVO->getTanque(),
                    $objectVO->getTotalizadorV(),
                    $objectVO->getTotalizadorI()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($this->conn->error);
            }
            $ps->close();
        } else {
            error_log($this->conn->error);
        }
        return $id;
    }

    /**
     * 
     * @param array() $rs
     * @return \ManProVO
     */
    public function fillObject($rs) {
        $objectVO = new ManProVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDispensario($rs["dispensario"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setManguera($rs["manguera"]);
            $objectVO->setDis_mang($rs["dis_mang"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setIsla($rs["isla"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setFactor($rs["factor"]);
            $objectVO->setEnable($rs["enable"]);
            $objectVO->setProteccion($rs["proteccion"]);
            $objectVO->setCpu($rs["cpu"]);
            $objectVO->setM($rs["m"]);
            $objectVO->setPresente($rs["presente"]);
            $objectVO->setManf($rs["manf"]);
            $objectVO->setLc_emr3($rs["lc_emr3"]);
            $objectVO->setBack($rs["back"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setTotalizadorV($rs["totalizadorV"]);
            $objectVO->setTotalizadorI($rs["totalizador$"]);
            $objectVO->setVigencia_calibracion($rs["vigencia_calibracion"]);
            $objectVO->setValor_calibracion($rs["valor_calibracion"]);
            $objectVO->setNum_medidor($rs["num_medidor"]);
            $objectVO->setTipo_medidor($rs["tipo_medidor"]);
            $objectVO->setModelo_medidor($rs["modelo_medidor"]);
            $objectVO->setIncertidumbre($rs["incertidumbre"]);
            $objectVO->setDescripcion($rs["descripcion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ManProVO
     */
    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        } else {
            error_log($this->conn->error);
        }
        return $array;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \ManProVO
     */
    public function retrieve($idObjectVO, $field = "id", $activos = true) {
        $objectVO = new ManProVO();
        $sql = "SELECT " . self::TABLA . ".*, medidores.valor_calibracion, medidores.num_medidor, medidores.tipo_medidor, medidores.modelo_medidor, medidores.incertidumbre, com.descripcion "
                . "FROM " . self::TABLA . " "
                . "LEFT JOIN com ON com.clavei = man_pro.producto AND com.activo = 'Si' "
                . "LEFT JOIN medidores ON man_pro.dispensario = medidores.num_dispensario AND man_pro.posicion = medidores.posicion AND man_pro.manguera = medidores.num_manguera "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if ($activos) {
            $sql .= " AND " . self::TABLA . ".activo = 'Si'";
        }
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \ManProVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = ManProVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "dispensario = ?, "
                . "posicion = ?, "
                . "manguera = ?, "
                . "dis_mang = ?, "
                . "producto = ?, "
                . "isla = ?, "
                . "activo = ?, "
                . "factor = ?, "
                . "enable = ?, "
                . "proteccion = ?, "
                . "cpu = ?, "
                . "m = ?, "
                . "presente = ?, "
                . "manf = ?, "
                . "lc_emr3 = ?, "
                . "back = ?, "
                . "tanque = ?, "
                . "totalizadorV = ?, "
                . "totalizador$ = ?, "
                . "vigencia_calibracion = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssssi",
                    $objectVO->getDispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getDis_mang(),
                    $objectVO->getProducto(),
                    $objectVO->getIsla(),
                    $objectVO->getActivo(),
                    $objectVO->getFactor(),
                    $objectVO->getEnable(),
                    $objectVO->getProteccion(),
                    $objectVO->getCpu(),
                    $objectVO->getM(),
                    $objectVO->getPresente(),
                    $objectVO->getManf(),
                    $objectVO->getLc_emr3(),
                    $objectVO->getBack(),
                    $objectVO->getTanque(),
                    $objectVO->getTotalizadorV(),
                    $objectVO->getTotalizadorI(),
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
