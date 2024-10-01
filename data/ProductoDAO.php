<?php

/**
 * Description of ProductoDAO
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
include_once ('ProductoVO.php');

class ProductoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "inv";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ProductoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ProductoVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "umedida,"
                . "rubro,"
                . "activo,"
                . "existencia,"
                . "minimo,"
                . "maximo,"
                . "precio,"
                . "dlls,"
                . "codigo,"
                . "ncc_vt,"
                . "ncc_cv,"
                . "ncc_al,"
                . "inv_cunidad,"
                . "inv_cproducto,"
                . "clave_producto,"
                . "retiene_iva,"
                . "porcentaje,"
                . "factorIva"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssiiidsssssssisii",
                    $objectVO->getDescripcion(),
                    $objectVO->getUmedida(),
                    $objectVO->getRubro(),
                    $objectVO->getActivo(),
                    $objectVO->getExistencia(),
                    $objectVO->getMinimo(),
                    $objectVO->getMaximo(),
                    $objectVO->getPrecio(),
                    $objectVO->getDlls(),
                    $objectVO->getCodigo(),
                    $objectVO->getNcc_vt(),
                    $objectVO->getNcc_cv(),
                    $objectVO->getNcc_al(),
                    $objectVO->getInv_cunidad(),
                    $objectVO->getInv_cproducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getRetiene_iva(),
                    $objectVO->getPorcentaje(),
                    $objectVO->getFactorIva()
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
     * @return \ProductoVO
     */
    public function fillObject($rs) {
        $objectVO = new ProductoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setUmedida($rs["umedida"]);
            $objectVO->setRubro($rs["rubro"]);
            $objectVO->setCategoria($rs["categoria"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setExistencia($rs["existencia"]);
            $objectVO->setMinimo($rs["minimo"]);
            $objectVO->setMaximo($rs["maximo"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setCosto($rs["costo"]);
            $objectVO->setCosto_prom($rs["costo_prom"]);
            $objectVO->setDlls($rs["dlls"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setNcc_vt($rs["ncc_vt"]);
            $objectVO->setNcc_cv($rs["ncc_cv"]);
            $objectVO->setNcc_al($rs["ncc_al"]);
            $objectVO->setInv_cunidad($rs["inv_cunidad"]);
            $objectVO->setInv_cproducto($rs["inv_cproducto"]);
            $objectVO->setClave_producto($rs["clave_producto"]);
            $objectVO->setRetiene_iva($rs["retiene_iva"]);
            $objectVO->setPorcentaje($rs["porcentaje"]);
            $objectVO->setFactorIva($rs["factorIva"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ProductoVO
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
     * @return \ProductoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ProductoVO();
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
     * @param \ProductoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = ProductoVO) {
        //$objectVO = new ProductoVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "umedida = ?, "
                . "rubro = ?, "
                . "categoria = ?, "
                . "activo = ?, "
                . "existencia = ?, "
                . "minimo = ?, "
                . "maximo = ?, "
                . "precio = ?, "
                . "costo = ?, "
                . "costo_prom = ?, "
                . "dlls = ?, "
                . "codigo = ?, "
                . "ncc_vt = ?, "
                . "ncc_cv = ?, "
                . "ncc_al = ?, "
                . "inv_cunidad = ?, "
                . "inv_cproducto = ? ,"
                . "clave_producto = ? ,"
                . "retiene_iva = ? ,"
                . "porcentaje = ?, "
                . "factorIva = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssiisssssssssssisiii",
                    $objectVO->getDescripcion(),
                    $objectVO->getUmedida(),
                    $objectVO->getRubro(),
                    $objectVO->getCategoria(),
                    $objectVO->getActivo(),
                    $objectVO->getExistencia(),
                    $objectVO->getMinimo(),
                    $objectVO->getMaximo(),
                    $objectVO->getPrecio(),
                    $objectVO->getCosto(),
                    $objectVO->getCosto_prom(),
                    $objectVO->getDlls(),
                    $objectVO->getCodigo(),
                    $objectVO->getNcc_vt(),
                    $objectVO->getNcc_cv(),
                    $objectVO->getNcc_al(),
                    $objectVO->getInv_cunidad(),
                    $objectVO->getInv_cproducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getRetiene_iva(),
                    $objectVO->getPorcentaje(),
                    $objectVO->getFactorIva(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
