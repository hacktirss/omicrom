<?php

class ComboboxComplementos {

    static function generate($comboID, $width = "350px") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id clave, nombre descripcion FROM complementos WHERE status = 'A'");

        $html = "<select style='width: $width' class='texto_tablas' name='$comboID' id='$comboID'><option value=''>ASIGNAR COMPLEMENTO</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value='" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxFormaDePago {

    /**
     * Regresa parte de html select con las opciones de cfdi33_c_fpago
     * @param string $comboID Nombre del select
     * @param string $width Ancho del select
     * @param string $aditional Poder agregar codigo dentro del select
     * @param string $tpago Tipo de pago del cliente a facturar
     * @param int $Vc Bandera ('CreditoRestringido') que valida si es que tiene la restricción a clientes de credito  
     * @return string tipo HTML 
     */
    static function generate($comboID, $width = "350px", $aditional = "", $tpago = "", $Vc = 0) {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_fpago WHERE status = 1");

        $html = "<select style='width: $width' class='texto_tablas' name='$comboID' id='$comboID' $aditional>";
        if ($tpago === "Credito" && $Vc == 1) {
            $html .= "<option value='99'>99 | Por definir</option>";
        } else {
            $html .= "<option value=''>SELECCIONE FORMA DE PAGO</option>";
            while (($rs = $qry->fetch_array())) {
                $html .= "<option value='" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . "</option>";
            }
            $html .= "<option value='98'>NA | No Aplica</option>";
        }
        echo $html . "</select>";
    }

}

class ComboboxMetodoDePago {

    /**
     * Regresa parte de html select con las opciones de cfdi33_c_mpago
     * @param string $comboID Nombre del select
     * @param string $width Ancho del select
     * @param string $aditional Poder agregar codigo dentro del select
     * @param string $tpago Tipo de pago del cliente a facturar
     * @param int $Vc Bandera ('CreditoRestringido') que valida si es que tiene la restricción a clientes de credito  
     * @return string tipo HTML 
     */
    static function generate($comboID, $width = "350px", $aditional = "", $tpago = "", $Vc = 0) {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_mpago WHERE status = 1");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $aditional>";
        if ($tpago === "Credito" && $Vc == 1) {
            $html .= "<option value = 'PPD'>PPD | Pago en parcialidades o diferido</option>";
        } else {
            while (($rs = $qry->fetch_array())) {
                $html .= "<option value = '" . $rs["clave"] . "' > " . $rs["clave"] . " | " . $rs["descripcion"] . "</option>";
            }
        }
        echo $html . "</select>

            ";
    }

}

class ComboboxUnidades {

    static function generate($comboID, $width = "350px") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_unidades WHERE status = 1");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE UNIDAD</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["nombre"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxDivison {

