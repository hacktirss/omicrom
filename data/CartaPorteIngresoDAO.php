<?php

/**
 * Description of CartaPorteDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since dic 2021
 */

namespace com\detisa\omicrom {

    include_once ("mysqlUtils.php");
    include_once ("FunctionsDAO.php");
    include_once ("CartaPorteVO.php");
    require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
    require_once ('com/softcoatl/cfdi/complemento/cartaporte/CartaPorte20.php');

    use \com\softcoatl\cfdi\v40\schema as cfdi40;
    use com\softcoatl\cfdi\complemento as complementos;

    class CartaPorteIngresoDAO {

        const RESPONSE_VALID = "OK";
        const TABLA = "carta_porte";

        private $conn;

        function __construct($folio, $origen) {
            $this->folio = $folio;
            $this->origen = $origen;
            $this->comprobante = new cfdi40\Comprobante40();
            $this->conn = getConnection();

            $this->comprobante();
            $this->emisor();
            $this->receptor();
            $this->conceptos();
            $this->impuestos();
            $this->cartaPorte();
            //error_log(print_r($this->comprobante, true));
        }

        function __destruct() {
            $this->conn->close();
        }

        function getComprobante() {
            return $this->comprobante;
        }

        function getFolio() {
            return $this->folio;
        }

        function setFolio($folio) {
            $this->folio = $folio;
        }

        function getOrigen($origen) {
            return $this->origen;
        }

        function setOrigen($origen) {
            $this->origen = $origen;
        }

        /**
         * Recupera la información relativa a la factura.
         * Crea el objeto Comprobante
         */
        private function comprobante() {
            $sql = "SELECT id.preciob,(id.preciob/1.16) subt,id.iva,id.ieps,cp.moneda,DATE_FORMAT(i.fecha, '%Y-%m-%dT%H:%i:%s') fechat,i.metodopago metodoPago,
                        i.formadepago formaPago,i.usocfdi usoCfdi,i.folio,i.serie FROM ingresos i LEFT JOIN ingresos_detalle id ON i.id = id.id 
                        LEFT JOIN carta_porte cp ON id.id=cp.id_origen  WHERE i.id = " . $this->folio . " AND cp.origen='CPI' AND id.producto=0;";

            error_log("SQL 1 " . $sql);
            $cia = "SELECT codigo FROM cia;";
            $Cia = $this->conn->query($cia)->fetch_assoc();
            if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {

                $ImpuestoT = $rs["preciob"] - $this->comprobante->getSubTotal();
                $Imps = $ImpuestoT - ($this->comprobante->getSubTotal() * ($rs["ieps"] / 100));
                $this->comprobante->setSubTotal(number_format($rs["subt"], 2, ".", ""));
                $this->comprobante->setTotal(number_format($rs["preciob"], 2, ".", ""));
                $this->comprobante->setFecha($rs['fechat']);
                $this->comprobante->setTipoDeComprobante("I");
                $this->comprobante->setVersion("4.0");
                $this->comprobante->setFolio($rs["folio"]);
                $this->comprobante->setSerie($rs["serie"]);
                $this->comprobante->setMetodoPago($rs["metodoPago"]);
                $this->comprobante->setFormaPago($rs["formaPago"]);
                $this->comprobante->setMoneda($rs["moneda"]);
                $this->comprobante->setExportacion("01");
                $this->comprobante->setLugarExpedicion($Cia["codigo"]);
            }
        }

        /**
         * Recupera los datos de la estación de servicio.
         * Crea el nodo Emisor.
         */
        private function emisor() {

            /* @var $emisor cfdi33\Comprobante\Emisor */
            $emisor = new cfdi40\Comprobante40\Emisor();
            $sql = "SELECT REGEXP_REPLACE(upper(cia), '([, ]{1,4})?[S][.]?[A][.]?[ ]{1,3}[DE]{2}[ ]{1,3}([C][.]?[V][.]?|[R][.]?[L][.]?)$','') Nombre, rfc Rfc, clave_regimen RegimenFiscal FROM cia";

            if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {

                $emisor->setNombre($rs['Nombre']);
                $emisor->setRfc($rs['Rfc']);
                $emisor->setRegimenFiscal($rs['RegimenFiscal']);
            }
            $this->comprobante->setEmisor($emisor);
        }

