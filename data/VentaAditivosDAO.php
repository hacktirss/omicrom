<?php

/**
 * Description of VentaAditivosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('VentaAditivosVO.php');

class VentaAditivosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "vtaditivos";
    const TIPO = "A";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \VentaAditivosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "clave,"
                . "cantidad,"
                . "unitario,"
                . "costo,"
                . "total,"
                . "corte,"
                . "posicion,"
                . "fecha,"
                . "descripcion,"
                . "cliente,"
                . "vendedor,"
                . "referencia,"
                . "iva,"
                . "tm,"
                . "enviado_grupo,"
                . "comentarios,"
                . "idtransaccion) "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isssssssssssssssi",
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getUnitario(),
                    $objectVO->getCosto(),
                    $objectVO->getTotal(),
                    $objectVO->getCorte(),
                    $objectVO->getPosicion(),
                    $objectVO->getFecha(),
                    $objectVO->getDescripcion(),
                    $objectVO->getCliente(),
                    $objectVO->getVendedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getIva(),
                    $objectVO->getTm(),
                    $objectVO->getEnviado_grupo(),
                    $objectVO->getComentarios(),
                    $objectVO->getIdtransaccion()
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
     * @return \VentaAditivosVO
     */
    public function fillObject($rs) {
        $objectVO = new VentaAditivosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setProducto($rs["clave"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setUnitario($rs["unitario"]);
            $objectVO->setCosto($rs["costo"]);
            $objectVO->setTotal($rs["total"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setVendedor($rs["vendedor"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setPagado($rs["pagado"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setEnviado($rs["enviado"]);
            $objectVO->setTm($rs["tm"]);
            $objectVO->setDatalist($rs["datalist"]);
            $objectVO->setEnviado_grupo($rs["enviado_grupo"]);
            $objectVO->setComentarios($rs["comentarios"]);
            $objectVO->setIdtransaccion($rs["idtransaccion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \VentaAditivosVO
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
     * @return \VentaAditivosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new VentaAditivosVO();
        $sql = "SELECT " . self::TABLA . ".*, IFNULL(CONCAT(uni.codigo, ' | ', TRIM(uni.impreso), ' | ', TRIM(uni.descripcion) , ' | ', TRIM(uni.placas)), '') datalist "
                . "FROM " . self::TABLA . " "
                . "LEFT JOIN unidades uni ON uni.codigo = " . self::TABLA . ".codigo "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        error_log($sql);
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
     * @param \VentaAditivosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "clave = ?, "
                . "cantidad = ?, "
                . "unitario = ?, "
                . "costo = ?, "
                . "total = ?, "
                . "corte = ?, "
                . "posicion = ?, "
                . "fecha = ?, "
                . "descripcion = ?, "
                . "cliente = ?, "
                . "vendedor = ?, "
                . "referencia = ?, "
                . "pagado = ?, "
                . "codigo = ?, "
                . "iva = ?, "
                . "uuid = ?, "
                . "enviado = ?, "
                . "tm = ? ,"
                . "enviado_grupo = ?, "
                . "comentarios = ?, "
                . "idtransaccion = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iidddiissiiiisdsisssii",
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getUnitario(),
                    $objectVO->getCosto(),
                    $objectVO->getTotal(),
                    $objectVO->getCorte(),
                    $objectVO->getPosicion(),
                    $objectVO->getFecha(),
                    $objectVO->getDescripcion(),
                    $objectVO->getCliente(),
                    $objectVO->getVendedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getPagado(),
                    $objectVO->getCodigo(),
                    $objectVO->getIva(),
                    $objectVO->getUuid(),
                    $objectVO->getEnviado(),
                    $objectVO->getTm(),
                    $objectVO->getEnviado_grupo(),
                    $objectVO->getComentarios(),
                    $objectVO->getIdtransaccion(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    public function getProductos($FechaI, $FechaF, $Cliente = 0) {
        $SqlAddRm = "";
        if ($Cliente > 0) {
            $SqlAddRm = "AND vta.cliente = $Cliente ";
            $SqlAddRm = "AND fc.cliente = $Cliente ";
        }
        $cSql = "
        SELECT vendido.descripcion,IFNULL((vendido.piezas),0) AS piezas,IFNULL((vendido.importeV),0) AS Importe_Vendido,IFNULL((facturado.piezas),0) AS piezas_fact,IFNULL((facturado.importef),0) AS Importe_facturado
                    FROM (
                            SELECT clave,descripcion,sum(cantidad) as piezas,sum(total) as importev
                                    FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
                                        WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                                        AND vta.tm = 'C' $SqlAddRm
                                        group by vta.clave			  
                        )vendido
                        left join 
                        (
                            SELECT 
                                fcd.producto,
                                inv.descripcion
                                , sum(fcd.cantidad) as piezas
                                , sum(fcd.importe) importef
                                FROM fc inner join fcd
                                ON fc.id = fcd.id inner join inv
                                on fcd.producto = inv.id
                                WHERE DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                                and fc.uuid != '-----' 
                                and status = 1
                                and producto > 5
                                and fcd.ticket  >= 0	
                                $SqlAddRm
                                group by inv.id
                        ) facturado ON vendido.clave = facturado.producto inner join inv on inv.id = vendido.clave
       ";
        $array = array();
        $i = 0;
        if (($query = $this->conn->query($cSql))) {

            while (($rs = $query->fetch_assoc())) {
                $array[$i] = $rs;
                $i++;
            }
        } else {
            error_log($this->conn->error);
        }
        return $array;
    }

}
