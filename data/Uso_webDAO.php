<?php

/**
 * Description of TanqueDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('Uso_webVO.php');

class Uso_webDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "uso_web";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Uso_webVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = Uso_webVO) {
        error_log(print_r($objectVO, true));
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA
                . " (id,"
                . "origen,"
                . "fecha,"
                . "id_authuser) "
                . "VALUES(?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issi",
                    $objectVO->getId(),
                    $objectVO->getOrigen(),
                    $objectVO->getFecha(),
                    $objectVO->getId_authuser()
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
     * @return \Uso_webVO
     * 
     * * @param \Uso_webVO $objectVO
     */
    public function fillObject($rs) {

        $objectVO = new Uso_webVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdNvo($rs["idNvo"]);
            $objectVO->setOrigen($rs["origen"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setId_authuser($rs["id_authuser"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \Uso_webVO
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
     * @return \Uso_webVO
     */
    public function retrieve($idObjectVO, $field = "id", $Add = "") {
        $objectVO = new Uso_webVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "' $Add";
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
     * @param \Uso_webVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_authuser = ?, "
                . "fecha = ?  "
                . "WHERE id = ? AND origen = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isis",
                    $objectVO->getId_authuser(),
                    $objectVO->getFecha(),
                    $objectVO->getId(),
                    $objectVO->getOrigen()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * Validamos que un usuario se encuentre en el detalle de facturación y no pueda acceder ningun otro
     * dentro de los siguientes 11 minutos, despues de eso puede acceder cualquier otro y el caso volveria a ser lo mismo 
     * @param \Uso_webVO $Uso_webVO
     * * @param \Uso_webVO $objectVO
     * @return string Si la operación fue exitosa devolvera Msj
     */
    public function ValidaExistencia($Uso_webVO, $Origen) {
        $objectVO = new Uso_webVO();
        $objectVO = $this->retrieve($Uso_webVO->getId(), "id", " AND origen = '$Origen'");
        $Dta = $objectVO->getIdNvo() > 0 ? $objectVO->getFecha() : date("Y-m-d H:i:s");
        $dateTime = new DateTime($Dta);
        $dateTime2 = new DateTime($Uso_webVO->getFecha());
        $Df = $dateTime->diff($dateTime2);
        $DiferenciaR = 11 - $Df->i;
        com\softcoatl\utils\HTTPUtils::setSessionValue("MinutosRes", $DiferenciaR);
        $objectVO->getIdNvo() > 0 ? $dateTime->modify('+11 minutes') : $dateTime->modify('-1 minutes');
        if ($Uso_webVO->getId_authuser() == $objectVO->getId_authuser() || $dateTime->format('Y-m-d H:i:s') <= $Uso_webVO->getFecha()) {
            $Suss = $objectVO->getIdNvo() > 0 ? $this->update($Uso_webVO) : $this->create($Uso_webVO);
            ($Suss || $Suss > 0) ? \com\softcoatl\utils\Messages::MESSAGE_DEFAULT : \com\softcoatl\utils\Messages::RESPONSE_ERROR;
        } else {
            $Msj = \com\softcoatl\utils\Messages::RESPONSE_USER_LIVE;
        }
        return $Msj;
    }

}
