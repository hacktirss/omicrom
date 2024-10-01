<?php

/**
 * Description of TarjetaDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('TarjetaVO.php');

class TarjetaDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "unidades";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TarjetaVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = TarjetaVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "codigo,"
                . "impreso"
                . ") "
                . "VALUES(?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ss",
                    $objectVO->getCodigo(),
                    $objectVO->getImpreso()
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
     * @return \TarjetaVO
     */
    public function fillObject($rs) {
        $objectVO = new TarjetaVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setPlacas($rs["placas"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setImpreso($rs["impreso"]);
            $objectVO->setCombustible($rs["combustible"]);
            $objectVO->setLitros($rs["litros"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setPeriodo($rs["periodo"]);
            $objectVO->setSimultaneo($rs["simultaneo"]);
            $objectVO->setLocal($rs["local"]);
            $objectVO->setLuni($rs["luni"]);
            $objectVO->setLunf($rs["lunf"]);
            $objectVO->setMari($rs["mari"]);
            $objectVO->setMarf($rs["marf"]);
            $objectVO->setMiei($rs["miei"]);
            $objectVO->setMief($rs["mief"]);
            $objectVO->setJuei($rs["juei"]);
            $objectVO->setJuef($rs["juef"]);
            $objectVO->setViei($rs["viei"]);
            $objectVO->setVief($rs["vief"]);
            $objectVO->setSabi($rs["sabi"]);
            $objectVO->setSabf($rs["sabf"]);
            $objectVO->setDomi($rs["domi"]);
            $objectVO->setDomf($rs["domf"]);
            $objectVO->setInteres($rs["interes"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setDepto($rs["depto"]);
            $objectVO->setNip($rs["nip"]);
            $objectVO->setChip($rs["chip"]);
            $objectVO->setDepartamento($rs["departamento"]);
            $objectVO->setNumeco($rs["numeco"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TarjetaVO
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
     * @return \TarjetaVO
     */
    public function retrieve($idObjectVO, $field = "id", $id = 0) {
        $objectVO = new TarjetaVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        if (!empty($id)) {
            $sql .= " AND id <> $id LIMIT 1";
        }
        //error_log($sql);
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
     * @param \TarjetaVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = TarjetaVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "cliente = ?, "
                . "placas = ?, "
                . "codigo = ?, "
                . "impreso = ?, "
                . "combustible = ?, "
                . "litros = ?, "
                . "importe = ?, "
                . "periodo = ?, "
                . "simultaneo = ?, "
                . "local = ?, "
                . "luni = ?, "
                . "lunf = ?, "
                . "mari = ?, "
                . "marf = ?, "
                . "miei = ?, "
                . "mief = ?, "
                . "juei = ?, "
                . "juef = ?, "
                . "viei = ?, "
                . "vief = ?, "
                . "sabi = ?, "
                . "sabf = ?, "
                . "domi = ?, "
                . "domf = ?, "
                . "interes = ?, "
                . "estado = ?, "
                . "depto = ?, "
                . "nip = ?, "
                . "chip = ?, "
                . "departamento = ?, "
                . "numeco = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssssssssssssssssi",
                    $objectVO->getDescripcion(),
                    $objectVO->getCliente(),
                    $objectVO->getPlacas(),
                    $objectVO->getCodigo(),
                    $objectVO->getImpreso(),
                    $objectVO->getCombustible(),
                    $objectVO->getLitros(),
                    $objectVO->getImporte(),
                    $objectVO->getPeriodo(),
                    $objectVO->getSimultaneo(),
                    $objectVO->getLocal(),
                    $objectVO->getLuni(),
                    $objectVO->getLunf(),
                    $objectVO->getMari(),
                    $objectVO->getMarf(),
                    $objectVO->getMiei(),
                    $objectVO->getMief(),
                    $objectVO->getJuei(),
                    $objectVO->getJuef(),
                    $objectVO->getViei(),
                    $objectVO->getVief(),
                    $objectVO->getSabi(),
                    $objectVO->getSabf(),
                    $objectVO->getDomi(),
                    $objectVO->getDomf(),
                    $objectVO->getInteres(),
                    $objectVO->getEstado(),
                    $objectVO->getDepto(),
                    $objectVO->getNip(),
                    $objectVO->getChip(),
                    $objectVO->getDepartamento(),
                    $objectVO->getNumeco(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusUnidad extends BasicEnum {

    const ACTIVA = "a";
    const INACTIVA = "d";

}