        /**
         * Recupera los datos del receptor del CFDI.
         * Crea el nodo Receptor.
         */
        private function receptor() {

            /* @var $emisor cfdi33\Comprobante\Receptor */
            $receptor = new cfdi40\Comprobante40\Receptor();
            $sql = "SELECT cli.codigo,cli.regimenfiscal,cli.nombre,cli.rfc,usoCfdi FROM ingresos i LEFT JOIN cli ON i.id_cli=cli.id WHERE i.id = " . $this->folio;

            if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
                $receptor->setDomicilioFiscalReceptor($rs["codigo"]);
                $receptor->setRegimenFiscalReceptor($rs["regimenfiscal"]);
                $receptor->setNombre($rs['nombre']);
                $receptor->setRfc($rs['rfc']);
                $receptor->setUsoCFDI($rs["usoCfdi"]);
                //$receptor->setObservaciones("CP01");
            }
            $this->comprobante->setReceptor($receptor);
        }

        /**
         * Recupera los conceptos asociados a la factura.
         * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
         */
        private function conceptos() {

            $conceptos = new cfdi40\Comprobante40\Conceptos();
            $subTotal = 0.00;

            $sql = "SELECT *,ROUND( id.precio * ( 1 / 100 ) * id.iva, 2 ) tax_iva,"
                    . "CAST( id.iva AS DECIMAL( 10, 6 ) ) factoriva,preciob,"
                    . "ROUND( id.cantidad * id.precio * id.iva, 2 ) tax_iva FROM ingresos_detalle id "
                    . "WHERE id.id=" . $this->folio . " AND id.producto = 0;";

            $Sql2 = "SELECT ClaveProdServ claveProductoServicio,nombre FROM ingresos t LEFT JOIN cfdi33_c_conceptos cc ON "
                    . "t.ClaveProdServ = cc.clave where id =" . $this->folio . "";
            error_log($Sql2);
            if ($query0 = $this->conn->query($Sql2)) {
                $rs0 = $query0->fetch_assoc();
            }
            $total = 0.00;

            if (($query = $this->conn->query($sql))) {

                while (($rs = $query->fetch_assoc())) {
                    $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                    $concepto->setClaveProdServ($rs0["claveProductoServicio"]);
                    $concepto->setClaveUnidad("E48");
                    $concepto->setDescripcion($rs0['nombre']);
                    $concepto->setImporte(number_format($rs['preciob'] / (1 + $rs["iva"]), 2, '.', ''));
                    $concepto->setCantidad(number_format($rs['cantidad'], 2, '.', ''));
                    $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                    $concepto->setValorUnitario(number_format($rs['preciob'] / (1 + $rs["iva"]), 2, '.', ''));

                    $concepto->setObjetoImp("02");
                    $subTotal += $rs['Importe'];
                    //$concepto->setImpuestos($impuestos);
                    $Impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                    $Traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();
                    $Retenciones = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones();
                    $Pass = false;
                    if ($rs["tax_iva"] > 0) {
                        $Impuesto = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                        $Impuesto->setBase(number_format($rs['preciob'] / (1 + $rs["iva"]), 2, '.', ''));
                        $Impuesto->setImpuesto("002");
                        $Impuesto->setTasaOCuota($rs["factoriva"]);
                        $Impuesto->setTipoFactor("Tasa");
                        $Impuesto->setImporte(str_replace(",", "", number_format($Impuesto->getBase() * 0.16, 2)));
                        $Pass = true;
                        $Traslados->addTraslado($Impuesto);
                        $Impuestos->setTraslados($Traslados);
                        $concepto->setImpuestos($Impuestos);
                    }

                    if ($rs["ieps"] > 0.5) {
                        $Retencion = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
                        $Retencion->setBase($Impuesto->getBase());
                        $Retencion->setImpuesto("002");
                        $Retencion->setTipoFactor("Tasa");
                        $Importe = ($rs["ieps"] * $Impuesto->getBase()) / 100;
                        $Retencion->setImporte(number_format($Importe, 2, '.', ''));
                        $Tasa = $rs["ieps"] / 100;
                        $Retencion->setTasaOCuota(number_format($Tasa, 6, '.', ''));
                        $Retenciones->addRetencion($Retencion);
                        $Impuestos->setRetenciones($Retenciones);
                        $concepto->setImpuestos($Impuestos);
                        $Rt = 1;
                    }

                    $conceptos->addConcepto($concepto);
                }
                if ($Pass) {
                    $TT = $Rt == 1 ? $Impuesto->getBase() + $Impuesto->getImporte() - $Retencion->getImporte() : $Impuesto->getBase() + $Impuesto->getImporte();
                    $this->comprobante->getTotal() <> $TT ? $this->comprobante->setTotal($TT) : true;

//$this->comprobante->setSubTotal(number_format($subTotal, 2, '.', ''));
                    $this->comprobante->setConceptos($conceptos);
                }
            }
        }