    static function generate($comboID, $tipo) {
        $mysqli = iconnect();

        $query = "SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '0' " . (empty($tipo) ? "" : " AND tipo = '$tipo'");
        $qry = $mysqli->query($query);

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE DIVISIÓN</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxGrupo {

    static function generate($comboID, $division) {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '$division'");

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE GRUPO</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxClase {

    static function generate($comboID, $grupo, $options = "") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '$grupo'");

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE CLASE</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxProductoServicio {

    static function generate($comboID, $clase) {
        $mysqli = iconnect();

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE CONCEPTO</option>";
        $html .= "<option value = '01010101'>No existe en el catálogo</option>";
        if ($clase != '') {
            $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_conceptos WHERE clave LIKE 'substr($clase, 0, 6)%'");

            while (($rs = $qry->fetch_array())) {
                $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["nombre"] . "</option>";
            }
        }

        echo $html . "</select>";
    }

}

class ComboboxCommonProductoServicio {

    static function generate($comboID, $width = "350px") {
        $mysqli = iconnect();

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE CONCEPTO</option>";
        $html .= "<option value = '01010101'>01010101 | No existe en el catálogo</option>";
        $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_conceptos WHERE status = '1'");

        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["nombre"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxTipoRelacion {

    static function generate($comboID, $width = "350px") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_trelacion WHERE status = 1");

        $html = "&nbsp;
            <select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID'>";
        $html .= "<option value = ''>SELECCIONE EL TIPO DE RELACI&Oacute;
            N</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

//ComboboxTipoRelacion

class ComboboxUsoCFDI {

    static function generate($comboID, $width = "350px") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_uso WHERE status = 1");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID'><option value = ''>SELECCIONE USO CFDI</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . str_replace(' ', '&nbsp;', $rs["clave"]) . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

    static function generateByTypeCli($comboID, $Tipo, $Extra = "") {
        $mysqli = iconnect();
        if ($Tipo == 12) {
            $sql = "SELECT clave, descripcion FROM cfdi33_c_uso WHERE status = 1 AND tipo IN (2, 3)";
        } else if ($Tipo >= 13) {
            $sql = "SELECT clave, descripcion FROM cfdi33_c_uso WHERE status = 1 AND tipo IN (1, 3)";
        }
        $qry = $mysqli->query($sql);

        $html = "<select style = 'width: 350px' class = 'texto_tablas' name = '$comboID' id = '$comboID' $Extra><option value = ''>SELECCIONE USO CFDI</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clave"] . "'>" . str_replace(' ', '&nbsp;', $rs["clave"]) . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxBancos {

    static function generate($comboID, $width = "350px", $aditional = "") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id, cuenta, banco FROM bancos WHERE TRUE AND rubro = 0 AND activo = 1 ORDER BY banco;
            ");

        $html = "&nbsp;
            <select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $aditional>";
        $html .= "<option value = ''>SELECCIONE BANCO</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . str_replace(' ', '&nbsp;', $rs["cuenta"]) . " | " . $rs["banco"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxActivo {

    static function generate($comboID, $width = "350px") {

        $html = "&nbsp;
            <select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID'>";
        $html .= "<option value = 'Si'>Activo</option>";
        $html .= "<option value = 'No'>Inactivo</option>";

        echo $html . "</select>";
    }

}

class ComboboxInventario {

    static function generate($comboID, $rubro, $width = "350px", $onChange = "", $default = "SELECCIONE UN PRODUCTO") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id, descripcion, clave_producto FROM inv WHERE inv.rubro IN ($rubro) AND inv.activo = 'Si' ORDER BY id;
            ");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange>";
        $html .= "<option value = '' selected = 'selected' disabled = ''>$default</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . str_replace(' ', '&nbsp;', $rs["id"]) . " | " . $rs["clave_producto"] . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

    static function generatePuntos($comboID, $rubro, $width = "350px", $onChange = "", $default = "SELECCIONE UN PRODUCTO") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id, descripcion, clave_producto,precio FROM inv WHERE inv.rubro IN ($rubro) AND inv.activo = 'Si' ORDER BY id;
            ");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange>";
        $html .= "<option value = '' selected = 'selected' disabled = ''>$default</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . str_replace(' ', '&nbsp;', $rs["id"]) . " | " . $rs["descripcion"] . " | Puntos :" . $rs["precio"] . " </option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxProveedor {

    static function generate($comboID, $rubro, $width = "350px", $onChange = "") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id, nombre, tipoProveedor FROM prv WHERE prv.proveedorde IN ($rubro) ORDER BY id;
            ");

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange><option value = ''>SELECCIONE UN PROVEEDOR</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . str_replace(' ', '&nbsp;', $rs["id"]) . " | " . $rs["nombre"] . " | " . $rs["tipoProveedor"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxCatalogoUniversal {

    static function generate($comboID, $rubro, $width = "350px", $onChange = "", $default = "SELECCIONE UNA OPCIÓN DEL CATALOGO") {
        $mysqli = iconnect();

        $sql = "
            SELECT id, llave clave, permiso, descripcion
            FROM permisos_cre
            WHERE TRUE AND padre > 0 AND estado = 1 AND catalogo = '" . $rubro . "'
            ORDER BY llave;
            ";
        $qry = $mysqli->query($sql);

        $html = "<select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange>";
        if ($default !== "") {
            $html .= "<option value = ''>$default</option>";
        }
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . " | " . $rs["permiso"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxTanques {

    static function generate($comboID, $width = "350px", $onChange = "") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT t.tanque, t.producto FROM tanques t ORDER BY t.tanque;
            ");

        $html = "&nbsp;
            <select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange><option value = ''>SELECCIONE UN TANQUE</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["tanque"] . "'>" . str_replace(' ', '&nbsp;', $rs["tanque"]) . " | " . $rs["producto"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxCombustibles {

    static function generate($comboID, $width = "350px", $onChange = "", $default = "SELECCIONE UN PRODUCTO", $class = "texto_tablas") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT com.clavei, com.descripcion FROM com WHERE com.activo = 'Si' ORDER BY com.clavei;
            ");

        $html = "<select style = 'width: $width' class = '$class' name = '$comboID' id = '$comboID' $onChange><option value = ''>$default</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clavei"] . "'>" . str_replace(' ', '&nbsp;', $rs["clavei"]) . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

    static function generateBusqueda($comboID, $width = "350px", $onChange = "", $default = "TODOS LOS PRODUCTOS", $class = "texto_tablas") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT com.clavei, com.descripcion FROM com WHERE com.activo = 'Si' ORDER BY com.clavei;
            ");

        $html = "<select style = 'width: $width' class = '$class' name = '$comboID' id = '$comboID' $onChange><option value = '*'>$default</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["clavei"] . "'>" . str_replace(' ', '&nbsp;', $rs["clavei"]) . " | " . $rs["descripcion"] . "</option>";
        }

        echo $html . "</select>";
    }

}

class ComboboxClientes {

    static function generate($comboID, $rubro, $width = "350px", $onChange = "", $default = "SELECCIONE UN CLIENTE") {
        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT id, nombre FROM cli WHERE cli.tipodepago IN ($rubro) ORDER BY id;
            ");

        $html = "&nbsp;
            <select style = 'width: $width' class = 'texto_tablas' name = '$comboID' id = '$comboID' $onChange>";
        $html .= "<option value = '' selected = 'selected' disabled = ''>$default</option>";
        while (($rs = $qry->fetch_array())) {
            $html .= "<option value = '" . $rs["id"] . "'>" . str_replace(' ', '&nbsp;', $rs["id"]) . " | " . $rs["nombre"] . "</option>";
        }

        echo $html . "</select>";
    }

}

/* Nuevas funciones */

class CatalogosSelectores {

    static function getMonedas() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_moneda WHERE status = 1");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getEmbalaje() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cp_embalaje");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getTipoPermiso() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cp_tipo_permiso");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getRemolque() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cp_tipo_rem");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getConfiguracionVehicular() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cp_config_autotransp");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getBienesTransporte() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, nombre descripcion FROM cfdi33_c_conceptos WHERE status = 1");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getOperadores() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id clave, nombre descripcion FROM catalogo_operadores");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function generateClaveCP($comboID, $StyleAdd = "") {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT * FROM cfdi33_c_conceptos where clave in
            (78101800);
            ");
        $html = "<select class = \"form-control\" style=\"font-size: 11px; font-family: monospace;$StyleAdd\" id=\"" . $comboID . "\" name=\"" . $comboID . "\"><option value=\"000000\">SELECCIONE CLAVE CP</option>";
        while (($rs = $qry->fetch_array())) {

            $html = $html . "<option value=\"" . $rs['clave'] . "\">" . $rs['clave'] . " .- " . $rs['nombre'] . "</option>";
        }
        $html = $html . "</select>";

        echo $html;
    }

    static function getVehiculos() {

        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id clave, descripcion FROM catalogo_vehiculos");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getDireccion($TablaOrigen = "'D','P','C'") {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id,descripcion FROM catalogo_direcciones WHERE tabla_origen in ($TablaOrigen);");

        while (($rs = $qry->fetch_array())) {
            $array[$rs["id"]] = $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getLocalidad($Estado) {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id,localidad clave, descripcion FROM cp_localidad WHERE estado = '" . $Estado . "'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["id"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getColonia($CP) {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT colonia,codigo_postal, nombre FROM cp_colonia WHERE codigo_postal = '" . $CP . "'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["colonia"]] = $rs["codigo_postal"] . " | " . $rs["nombre"];
        }
        $mysqli->close();
        return $array;
    }

    static function getEstado() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT * FROM cp_estados WHERE c_pais='MEX'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["c_estado"]] = $rs["c_estado"] . " | " . $rs["nombre"];
        }
        $mysqli->close();
        return $array;
    }

    static function getMunicipio($Estado) {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id clave, descripcion FROM cp_municipio WHERE estado = '" . $Estado . "'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getFormasDePago() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_fpago WHERE status = 1");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getTipos_Cliente() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT concepto clave, descripcion FROM tipos_cliente WHERE estado = 1");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getDatosClaveInstalacion() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id,clave FROM catalogos_sat_cv WHERE catalogo='CLAVES_INSTALACION'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"];
        }
        $mysqli->close();
        return $array;
    }

    static function getDatosCaracterSat() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id,clave FROM catalogos_sat_cv WHERE catalogo='CLAVES_CARACTER'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"];
        }
        $mysqli->close();
        return $array;
    }

    static function getDatosModalidadPermiso() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT id,clave FROM catalogos_sat_cv WHERE catalogo='CLAVES_PERMISO'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"];
        }
        $mysqli->close();
        return $array;
    }

    static function getUnidades() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, nombre descripcion FROM cfdi33_c_unidades WHERE status = 1");
        $array[""] = "SELECCIONE UNIDAD";
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getProductoServicio() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, nombre descripcion FROM cfdi33_c_conceptos WHERE status = '1'");
        $array[""] = "SELECCIONE CONCEPTO";
        $array["01010101"] = "01010101 | No existe en el catálogo";
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getCfdiRegimenes() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_regimenes WHERE status = '1'");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getMotivos_Cancelacion() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cp_motivo_cancelacion WHERE activo = 1");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

    static function getFiguraTrans() {
        $mysqli = iconnect();
        $array = array();
        $qry = $mysqli->query("SELECT clave, descripcion FROM omicrom.cp_figura_transporte");
        while (($rs = $qry->fetch_array())) {
            $array[$rs["clave"]] = $rs["clave"] . " | " . $rs["descripcion"];
        }
        $mysqli->close();
        return $array;
    }

}
