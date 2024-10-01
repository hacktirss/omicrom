<?php

/**
 * Description of V_CorporativoDAO
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
include_once ('V_CorporativoVO.php');

class V_CorporativoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "variables_corporativo";
    const ENCRIPT_FIELD = "encrypt_fields";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \V_CorporativoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "llave,"
                . "valor"
                . ") "
                . "VALUES(?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ss",
                    $objectVO->getLlave(),
                    $objectVO->getValor()
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
     * @return \V_CorporativoVO
     */
    public function fillObject($rs) {
        $objectVO = new V_CorporativoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setLlave($rs["llave"]);
            $objectVO->setValor($rs["valor"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \V_CorporativoVO
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
     * @return \V_CorporativoVO
     */
    public function retrieve($idObjectVO, $field = "llave") {
        $objectVO = new V_CorporativoVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \V_CorporativoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "llave = ?, "
                . "valor = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssi",
                    $objectVO->getLlave(),
                    $objectVO->getValor(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class ListaLlaves extends BasicEnum {

    const USO_CORPORATIVO = "uso_corporativo";
    const URL_SYNC_DATA = "url_sync_data";
    const IP_CORPORATIVO = "ip_corporativo";
    const DOMINIO_CORPORATIVO = "dominio_corporativo";
    const SINCRONIZA_CLIENTES = "sincroniza_clientes";
    const SINCORONIZA_PRECIOS = "sincroniza_precios";
    const ENVIA_TANQUES = "envia_tanques";
    const SINCRONIZA_PRODUCTOS = "sincroniza_productos";
    const SINCRONIZA_CODIGOS = "sincroniza_codigos";
    const URL_FACT_ONLINE = "url_fact_online";
    const BALANCE = "balance";
    const LIMITE_IMPRESION = "limite_impresion";
    const PWD_CONF_POS = "pwd_conf_pos";
    const ENVIA_VOLUMETRICO = "envia_volumetrico";
    const AUTORIZACION_CORPORATIVO = "autorizacion_corporativo";
    const FECHA_INICIO = "fecha_inicio";
    const ACTUALIZA_POS = "actualiza_pos";
    const PERIODO_VENTAS = "periodo_ventas";
    const FACTURA_ELECTRONICA = "factura_electronica";
    const PERIODO_FACTURA = "periodo_factura";
    const POS_FORMAPAGO = "pos_formaPago";
    const CV_VALIDA_HORA = "CV_VALIDA_HORA";
    const FACT_GLOBAL_VENTANA = "fact_global_ventana";
    const IMPRESIONES_POS = "impresiones_pos";
    const CC_ENDPONIT = "cc_endpoint";
    const WSO_FACTORY = "wso_factory";
    const LINK_SOPORTE = "link_soporte";
    const ENCRIPT_FIELDS = "encrypt_fields";
    const FACTURACION_ABIERTA = "facturacion_abierta";
    const PAGOS_TICKETS = "pago_tickets";
    const DESGLOSE_DEPOSITOS = "desglosa_depositos";
}