        function cartaPorte() {
            $cartaPorte = new complementos\cartaporte\CartaPorte20();
            $Ubicaciones = new complementos\cartaporte\CartaPorte20\Ubicaciones();
            $cartaPorteU = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion();
            $complemento = new cfdi40\Comprobante40\Complemento();

            $CantidadTotal = 0;

            $sql = "SELECT carta_porte.*,catalogo_vehiculos.permiso_sct,catalogo_vehiculos.numero_sct,catalogo_vehiculos.nombre_aseguradora,"
                    . "catalogo_vehiculos.numero_seguro,catalogo_vehiculos.conf_vehicular,catalogo_vehiculos.placa,catalogo_vehiculos.anio_modelo,"
                    . "catalogo_operadores.rfc_operador,catalogo_operadores.nombre,catalogo_operadores.num_licencia,"
                    . "catalogo_direcciones.colonia coloniaD, catalogo_direcciones.localidad localidadD, catalogo_direcciones.municipio municipioD,"
                    . "catalogo_direcciones.estado estadoD, catalogo_direcciones.codigo_postal codigo_postalD , catalogo_direcciones.calle calleD,"
                    . "catalogo_direcciones.num_exterior numexteriorD, catalogo_direcciones.num_interior numinteriorD,catalogo_operadores.nombre nombreD "
                    . "FROM carta_porte "
                    . "LEFT JOIN carta_porte_destino cpd ON carta_porte.id=cpd.id_carta_porte_fk "
                    . "LEFT JOIN catalogo_vehiculos ON carta_porte.id_vehiculo = catalogo_vehiculos.id "
                    . "LEFT JOIN catalogo_operadores ON carta_porte.id_operador = catalogo_operadores.id "
                    . "LEFT JOIN catalogo_direcciones ON cpd.id_destino_fk = catalogo_direcciones.id "
                    . "WHERE catalogo_direcciones.tabla_origen in ('D','P','C') AND carta_porte.id = (SELECT id FROM carta_porte WHERE origen = '"
                    . $this->origen . "' AND id_origen = " . $this->folio . " AND origen='CPI') LIMIT 1;";
            error_log($sql);

            $cia = "SELECT * FROM cia;";
            $cCia = $this->conn->query($cia);
            $Cia = $cCia->fetch_assoc();
            $factura = "SELECT * FROM fc WHERE id = " . $this->folio;
            $rFactura = $this->conn->query($factura);
            $fc = $rFactura->fetch_assoc();
            $cCia = "SELECT * FROM cia ";
            $rCia = $this->conn->query($cCia);
            $rscia = $rCia->fetch_assoc();

            if ($query = $this->conn->query($sql)) {
                if ($rs = $query->fetch_assoc()) {
                    $cartaPorte->setVersion("2.0");
                    $cartaPorte->setTranspInternac("No");
                    //$cartaPorte->setViaEntradaSalida("01"); Optional
                    $Distancia = "SELECT distancia FROM carta_porte_destino "
                            . "WHERE id_carta_porte_fk = " . $rs["id"] . " AND tipo = 'Destino' AND origen='ING';";
                    if ($Dist = $this->conn->query($Distancia)) {
                        $TotalRecorrido = 0;
                        while ($cP = $Dist->fetch_assoc()) {
                            $TotalRecorrido = $TotalRecorrido + $cP["distancia"];
                        }
                    }
                    error_log("TOTAL DIST REC " . $TotalRecorrido);
                    $cartaPorte->setTotalDistRec($TotalRecorrido);
                    /* Ubicacion Origen */
                    $Origen = "SELECT *,carta_porte_destino.id idcpd,cd.municipio muncd,cd.estado estcd,cd.colonia colcd FROM carta_porte_destino LEFT JOIN catalogo_direcciones cd ON
                      carta_porte_destino.id_destino_fk = cd.id LEFT JOIN prv 
                      ON prv.id =cd.id_origen WHERE id_carta_porte_fk = " . $rs["id"] . " AND cd.tabla_origen='P'
                      AND carta_porte_destino.tipo = 'Origen' AND origen='ING';";
                    if ($RsOrg = $this->conn->query($Origen)) {
                        $RsO = $RsOrg->fetch_assoc();
                        $cartaPorteU->setTipoUbicacion("Origen");
                        $idUbi = substr(str_repeat(0, 6) . $rs["id"], - 6);
                        //$cartaPorteU->setIdUbicacion("OR" . $idUbi);
                        $cartaPorteU->setRfcRemitenteDestinatario($RsO["rfc"]);
                        $cartaPorteU->setFechaHoraSalidaLlegada($rs["fechaHoraSalidaLlegada"] . ":01");
                        $cartaPorteDom = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion\Domicilio();
                        $cartaPorteDom->setEstado($RsO["estcd"]);
                        $cartaPorteDom->setPais("MEX");
                        $cartaPorteDom->setCodigoPostal($RsO["codigo_postal"]);
                        $cartaPorteDom->setNumeroExterior($RsO["num_exterior"]);
                        $cartaPorteDom->setNumeroInterior($RsO["num_interior"]);
                        $cartaPorteDom->setCalle($RsO["calle"]);
                        $cartaPorteDom->setColonia($RsO["colcd"]);
                        //$cartaPorteDom->setLocalidad($rscia["localidadClv"]);
                        $cartaPorteDom->setReferencia($RsO["descripcion"]);
                        $cartaPorteDom->setMunicipio($RsO["muncd"]);
//                        $cartaPorteDom->setEstado($rscia["estadoClv"]);
                        $cartaPorteDom->setPais("MEX");

                        $cartaPorteU->setDomicilio($cartaPorteDom);
                        $Ubicaciones->addUbicacion($cartaPorteU);
                    } /* Ubicacion Destino */
                    $CartaPorte = "SELECT *,carta_porte_destino.id idcpd,cd.colonia coloniacd,cd.municipio muncd,cd.estado estcd FROM carta_porte_destino LEFT JOIN catalogo_direcciones cd ON "
                            . "carta_porte_destino.id_destino_fk = cd.id LEFT JOIN cli ON cli.id = cd.id_origen "
                            . "WHERE id_carta_porte_fk = " . $rs["id"] . " AND tipo = 'Destino' AND origen='ING';";
                    error_log("NV __________" . $CartaPorte);
                    if ($CartaP = $this->conn->query($CartaPorte)) {
                        $i = 0;

                        while ($cP = $CartaP->fetch_assoc()) {
                            $cartaPorteUD = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion();
                            $cartaPorteDomD = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion\Domicilio();
                            error_log("THIS _>>>>>>>>>>>>>>>>>" . print_r($cP, true));
                            $cartaPorteDomD->setEstado($cP["estcd"]);
                            $cartaPorteDomD->setMunicipio($cP["muncd"]);
                            $cartaPorteDomD->setReferencia($cP["descripcion"]);
//                            $cartaPorteDomD->setColonia($cP["coloniacd"]);
                            $cartaPorteDomD->setNumeroExterior($cP["num_exterior"]);
                            $cartaPorteDomD->setNumeroInterior($cP["num_interior"]);
                            $cartaPorteDomD->setCalle($cP["calle"]);
                            $cartaPorteDomD->setPais("MEX");
                            $cartaPorteDomD->setCodigoPostal($cP["codigo_postal"]);
                            $cartaPorteUD->setDomicilio($cartaPorteDomD);

                            $idUbi = "DE" . substr(str_repeat(0, 6) . $cP["idcpd"], - 6);
                            $cartaPorteUD->setTipoUbicacion("Destino");
                            $cartaPorteUD->setRfcRemitenteDestinatario($cP["rfc"]);
                            $cartaPorteUD->setFechaHoraSalidaLlegada($cP["fecha"] . ":00");
                            //$cartaPorteUD->setIdUbicacion($idUbi);
                            $cartaPorteUD->setDistanciaRecorrida($cP["distancia"]);

                            $Ubicaciones->addUbicacion($cartaPorteUD);
                            if ($i > 0) {
                                $Val = true;
                            }
                            $i++;
                        }
                        $cartaPorte->setUbicaciones($Ubicaciones);
                    }

                    /* Mercancia */
                    $cartaPorteMercancias = new complementos\cartaporte\CartaPorte20\Mercancias();

                    $cartaPorteMercancias->setUnidadPeso("X1A");
                    $facturad = "SELECT td.cantidad,inv.descripcion,inv.inv_cproducto,td.importe,com.densidad_producto,inv.umedida,"
                            . "inv.inv_cproducto,inv.inv_cunidad "
                            . "FROM ingresos_detalle td "
                            . "LEFT JOIN inv ON td.producto = inv.id "
                            . "LEFT JOIN com ON inv.descripcion = com.descripcion "
                            . "WHERE td.id = " . $this->folio . " AND td.producto > 0";

                    $rFacturad = $this->conn->query($facturad);
                    $Mercancias = 0;
                    while ($fcd = $rFacturad->fetch_assoc()) {
                        $cartaPorteMercancia = new complementos\cartaporte\CartaPorte20\Mercancias\Mercancia();
                        $cartaPorteMercanciaCT = new complementos\cartaporte\CartaPorte20\Mercancias\Mercancia\CantidadTransporta();
                        $cartaPorteMercanciaDM = new complementos\cartaporte\CartaPorte20\Mercancias\Mercancia\DetalleMercancia();

                        $cartaPorteMercancia->setMaterialPeligroso("Sí");
                        $cartaPorteMercancia->setCveMaterialPeligroso(3475);
                        $cartaPorteMercancia->setValorMercancia($fcd["importe"]);
//                        $cartaPorteMercancia->setDescripEmbalaje($enbalajeDesc["descripcion"]);
                        $cartaPorteMercancia->setDescripEmbalaje("Embalaje");
                        $cartaPorteMercancia->setBienesTransp($fcd["inv_cproducto"]);
                        $cartaPorteMercancia->setCantidad($fcd["cantidad"]);
                        $cartaPorteMercancia->setDescripcion($fcd["descripcion"]);
                        $cartaPorteMercancia->setClaveUnidad(strtoupper($fcd["inv_cunidad"]));
                        $cartaPorteMercancia->setEmbalaje($rs["embalaje"]);
                        $CantidadTotal = $CantidadTotal + ($fcd["cantidad"] * ($fcd["densidad_producto"] / 1000));
                        $cartaPorteMercancia->setPesoEnKg(($fcd["cantidad"] * ($fcd["densidad_producto"] / 1000)));
                        $cartaPorteMercancia->setMoneda("XXX");

                        $cartaPorteMercanciaCT->setCantidad($fcd["cantidad"]);
                        $cartaPorteMercanciaCT->setIdOrigen($cartaPorteU->getIdUbicacion());
                        $cartaPorteMercanciaCT->setIdDestino($idUbi);

                        /* Datos faltantes */
                        $cartaPorteMercanciaDM->setUnidadPesoMerc("");
                        $cartaPorteMercanciaDM->setPesoBruto("");
                        $cartaPorteMercanciaDM->setPesoNeto("");
                        $cartaPorteMercanciaDM->setPesoTara("");

                        $cartaPorteMercancia->setCantidadTransporta($cartaPorteMercanciaCT);
                        $cartaPorteMercancia->setDetalleMercancia($cartaPorteMercanciaDM);
                        $cartaPorteMercancias->addMercancia($cartaPorteMercancia);
                        $Mercancias++;
                    }
                    error_log("Contamos mercancias : " . $Mercancias);
                    $cartaPorteMercancias->setNumTotalMercancias($Mercancias);
                    $cartaPorteMercancias->setPesoBrutoTotal($CantidadTotal);

                    /* Autotransporte federal */
                    $autoTransporte = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte();
                    $autoTransporte->setPermSCT($rs["permiso_sct"]);
                    $autoTransporte->setNumPermisoSCT($rs["numero_sct"]);
                    $cartaPorteMercancias->setAutotransporte($autoTransporte);

                    /* Identificacion vehicular */
                    $autoTransporteVehicular = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte\IdentificacionVehicular();
                    $autoTransporteVehicular->setConfigVehicular($rs["conf_vehicular"]);
                    $autoTransporteVehicular->setPlacaVM($rs["placa"]);
                    $autoTransporteVehicular->setAnioModeloVM($rs["anio_modelo"]);
                    $autoTransporte->setIdentificacionVehicular($autoTransporteVehicular);
                    $cartaPorteMercancias->setAutotransporte($autoTransporte);

                    /* Seguros */
                    $autoTransporteSeguros = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte\Seguros();
                    $autoTransporteSeguros->setAseguraRespCivil($rs["nombre_aseguradora"]);
                    $autoTransporteSeguros->setPolizaRespCivil($rs["numero_seguro"]);
                    if ($cartaPorteMercancia->getMaterialPeligroso() === "Sí") {
                        $autoTransporteSeguros->setAseguraMedAmbiente($rs["nombre_aseguradora"]); //Aseguradora ambientalista
                        $autoTransporteSeguros->setPolizaMedAmbiente($rs["numero_seguro"]);
                    }
                    $autoTransporte->setSeguros($autoTransporteSeguros);
                    $cartaPorteMercancias->setAutotransporte($autoTransporte);
                    $cartaPorte->setMercancias($cartaPorteMercancias);
                    /* Remolques */

                    $autoTransporteRemolques = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte\Remolques();
                    $Remolques = "SELECT * FROM omicrom.carta_porte_remolques where id_carta_porte_fk = (select id FROM carta_porte WHERE id_origen=" . $this->folio . ");";

                    if ($rRem = $this->conn->query($Remolques)) {
                        error_log("REEEEEM " . print_r($rRem, true));
                        while ($rmRs = $rRem->fetch_assoc()) {
                            $autoTransporteRemolquesRem = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte\Remolques\Remolque();
                            $autoTransporteRemolquesRem->setPlaca($rmRs["placas"]);
                            $autoTransporteRemolquesRem->setSubTipoRem($rmRs["SubTipoRem"]);
                            $autoTransporteRemolques->addRemolque($autoTransporteRemolquesRem);
                        }
                        $autoTransporte->setRemolques($autoTransporteRemolques);
                    }
//            $autoTransporteRemolques = new cfdi33\Comprobante\complemento\CartaPorte20\Mercancias\Autotransporte\Remolques();
//            $autoTransporteRemolquesRem = new cfdi33\Comprobante\complemento\CartaPorte20\Mercancias\Autotransporte\Remolques\Remolque();
//            //$autoTransporteRemolquesRem->setSubTipoRem("");
//            $autoTransporteRemolquesRem->setPlaca("ASDF");
//
//            $autoTransporteRemolques->setRemolque($autoTransporteRemolquesRem);
//            $autoTransporte->setRemolques($autoTransporteRemolques);

                    /* Operador */
                    $FiguraTransporte = new complementos\cartaporte\CartaPorte20\FiguraTransporte();
                    $TiposFigura = new complementos\cartaporte\CartaPorte20\FigurasTransporte\TiposFigura();
                    $TiposFiguraDomicilio = new complementos\cartaporte\CartaPorte20\FigurasTransporte\TiposFigura\Domicilio();
                    $TiposFiguraPartesT = new complementos\cartaporte\CartaPorte20\FigurasTransporte\TiposFigura\PartesTransporte();

                    //$TiposFiguraPartesT->setParteTransporte("");
                    $TiposFigura->setRfcFigura($rs["rfc_operador"]);
                    $TiposFigura->setTipoFigura("01"); //Que tipo es?
                    $TiposFigura->setNumLicencia($rs["num_licencia"]);
                    $TiposFigura->setNombreFigura($rs["nombreD"]);

                    $TiposFiguraDomicilio->setEstado($Cia["estado"]);
                    $TiposFiguraDomicilio->setPais("México");
                    $TiposFiguraDomicilio->setCodigoPostal($Cia["codigo"]);
                    $TiposFiguraDomicilio->setCalle($rs["calleD"]);
                    $TiposFiguraDomicilio->setNumeroExterior($rs["numexteriorD"]);
                    $TiposFiguraDomicilio->setNumeroInterior($rs["numinteriorD"]);
                    $TiposFiguraDomicilio->setColonia($rs["coloniaD"]);
                    //$TiposFiguraDomicilio->setLocalidad($rs["localidadD"]);
                    $TiposFiguraDomicilio->setReferencia($referencia);
                    $TiposFiguraDomicilio->setMunicipio($rs["municipioD"]);
                    $TiposFiguraDomicilio->setEstado($rs["estadoD"]);
                    $TiposFiguraDomicilio->setPais("MEX");
                    $TiposFiguraDomicilio->setCodigoPostal($rs["codigo_postalD"]);
                    $TiposFigura->setDomicilio($TiposFiguraDomicilio);
                    $FiguraTransporte->addTiposFigura($TiposFigura);
                    $cartaPorte->setFiguraTransporte($FiguraTransporte);
                    //$TiposFigura->setPartesTransporte($TiposFiguraPartesT);
                    //$FiguraTransporte->addTiposFigura($TiposFigura);
                    //$cartaPorte->setFiguraTransporte($FiguraTransporte);
                }
            }
            $complemento->addAny($cartaPorte);
            $this->comprobante->setComplemento($complemento);
            //$this->comprobante->addComplemento($cartaPorte);
        }

