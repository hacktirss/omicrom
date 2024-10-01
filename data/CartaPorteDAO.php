<?php

/**
 * Description of CartaPorteDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodríguez
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

    class CartaPorteDAO {

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
            $sql = "SELECT td.preciob,td.iva,td.ieps,cp.moneda,DATE_FORMAT(t.fecha, '%Y-%m-%dT%H:%i:%s') fechat,t.metodoPago,t.formaPago,t.usoCfdi,t.id "
                    . "FROM traslados t LEFT JOIN traslados_detalle td ON t.id = td.id "
                    . "LEFT JOIN carta_porte cp ON td.id=cp.id_origen  WHERE t.id = " . $this->folio . " LIMIT 1;";
            error_log("SQL 1 " . $sql);
            $cia = "SELECT codigo FROM cia;";
            $Cia = $this->conn->query($cia)->fetch_assoc();
            if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {

                $ImpuestoT = $rs["preciob"] - $this->comprobante->getSubTotal();
                $Imps = $ImpuestoT - ($this->comprobante->getSubTotal() * ($rs["ieps"] / 100));
                $this->comprobante->setSubTotal(0.00);
                $this->comprobante->setTotal(0.00);
                $this->comprobante->setFecha($rs['fechat']);
                $this->comprobante->setTipoDeComprobante("T");
                $this->comprobante->setVersion("4.0");
                $this->comprobante->setSerie("TR");
                $this->comprobante->setFolio($rs["id"]);
//                $this->comprobante->setMetodoPago($rs["metodoPago"]);
//                $this->comprobante->setFormaPago($rs["formaPago"]);
                $this->comprobante->setMoneda("XXX");
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
            $sql = "SELECT cia.codigo,cia.clave_regimen regimenfiscal, REGEXP_REPLACE(upper(cia), '([, ]{1,4})?[S][.]?[A][.]?[ ]{1,3}[DE]{2}[ ]{1,3}([C][.]?[V][.]?|[R][.]?[L][.]?)$','') nombre, cia.rfc,t.usoCfdi "
                    . "FROM traslados t LEFT JOIN cia ON true WHERE t.id = " . $this->folio;
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

            $sql = "SELECT * FROM omicrom.traslados_detalle td LEFT JOIN inv ON td.producto = inv.id where td.id=" . $this->folio . " AND producto >= 1;";

            $total = 0.00;

            if (($query = $this->conn->query($sql))) {

                while (($rs = $query->fetch_assoc())) {
                    $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                    $concepto->setClaveProdServ($rs['inv_cproducto']);
                    $concepto->setClaveUnidad($rs['inv_cunidad']);
                    $concepto->setDescripcion($rs['descripcion']);
                    $concepto->setImporte(number_format($rs['importe'], 2, '.', ''));
                    $concepto->setCantidad(number_format($rs['cantidad'], 2, '.', ''));
                    $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                    $concepto->setValorUnitario(number_format($rs['preciob'], 2, '.', ''));
                    $concepto->setObjetoImp("01");
                    $subTotal += $rs['Importe'];
                    //$concepto->setImpuestos($impuestos);
                    $conceptos->addConcepto($concepto);
                }

                //$this->comprobante->setSubTotal(number_format($subTotal, 2, '.', ''));
                $this->comprobante->setConceptos($conceptos);
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
                    . "WHERE catalogo_direcciones.tabla_origen = 'D' AND cpd.origen='TRA'  AND carta_porte.id = (SELECT id FROM carta_porte WHERE origen = '"
                    . $this->origen . "' AND id_origen = " . $this->folio . ") LIMIT 1;";
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
                            . "WHERE id_carta_porte_fk = " . $rs["id"] . " AND tipo = 'Destino' AND origen='TRA' ;";
                    if ($Dist = $this->conn->query($Distancia)) {
                        $TotalRecorrido = 0;
                        while ($cP = $Dist->fetch_assoc()) {
                            $TotalRecorrido = $TotalRecorrido + $cP["distancia"];
                        }
                    }
                    error_log("TOTAL DIST REC " . $TotalRecorrido);
                    $cartaPorte->setTotalDistRec($TotalRecorrido);
                    /* Ubicacion Origen */
                    $cartaPorteU->setTipoUbicacion("Origen");
                    $idUbi = substr(str_repeat(0, 6) . $rs["id"], - 6);
                    //$cartaPorteU->setIdUbicacion("OR" . $idUbi);
                    $cartaPorteU->setRfcRemitenteDestinatario($Cia["rfc"]);
                    $cartaPorteU->setFechaHoraSalidaLlegada($rs["fechaHoraSalidaLlegada"] . ":01");
                    $cartaPorteDom = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion\Domicilio();
                    $cartaPorteDom->setEstado($Cia["estado"]);
                    $cartaPorteDom->setPais("MEX");
                    $cartaPorteDom->setCodigoPostal($Cia["codigo"]);
                    $cartaPorteDom->setNumeroExterior($Cia["numeroext"]);
                    $cartaPorteDom->setNumeroInterior($Cia["numeroint"]);
                    $cartaPorteDom->setCalle($Cia["direccion"]);
                    $cartaPorteDom->setColonia($rscia["coloniaClv"]);
                    //$cartaPorteDom->setLocalidad($rscia["localidadClv"]);
                    $cartaPorteDom->setReferencia($Cia["direccion"]);
                    $cartaPorteDom->setMunicipio($rscia["municipioClv"]);
                    $cartaPorteDom->setEstado($rscia["estadoClv"]);
                    $cartaPorteDom->setPais("MEX");

                    $cartaPorteU->setDomicilio($cartaPorteDom);
                    $Ubicaciones->addUbicacion($cartaPorteU);
                    /* Ubicacion Destino */
                    $CartaPorte = "SELECT *,carta_porte_destino.id idcpd FROM carta_porte_destino LEFT JOIN catalogo_direcciones cd ON "
                            . "carta_porte_destino.id_destino_fk = cd.id "
                            . "WHERE id_carta_porte_fk = " . $rs["id"] . " AND tipo = 'Destino' AND origen='TRA' ;";
                    if ($CartaP = $this->conn->query($CartaPorte)) {
                        $i = 0;

                        while ($cP = $CartaP->fetch_assoc()) {
                            $cartaPorteUD = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion();
                            $cartaPorteDomD = new complementos\cartaporte\CartaPorte20\Ubicaciones\Ubicacion\Domicilio();

                            $cartaPorteDomD->setEstado($cP["estado"]);
                            $cartaPorteDomD->setMunicipio($cP["municipio"]);
                            $cartaPorteDomD->setReferencia($cP["descripcion"]);
                            // $cartaPorteDomD->setLocalidad($cP["localidad"]);
                            $cartaPorteDomD->setColonia($cP["colonia"]);
                            $cartaPorteDomD->setNumeroExterior($cP["num_exterior"]);
                            $cartaPorteDomD->setNumeroInterior($cP["num_interior"]);
                            $cartaPorteDomD->setCalle($cP["calle"]);
                            $cartaPorteDomD->setPais("MEX");
                            $cartaPorteDomD->setCodigoPostal($cP["codigo_postal"]);
                            $cartaPorteUD->setDomicilio($cartaPorteDomD);

                            $idUbi = "DE" . substr(str_repeat(0, 6) . $cP["idcpd"], - 6);
                            $cartaPorteUD->setTipoUbicacion("Destino");
                            $cartaPorteUD->setRfcRemitenteDestinatario($Cia["rfc"]);
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
                            . "FROM traslados_detalle td "
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
                        $cartaPorteMercancia->setDescripEmbalaje("Emabalaje");
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
                    $Remolques = "SELECT * FROM omicrom.carta_porte_remolques where id_carta_porte_fk = (select id FROM carta_porte WHERE id_origen=" . $this->folio . " AND origen='" . $rs["origen"] . "');";
                    error_log($Remolques);
                    $rRem = $this->conn->query($Remolques);
                    while ($rmRs = $rRem->fetch_assoc()) {
                        $autoTransporteRemolquesRem = new complementos\cartaporte\CartaPorte20\Mercancias\Autotransporte\Remolques\Remolque();
                        $autoTransporteRemolquesRem->setPlaca($rmRs["placas"]);
                        $autoTransporteRemolquesRem->setSubTipoRem($rmRs["SubTipoRem"]);
                        $autoTransporteRemolques->addRemolque($autoTransporteRemolquesRem);
                    }
                    $autoTransporte->setRemolques($autoTransporteRemolques);

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