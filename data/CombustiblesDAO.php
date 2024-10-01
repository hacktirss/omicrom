<?php

/**
 * Description of CombustiblesDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CombustiblesVO.php');

class CombustiblesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "com";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CombustiblesVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = CombustiblesVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "clave,"
                . "clavei,"
                . "descripcion,"
                . "precio,"
                . "activo,"
                . "iva,"
                . "ieps,"
                . "medidor,"
                . "ncc_vt,"
                . "ncc_cv,"
                . "ncc_al,"
                . "ncc_mr,"
                . "ncc_ieps,"
                . "color,"
                . "claveProducto,"
                . "claveSubProducto,"
                . "ComOctanajeGas,"
                . "GasConEtanol,"
                . "ComDeEtanolEnGasolina,"
                . "otros,"
                . "marca,"
                . "tipo_producto,"
                . "marcaje,"
                . "conc_sustancia_marcaje,"
                . "marca_comercial,"
                . "cve_producto_sat,"
                . "cve_sub_producto_sat,"
                . "poder_calorifico,"
                . "densidad,"
                . "comp_azufre,"
                . "fraccion_molar,"
                . "gravedad_especifica,"
                . "comp_fosil,"
                . "comp_propano,"
                . "comp_butano"
                . ") "
                . "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssssssssss",
                    $objectVO->getClave(),
                    $objectVO->getClavei(),
                    $objectVO->getDescripcion(),
                    $objectVO->getPrecio(),
                    $objectVO->getActivo(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getMedidor(),
                    $objectVO->getNcc_vt(),
                    $objectVO->getNcc_cv(),
                    $objectVO->getNcc_al(),
                    $objectVO->getNcc_mr(),
                    $objectVO->getNcc_ieps(),
                    $objectVO->getColor(),
                    $objectVO->getClaveproducto(),
                    $objectVO->getClavesubproducto(),
                    $objectVO->getComoctanajegas(),
                    $objectVO->getGasconetanol(),
                    $objectVO->getComdeetanolengasolina(),
                    $objectVO->getOtros(),
                    $objectVO->getMarca(),
                    $objectVO->getTipo_producto(),
                    $objectVO->getMarcaje(),
                    $objectVO->getConc_sustancia_marcaje(),
                    $objectVO->getMarca_comercial(),
                    $objectVO->getCve_producto_sat(),
                    $objectVO->getCve_sub_producto_sat(),
                    $objectVO->getPoder_calorifico(),
                    $objectVO->getDensidad(),
                    $objectVO->getComp_azufre(),
                    $objectVO->getFraccion_molar(),
                    $objectVO->getGravedad_especifica(),
                    $objectVO->getComp_fosil(),
                    $objectVO->getComp_propano(),
                    $objectVO->getComp_butano()
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
     * @return \CombustiblesVO
     */
    public function fillObject($rs) {
        $objectVO = new CombustiblesVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setClave($rs["clave"]);
            $objectVO->setClavei($rs["clavei"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setMedidor($rs["medidor"]);
            $objectVO->setNcc_vt($rs["ncc_vt"]);
            $objectVO->setNcc_cv($rs["ncc_cv"]);
            $objectVO->setNcc_al($rs["ncc_al"]);
            $objectVO->setNcc_mr($rs["ncc_mr"]);
            $objectVO->setNcc_ieps($rs["ncc_ieps"]);
            $objectVO->setColor($rs["color"]);
            $objectVO->setClaveproducto($rs["claveProducto"]);
            $objectVO->setClavesubproducto($rs["claveSubProducto"]);
            $objectVO->setComoctanajegas($rs["ComOctanajeGas"]);
            $objectVO->setGasconetanol($rs["GasConEtanol"]);
            $objectVO->setComdeetanolengasolina($rs["ComDeEtanolEnGasolina"]);
            $objectVO->setOtros($rs["otros"]);
            $objectVO->setMarca($rs["marca"]);
            $objectVO->setTipo_producto($rs["tipo_producto"]);
            $objectVO->setMarcaje($rs["marcaje"]);
            $objectVO->setConc_sustancia_marcaje($rs["conc_sustancia_marcaje"]);
            $objectVO->setMarca_comercial($rs["marca_comercial"]);
            $objectVO->setCve_producto_sat($rs["cve_producto_sat"]);
            $objectVO->setCve_sub_producto_sat($rs["cve_sub_producto_sat"]);
            $objectVO->setPoder_calorifico($rs["poder_calorifico"]);
            $objectVO->setDensidad($rs["densidad"]);
            $objectVO->setComp_azufre($rs["comp_azufre"]);
            $objectVO->setFraccion_molar($rs["fraccion_molar"]);
            $objectVO->setGravedad_especifica($rs["gravedad_especifica"]);
            $objectVO->setComp_fosil($rs["comp_fosil"]);
            $objectVO->setComp_propano($rs["comp_propano"]);
            $objectVO->setComp_butano($rs["comp_butano"]);
            $objectVO->setClave_instalacion($rs["clave_instalacion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CombustiblesVO
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
     * @return \CombustiblesVO
     */
    public function retrieve($idObjectVO, $field = "id", $activos = true) {
        $objectVO = new CombustiblesVO();
        $sql = "SELECT " . self::TABLA . ".*,cia.clave_instalacion FROM " . self::TABLA . " LEFT JOIN cia ON TRUE "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if($activos){
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
     * @param \CombustiblesVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = CombustiblesVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "clave = ?, "
                . "clavei = ?, "
                . "descripcion = ?, "
                . "precio = ?, "
                . "activo = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "medidor = ?, "
                . "ncc_vt = ?, "
                . "ncc_cv = ?, "
                . "ncc_al = ?, "
                . "ncc_mr = ?, "
                . "ncc_ieps = ?, "
                . "color = ?, "
                . "claveProducto = ?, "
                . "claveSubProducto = ?, "
                . "ComOctanajeGas = ?, "
                . "GasConEtanol = ?, "
                . "ComDeEtanolEnGasolina = ?, "
                . "otros = ?, "
                . "marca = ?, "
                . "tipo_producto = ?, "
                . "marcaje = ?, "
                . "conc_sustancia_marcaje = ?, "
                . "marca_comercial = ?, "
                . "cve_producto_sat = ?, "
                . "cve_sub_producto_sat = ?, "
                . "poder_calorifico = ?, "
                . "densidad = ?, "
                . "comp_azufre = ?, "
                . "fraccion_molar = ?, "
                . "gravedad_especifica = ?, "
                . "comp_fosil = ?, "
                . "comp_propano = ?, "
                . "comp_butano = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssddddddddi",
                    $objectVO->getClave(),
                    $objectVO->getClavei(),
                    $objectVO->getDescripcion(),
                    $objectVO->getPrecio(),
                    $objectVO->getActivo(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getMedidor(),
                    $objectVO->getNcc_vt(),
                    $objectVO->getNcc_cv(),
                    $objectVO->getNcc_al(),
                    $objectVO->getNcc_mr(),
                    $objectVO->getNcc_ieps(),
                    $objectVO->getColor(),
                    $objectVO->getClaveproducto(),
                    $objectVO->getClavesubproducto(),
                    $objectVO->getComoctanajegas(),
                    $objectVO->getGasconetanol(),
                    $objectVO->getComdeetanolengasolina(),
                    $objectVO->getOtros(),
                    $objectVO->getMarca(),
                    $objectVO->getTipo_producto(),
                    $objectVO->getMarcaje(),
                    $objectVO->getConc_sustancia_marcaje(),
                    $objectVO->getMarca_comercial(),
                    $objectVO->getCve_producto_sat(),
                    $objectVO->getCve_sub_producto_sat(),
                    $objectVO->getPoder_calorifico(),
                    $objectVO->getDensidad(),
                    $objectVO->getComp_azufre(),
                    $objectVO->getFraccion_molar(),
                    $objectVO->getGravedad_especifica(),
                    $objectVO->getComp_fosil(),
                    $objectVO->getComp_propano(),
                    $objectVO->getComp_butano(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