        protected function impuestos() {

            $trasladados = array();
            $retenidos = array();

            $total_traslado = 0.00;
            $total_retencion = 0.00;

            $sqlIVA = "SELECT *,ROUND( cantidad * id.precio * ( 1 / 100 ) * id.iva, 2 ) tax_iva,"
                    . "CAST( id.iva AS DECIMAL( 10, 6 ) ) factoriva,producto,"
                    . "ROUND( id.cantidad * id.precio * id.iva, 2 ) tax_iva FROM ingresos_detalle id "
                    . "WHERE id.id=" . $this->folio . " AND id.producto = 0;";

            $Impuestos = new cfdi40\Comprobante40\Impuestos();
            $ImpuestosT = new cfdi40\Comprobante40\Impuestos\Traslados();
            $ImpuestosTd = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
            $RetencionesT = new cfdi40\Comprobante40\Impuestos\Retenciones();
            $RetencionesTd = new cfdi40\Comprobante40\Impuestos\Retenciones\Retencion();
            if (($query = $this->conn->query($sqlIVA))) {
                while (($rs = $query->fetch_assoc())) {
                    if ($rs["ieps"] > 0) {
                        $RetencionesTd->setImpuesto("002");
                        $RetencionesTd->setImporte(number_format($rs["precio"] * ($rs["ieps"] / 100), 2, '.', ''));
                        $RetencionesT->addRetencion($RetencionesTd);
                        $Impuestos->setRetenciones($RetencionesT);
                        $Impuestos->setTotalImpuestosRetenidos($RetencionesTd->getImporte());
                    }

                    $ImpuestosTd->setImpuesto("002");
                    $ImpuestosTd->setTasaOCuota($rs["factoriva"]);
                    $ImpuestosTd->setBase(number_format($rs['preciob'] / 1.16, 2, '.', ''));
                    $ImpuestosTd->setImporte(number_format($ImpuestosTd->getBase() * 0.16, 2, '.', ''));
                    $ImpuestosTd->setTipoFactor("Tasa");
                    $ImpuestosT->addTraslado($ImpuestosTd);
                    $Impuestos->setTraslados($ImpuestosT);
                    $Impuestos->setTotalImpuestosTrasladados($ImpuestosTd->getImporte());
                }
            }

            $this->comprobante->setImpuestos($Impuestos);
            return $this;
        }

