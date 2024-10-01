<?php

include_once 'data/ListasValorVO.php';
include_once 'data/ListasValorDAO.php';

/**
 * Description of ListasCatalogo
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ListasCatalogo {

    public static function listaNombreCatalogo($nombreSelect, $nombreCatalogo, $adicional = "", $opciones = "") {
        $listasDAO = new ListasValorDAO();
        $SelectLista = "SELECT * FROM listas,listas_valor WHERE listas.id_lista = listas_valor.id_lista_lista_valor AND nombre_lista='$nombreCatalogo';";
        $array = $listasDAO->getAll($SelectLista);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "' class='texto_tablas' $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'> " . $adicional . " </option>";
        }
        foreach ($array as $key => $value) {
            $html .= "<option value='" . $value->getLlave_lista_valor() . "'> " . $value->getValor_lista_valor() . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getArrayList($nombreCatalogo) {
        $array = array();
        $listaDAO = new ListasValorDAO();
        $SelectLista = "SELECT * FROM listas,listas_valor WHERE listas.id_lista = listas_valor.id_lista_lista_valor AND nombre_lista='$nombreCatalogo';";
        $listas = $listaDAO->getAll($SelectLista);
        foreach ($listas as $value) {
            $array[$value->getLlave_lista_valor()] = $value->getValor_lista_valor();
        }

        return $array;
    }

    public static function getRolesUsuarios($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT id, UPPER(perfil) perfil FROM authuser_rol WHERE estado = 1";
        $result = $mysqli->query($selectPosiciones) or error_log($mysqli->error);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "' class='texto_tablas'  $opciones>";
        if (!empty($adicional)) {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["id"] . "'>" . $rg["perfil"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getPosiciones($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT posicion FROM man WHERE activo = 'Si'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg[posicion] . "'>" . $rg[posicion] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getDispensarios($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT DISTINCT dispensario FROM man_pro WHERE activo = 'Si'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if (is_array($adicional) && count($adicional) > 0) {
            foreach ($adicional as $key => $value) {
                $html .= "<option value='$key'>$value</option>";
            }
        } else {
            if ($adicional !== "") {
                $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
            }
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg[dispensario] . "'>" . $rg[dispensario] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getProductosByInventario($nombreSelect, $rubro, $opciones = "", $adicional = array()) {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT inv.id producto,inv.descripcion FROM inv WHERE inv.rubro IN($rubro) AND inv.activo = 'Si';";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if (is_array($adicional) && count($adicional) > 0) {
            foreach ($adicional as $key => $value) {
                $html .= "<option value='$key'>$value</option>";
            }
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["producto"] . "'>" . $rg["producto"] . " | " . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getClientes($nombreSelect, $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT id cliente,CONCAT(id, ' | ', tipodepago, ' | ', nombre) descripcion FROM cli WHERE id >= 10 AND activo = 'Si';";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["cliente"] . "'>" . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getClientesByRubro($nombreSelect, $rubros = null, $adicional = null, $opciones = "") {
        $mysqli = getConnection();
        $selectClientes = "SELECT id, nombre, alias FROM cli WHERE TRUE ";
        if (is_array($rubros) && count($rubros) > 0) {
            $selectClientes .= " AND tipodepago IN (" . implode(",", $rubros) . ")";
        }
        $result = $mysqli->query($selectClientes) or error_log($mysqli->error . "\n" . $selectClientes);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if (is_array($adicional) && count($adicional) > 0) {
            foreach ($adicional as $key => $value) {
                $html .= "<option value='$key'>$value</option>";
            }
        } else {
            if ($adicional !== "") {
                $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
            }
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["id"] . "'>" . $rg["id"] . " | " . $rg["alias"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getIslaPosicion($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT isla_pos value FROM  man  WHERE activo = 'Si' AND inventario = 'Si'  GROUP BY isla_pos";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["value"] . "'>" . $rg["value"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getCombustibles($nombreSelect, $campo = "clavei", $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT $campo value, descripcion FROM com WHERE activo = 'Si'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["value"] . "'>" . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getCombustiblesStr($nombreSelect, $campo = "clavei", $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT $campo value, descripcion FROM com WHERE activo = 'Si' AND clave = '$nombreSelect'";
        error_log($selectPosiciones);
        $html = "";
        $result = $mysqli->query($selectPosiciones);
        while ($rg = $result->fetch_array()) {
            $html .= $rg["descripcion"];
        }
        echo $html;
    }

    public static function getTanques($nombreSelect, $campo = "tanque", $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT $campo value, producto FROM tanques WHERE estado = '1'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["value"] . "'>" . $rg["value"] . " | " . $rg["producto"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getEstado($nombreSelect, $adicional = "", $opciones = "") {
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        $html .= "<option value='0'>Inactivo</option>";
        $html .= "<option value='1'>Activo</option>";
        $html .= "</select>";
        echo $html;
    }

    public static function getDataFromCatalogoSatCv($nombreSelect, $clave, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT id, clave, descripcion FROM catalogos_sat_cv WHERE catalogo = '$clave'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["clave"] . "'>" . $rg["id"] . " | " . $rg["clave"] . " | " . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getDataPeriodicidad($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "select clave,descripcion from `cp_periodicidad` WHERE activo=1;";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["clave"] . "'>" . $rg["clave"] . " | " . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getClientesConsignacion($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "select id,nombre from `cli` WHERE tipodepago='Consignacion';";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["id"] . "'>" . $rg["id"] . " | " . $rg["nombre"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getCombustiblesId($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT id value, descripcion FROM com WHERE activo = 'Si'";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["value"] . "'>" . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getDataMeses($nombreSelect, $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "select clave,descripcion from `cp_meses` WHERE activo=1;";
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["clave"] . "'>" . $rg["clave"] . " | " . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

    public static function getProveedores($nombreSelect, $proveedorde = "", $adicional = "", $opciones = "") {
        $mysqli = getConnection();
        $selectPosiciones = "SELECT id value, nombre descripcion FROM prv WHERE activo = 'Si' ";
        if (!empty($proveedorde)) {
            $selectPosiciones .= " AND proveedorde IN ($proveedorde)";
        }
        $result = $mysqli->query($selectPosiciones);
        $html = "<select name='" . $nombreSelect . "' id='" . $nombreSelect . "'  $opciones>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'>" . $adicional . " </option>";
        }
        while ($rg = $result->fetch_array()) {
            $html .= "<option value='" . $rg["value"] . "'>" . $rg["descripcion"] . " </option>";
        }
        $html .= "</select>";
        echo $html;
    }

}
