<?php

/**
 * Description of MensajesDAO
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
include_once ('MensajeVO.php');

class MensajesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "msj";

    private $conn;
    private static $instance;
    
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new MensajesDAO();
        }
        return self::$instance;
    }
    
    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \MensajeVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tipo,"
                . "de,"
                . "para,"
                . "titulo,"
                . "nota,"
                . "bd,"
                . "fecha,"
                . "hora,"
                . "vigencia"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, 0, CURRENT_DATE(), CURRENT_TIME(), ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getTipo(),
                    $objectVO->getDe(),
                    $objectVO->getPara(),
                    $objectVO->getTitulo(),
                    $objectVO->getNota(),
                    $objectVO->getVigencia()
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
     * @return \MensajeVO
     */
    public function fillObject($rs) {
        $objectVO = new MensajeVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setTipo($rs["tipo"]);
            $objectVO->setDe($rs["de"]);
            $objectVO->setPara($rs["para"]);
            $objectVO->setTitulo($rs["titulo"]);
            $objectVO->setNota($rs["nota"]);
            $objectVO->setBd($rs["bd"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setHora($rs["hora"]);
            $objectVO->setVigencia($rs["vigencia"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \MensajeVO
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
     * @return \MensajeVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new MensajeVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \MensajeVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "tipo = ?, "
                . "de = ?, "
                . "para = ?, "
                . "titulo = ?, "
                . "nota = ?, "
                . "bd = ?, "
                . "fecha = ?, "
                . "hora = ?, "
                . "vigencia = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssi",
                    $objectVO->getTipo(),
                    $objectVO->getDe(),
                    $objectVO->getPara(),
                    $objectVO->getTitulo(),
                    $objectVO->getNota(),
                    $objectVO->getBd(),
                    $objectVO->getFecha(),
                    $objectVO->getHora(),
                    $objectVO->getVigencia(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }
    
    /**
     * Genera mensaje instantaneo
     * @param string $Nombre Usuario que genera el mensaje
     * @param string $Titulo Breve descripcion del mensaje
     * @param string $Mensaje Mensaje a mostrar
     * @param int $Vigencia dias que durará el mensaje
     * @return int 
     */
    public function createMsj($Nombre,$Titulo, $Mensaje, $Vigencia, $comando = 0){
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tipo,"
                . "de,"
                . "para,"
                . "titulo,"
                . "nota,"
                . "bd,"
                . "fecha,"
                . "hora,"
                . "vigencia"
                . ") "
                . "VALUES('R', ?, 'Estacion de servicio', ?, ?, ?, CURRENT_DATE(), CURRENT_TIME(), ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssii",
                    $Nombre,
                    $Titulo,
                    $Mensaje,
                    $comando,
                    $Vigencia
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

}

abstract class TipoMensaje extends BasicEnum {
    const LEIDO = "L";
    const SIN_LEER = "R";
}