        /**
         * 
         * @param \CartaPorteVO $objectVO
         * @return int Nuevo identificador generado
         */
        public function create($objectVO) {
            $id = -1;
            $sql = "INSERT INTO " . self::TABLA . " ("
                    . "id_origen,"
                    . "origen,"
                    . "transpInternac,"
                    . "rfcRemitenteDestinatario,"
                    . "fechaHoraSalidaLlegada,"
                    . "moneda,"
                    . "embalaje,"
                    . "idOrigen,"
                    . "idDestino,"
                    . "id_operador, "
                    . "id_vehiculo, "
                    . "id_direccion"
                    . ") "
                    . "VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

            if (($ps = $this->conn->prepare($sql))) {
                $ps->bind_param("ssssssssssss",
                        $objectVO->getId_origen(),
                        $objectVO->getOrigen(),
                        $objectVO->getTranspInternac(),
                        $objectVO->getRfcRemitenteDestinatario(),
                        $objectVO->getFechaHoraSalidaLlegada(),
                        $objectVO->getMoneda(),
                        $objectVO->getEmbalaje(),
                        $objectVO->getIdOrigen(),
                        $objectVO->getIdDestino(),
                        $objectVO->getId_operador(),
                        $objectVO->getId_vehiculo(),
                        $objectVO->getId_direccion()
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
         * @return \CartaPorteVO
         */
        public function fillObject($rs) {
            $objectVO = new CartaPorteVO();
            if (is_array($rs)) {
                $objectVO->setId($rs["id"]);
                $objectVO->setId_origen($rs["id_origen"]);
                $objectVO->setOrigen($rs["origen"]);
                $objectVO->setTranspInternac($rs["transpInternac"]);
                $objectVO->setRfcRemitenteDestinatario($rs["rfcRemitenteDestinatario"]);
                $objectVO->setFechaHoraSalidaLlegada($rs["fechaHoraSalidaLlegada"]);
                $objectVO->setMoneda($rs["moneda"]);
                $objectVO->setEmbalaje($rs["embalaje"]);
                $objectVO->setIdOrigen($rs["idOrigen"]);
                $objectVO->setIdDestino($rs["idDestino"]);
                $objectVO->setId_operador($rs["id_operador"]);
                $objectVO->setId_vehiculo($rs["id_vehiculo"]);
                $objectVO->setId_direccion($rs["id_direccion"]);
            }
            return $objectVO;
        }

        /**
         * 
         * @param string $sql Consulta SQL
         * @return array Arreglo de objetos \CartaPorteVO
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
         * @return \CartaPorteVO
         */
        public function retrieve($idObjectVO, $field = "id") {
            $objectVO = new CartaPorteVO();
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
         * @param \CartaPorteVO $objectVO
         * @return boolean Si la operación fue exitosa devolvera TRUE
         */
        public function update($objectVO) {
            $sql = "UPDATE " . self::TABLA . " SET "
                    . "id_origen = ?, "
                    . "origen = ?, "
                    . "transpInternac = ?, "
                    . "rfcRemitenteDestinatario = ?, "
                    . "fechaHoraSalidaLlegada = ?, "
                    . "moneda = ?, "
                    . "embalaje = ?, "
                    . "idOrigen = ?, "
                    . "idDestino = ?, "
                    . "id_operador = ?, "
                    . "id_vehiculo = ?, "
                    . "id_direccion = ? "
                    . "WHERE id = ? ";
            if (($ps = $this->conn->prepare($sql))) {
                $ps->bind_param("ssssssssssssi",
                        $objectVO->getId_origen(),
                        $objectVO->getOrigen(),
                        $objectVO->getTranspInternac(),
                        $objectVO->getRfcRemitenteDestinatario(),
                        $objectVO->getFechaHoraSalidaLlegada(),
                        $objectVO->getMoneda(),
                        $objectVO->getEmbalaje(),
                        $objectVO->getIdOrigen(),
                        $objectVO->getIdDestino(),
                        $objectVO->getId_operador(),
                        $objectVO->getId_vehiculo(),
                        $objectVO->getId_direccion(),
                        $objectVO->getId()
                );
                return $ps->execute();
            }
            error_log($this->conn->error);
            return false;
        }

    }

}