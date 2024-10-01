<?php

/*
 * FacturaVO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('CiaVO.php');
include_once ('ClientesVO.php');
include_once ('RelacionesVO.php');
include_once ('MetodoDePagoVO.php');
include_once ('FcVO.php');

class FacturaVO {
    /* @var $emisor CiaVO */
    private $emisor;
    /* @var $receptor ClientesVO */
    private $receptor;
    /* @var $comprobante FcVO */
    private $comprobante;
    /* @var $conceptos array */
    private $conceptos;
    /* @var $relacion RelacionesVO */
    private $relacion;

    /** @var $tipoDocumento String */
    private $tipoDocumento = "FA";

    private $desgloseIEPS;

    /** @var $metodoDePago MetodoDePagoVO */
    private $metodoDePago;

    function getDesgloseIEPS() {
        return $this->desgloseIEPS;
    }

    function getMetodoDePago() {
        return $this->metodoDePago;
    }

    /**
     * 
     * @return CiaVO
     */
    function getEmisor() {
        return $this->emisor;
    }

    /**
     * 
     * @return ClientesVO
     */
    function getReceptor() {
        return $this->receptor;
    }

    /**
     * 
     * @return FcVO
     */
    function getComprobante() {
        return $this->comprobante;
    }

    /**
     * 
     * @return array
     */
    function getConceptos() {
        return $this->conceptos;
    }

    function getRelacion() {
        return $this->relacion;
    }

    function setEmisor($emisor) {
        $this->emisor = $emisor;
    }

    function setReceptor($receptor) {
        $this->receptor = $receptor;
    }

    function setComprobante($comprobante) {
        $this->comprobante = $comprobante;
    }

    function setConceptos($conceptos) {
        $this->conceptos = $conceptos;
    }
    
    function setRelacion($relacion) {
        $this->relacion = $relacion;
    }

    function setDesgloseIEPS($desgloseIEPS) {
        $this->desgloseIEPS = $desgloseIEPS;
    }

    function setMetodoDePago($metodoDePago) {
        $this->metodoDePago = $metodoDePago;
    }

    private function cZeros($Vlr, $nLen, $Position = "") {
        $Position = strtoupper($Position);
        if ($Position == "" || $Position == "LEFT") {
            for ($i = strlen($Vlr); $i < $nLen; $i = $i + 1) {
                $Vlr = "0" . $Vlr;
            }
        } elseif($Position == "RIGHT") {
            for ($i = strlen($Vlr); $i < $nLen; $i = $i + 1) {
                $Vlr .= "0";
            }
        }
        return $Vlr;
    }//cZeros

    function getCfdi32Pipes() {
        // Inicializa los totalizadores
        $TSubtotal = 0;
        $TTotal = 0;

        $RTotal     = 0;
        $RIEPSTotal = 0;

        $ItemIndex = 0;

        $Detalle = "";

        $ItemIndex = 0;
        /* @var $concepto FacturaConceptoVO */
        foreach ($this->conceptos as $concepto) {
            $stotal      = round($concepto->getCantidad() * $concepto->getPrecio(), 2);
            $TSubtotal  += $stotal;

            $TTotal     += round($concepto->getTotal(), 2);

            $RTotal     += $concepto->getImpiva() + ($this->desgloseIEPS ? $concepto->getImpieps() : 0);
            $RIEPSTotal += round($concepto->getImpieps(), 2);

            $cDescripcion = ucwords(strtolower($concepto->getDescripcion())) . ($concepto->getTicket() > 0 ? " Ticket no: " . $concepto->getTicket() : "");

            $Concepto = "|" . (++$ItemIndex)
                    . "|" . round($concepto->getCantidad(), 3)
                    . "|" . $concepto->getUmedida()
                    . "|" . $concepto->getClave()
                    . "|" . $cDescripcion
                    . "|" . round($concepto->getPrecio(), 6)
                    . "|" . ""                                                  // Descuento
                    . "|" . round($concepto->getCantidad() * $concepto->getPrecio(), 2)
                    . "|" . ""                                                  // Pedimento
                    . "|" . ""                                                  // Fecha Pedimento
                    . "|" . ""                                                  // Aduana
                    . "|" . ""                                                  // Número Predial
                    . "|TRASLADADOS|IVA|" . round($concepto->getFactoriva() * 100, 0)
                    . "|" . round($concepto->getImpiva(), 4)
                    . "|IEPS|" . round($concepto->getFactorieps(), 4)
                    . "|" . round($concepto->getImpieps(), 4);
            $Detalle = $Detalle . $Concepto;
        }//foreach concepto

        $Partidas = $this->cZeros($ItemIndex, 2);
        $Detalle = "|" . $Partidas . "|" . $Detalle;

        $Datos = "01"                                                           // Encabezado;
            . "|FA"                                                             // Factura,Nota de Credito...
            . "|" . "3.2"                                                       // Version del CFDI
            . "|" . $this->comprobante->getSerie()
            . "|" . $this->comprobante->getId()
            . "|" . strtoupper($this->metodoDePago->getDescripcion()) 
            . "|" . ""                                                          // Numero de certificado aqui no mando nada
            . "|" . ""                                                          // Condiciones de pago NO MANDO NADA
            . "|" . round($TSubtotal, 4)
            . "|" . ""                                                          // Descuento
            . "|" . ""                                                          // Descripcion del Motivo del descuento
            . "|" . round($TTotal, 4)
            . "|" . ($this->receptor->getFormadepago()=="98" ? 
                    "NA" : $this->receptor->getFormadepago())
            . "|" . "ingreso"
            . "|" . "MXN"
            . "|" . "1"                                                         // Tipo de cambio 1;
            . "|EMISOR"
            . "|" . $this->emisor->getRfc()
            . "|" . $this->emisor->getCia()
            . "|DOMICILIO FISCAL"
            . "|" . $this->emisor->getDireccion()
            . "|" . $this->emisor->getNumeroext()
            . "|" . $this->emisor->getNumeroint()
            . "|" . $this->emisor->getColonia()
            . "|" . $this->emisor->getCiudad()
            . "|" . "TEL. " . $this->emisor->getTelefono()                      // Referencia de la localidad o/y tel
            . "|" . $this->emisor->getCiudad()
            . "|" . $this->emisor->getEstado()
            . "|MEXICO"
            . "|" . $this->emisor->getCodigo()
            . "|EXPEDIDO"
            . "|" . $this->emisor->getDireccionexp()
            . "|" . $this->emisor->getNumeroextexp()
            . "|" . $this->emisor->getNumerointexp()
            . "|" . $this->emisor->getColoniaexp()
            . "|" . ""                                                          // Localidad y Ref
            . "|" . ($this->emisor->getCiudadexp()=="" ? 
                    $this->emisor->getCiudad() : $this->emisor->getCiudadexp())
            . "|" . ($this->emisor->getEstadoexp()=="" ? 
                    $this->emisor->getEstado() : $this->emisor->getEstadoexp())
            . "|MEXICO"
            . "|" . $this->emisor->getCodigo()
            . "|RECEPTOR"
            . "|" . strtoupper($this->receptor->getRfc())
            . "|" . strtoupper($this->receptor->getNombre())
            . "|DOMICILIO_FISCAL"
            . "|" . $this->receptor->getDireccion()
            . "|" . $this->receptor->getNumeroext()
            . "|" . $this->receptor->getNumeroint()
            . "|" . $this->receptor->getColonia()
            . "|" . $this->receptor->getMunicipio()                             // Localidad
            . "|" . ""                                                          // Referencia
            . "|" . ""                                                          // Sin uso
            . "|" . $this->receptor->getMunicipio()
            . "|" . strtoupper($this->receptor->getEstado())
            . "|MEXICO"
            . "|" . $this->receptor->getCodigo()
            . "|" . $this->receptor->getCorreo()
            . "|" . 0.00                                                        //Total de impuesto retenido
            . "|" . round($RTotal, 2)
            . "|" . ""                                                          //Texto en caso de que haya retencion solo se pone RETENIDOS
            . "|" . "IVA"
            . "|" . $this->emisor->getIva()
            . "|" . 0                                                           //Importe del iva retenido
            . "|" . ""                                                          //SI es que hay isr pone la palabra Isr
            . "|" . ""                                                          //Tasa impuesto isr
            . "|" . ""                                                          //Importe del Isr
            . "|" . $this->emisor->getCiudad() . " " 
                  . $this->emisor->getEstado()                                  //Lugar donde se expide el comprobante
            . "|" . $this->emisor->getRegimen()
            . "|" . $this->receptor->getCuentaban()
            . "|" . ($this->desgloseIEPS==TRUE ? 'A' : '')
            . "|" . $this->comprobante->getObservaciones()
            . "|" . ""                                                          // Publicidad
            . "|" . $this->comprobante->getFecha()
            . "|INI_PRODUCTOS";

        $Datos = $Datos . $Detalle;
        
        return $Datos;
    }

    function getCfdi33Json() {
        // Inicializa los totalizadores
        $STotal         = 0;
        $Descuento      = 0;
        $TTotal         = 0;

        $RIEPSTotal     = 0;
        $RIVATotal      = 0;

        $TTasaIVA       = 0;
        $TTasaIEPS      = 0;

        $conceptos      = array();
        $timpuestos     = array();

        $descripcion    = " * Correspondiente a los tickets: "; 

        $aconceptos = array();
        $combustibles = array();

        $inited = false;
        /* @var $concepto FacturaConceptoVO */
        foreach ($this->conceptos as $concepto) {
            $precio = round($concepto->getPrecio() + ($this->desgloseIEPS==TRUE ? 0 : $concepto->getFactorieps()), 4);
            error_log("-----------------------------" . $concepto->getPrecio());
            error_log("-----------------------------" . $precio);
            if ($concepto->getProducto()<=10) {
                $FlagPrecio  = $precio . "-" . $concepto->getFactorieps();
                if (array_key_exists($FlagPrecio, $combustibles)) {
                    /* @var $aconcepto FacturaConceptoVO */
                    $combustibles[$FlagPrecio]->setCantidad($combustibles[$FlagPrecio]->getCantidad() + $concepto->getCantidad());
                    $combustibles[$FlagPrecio]->setSubtotal($combustibles[$FlagPrecio]->getSubtotal() + round($concepto->getCantidad() * $precio, 4));
                    $combustibles[$FlagPrecio]->setDescuento($combustibles[$FlagPrecio]->getDescuento() + $concepto->getDescuento());
                    $combustibles[$FlagPrecio]->setBaseIva($combustibles[$FlagPrecio]->getBaseIva() + $concepto->getBaseIva());
                    $combustibles[$FlagPrecio]->setBaseIeps($combustibles[$FlagPrecio]->getBaseIeps() + $concepto->getBaseIeps());
                    $combustibles[$FlagPrecio]->setImpIva(round($combustibles[$FlagPrecio]->getBaseIva() * $concepto->getFactoriva(), 2));
                    $combustibles[$FlagPrecio]->setImpieps(round($combustibles[$FlagPrecio]->getCantidad() * $concepto->getFactorieps(), 2));
                    $combustibles[$FlagPrecio]->setTotal($combustibles[$FlagPrecio]->getTotal() + $concepto->getTotal());

                    $descripcion .= ", " . ($concepto->getTicket() == "0" ? "Captura Manual" : $concepto->getTicket());
                    error_log("Suma => " . $combustibles[$FlagPrecio]);
                } else {
                    $descripcion .= ($inited ? ", " : "") . ($concepto->getTicket() == "0" ? "Captura Manual" : $concepto->getTicket());
                    $combustibles[$FlagPrecio] = new FacturaConceptoVO();

                    $combustibles[$FlagPrecio]->setId($concepto->getId());
                    $combustibles[$FlagPrecio]->setClave($concepto->getClave());
                    $combustibles[$FlagPrecio]->setProducto($concepto->getProducto());
                    $combustibles[$FlagPrecio]->setDescripcion($concepto->getDescripcion() . " * ");
                    $combustibles[$FlagPrecio]->setInv_cproducto($concepto->getInv_cproducto());
                    $combustibles[$FlagPrecio]->setInv_cunidad($concepto->getInv_cunidad());
                    $combustibles[$FlagPrecio]->setFactoriva($concepto->getFactoriva());
                    $combustibles[$FlagPrecio]->setFactorieps($concepto->getFactorieps());

                    $combustibles[$FlagPrecio]->setCantidad($concepto->getCantidad());
                    $combustibles[$FlagPrecio]->setPrecio($precio);
                    $combustibles[$FlagPrecio]->setSubtotal(round($concepto->getCantidad() * $precio, 4));
                    $combustibles[$FlagPrecio]->setDescuento($concepto->getDescuento());
                    $combustibles[$FlagPrecio]->setBaseIva($concepto->getBaseIva());
                    $combustibles[$FlagPrecio]->setBaseIeps($concepto->getBaseIeps());
                    $combustibles[$FlagPrecio]->setImpiva(round($concepto->getBaseIva() * $concepto->getFactoriva(), 2));
                    $combustibles[$FlagPrecio]->setImpieps(round($concepto->getCantidad() * $concepto->getFactorieps(), 2));
                    $combustibles[$FlagPrecio]->setTotal($concepto->getTotal());

                    error_log("Inicia => " . $combustibles[$FlagPrecio]);
                    $inited = true;
                }
            } else {
                $aconceptos[] = $concepto;
            }
        }
        
        /* @var $concepto FacturaConceptoVO */
        foreach ($combustibles as $ieps => $concepto) {
            $aconceptos[] = $concepto;
        }

        /* @var $concepto FacturaConceptoVO */
        foreach ($aconceptos as $concepto) {
            $STotal     += round($concepto->getSubtotal(), 2);
            $Descuento  += $concepto->getDescuento();
            $TTotal     += $concepto->getTotal();

            $RIEPSTotal += $concepto->getImpieps();
            $RIVATotal  += $concepto->getImpiva();

            $TTasaIVA   = $concepto->getFactoriva();
            $TTasaIEPS  = $concepto->getFactorieps();

            $cDescripcion = ucwords(strtolower($concepto->getDescripcion())) . ($concepto->getTicket() > 0 ? " Ticket no: " . $concepto->getTicket() : "");

            if (array_key_exists('IVA', $timpuestos)) {
                $timpuestos['IVA']['Importe'] += $concepto->getImpiva();
            } else {
                $timpuestos['IVA'] = array(
                    'Impuesto'      => '002',
                    'TipoFactor'    => 'Tasa',
                    'TasaOCuota'    => $TTasaIVA,
                    'Importe'       => $concepto->getImpiva()
                );
            }

            if ($TTasaIEPS>0) {
                if (array_key_exists($TTasaIEPS, $timpuestos)) {
                    $timpuestos[$TTasaIEPS]['Importe'] += $concepto->getImpieps();
                } else {
                    $timpuestos[$TTasaIEPS] = array(
                        'Impuesto'      => '003',
                        'TipoFactor'    => 'Cuota',
                        'TasaOCuota'    => $TTasaIEPS,
                        'Importe'       => $concepto->getImpieps()
                    );
                }
            }

            $traslados = array();
            $tIva = array(
                'Base'          => $concepto->getBaseIva(),
                'Impuesto'      => '002',
                'TipoFactor'    => 'Tasa',
                'TasaOCuota'    => $concepto->getFactoriva(),
                'Importe'       => $concepto->getImpiva()
            );

            $tIeps = array(
                'Base'          => $concepto->getCantidad(),
                'Impuesto'      => '003',
                'TipoFactor'    => 'Cuota',
                'TasaOCuota'    => $concepto->getFactorieps(),
                'Importe'       => $concepto->getImpieps()
            );

            array_push($traslados, $tIva);
            if ($tIeps['Importe']>0 && $this->desgloseIEPS==TRUE) {
                array_push($traslados, $tIeps);
            }

            $concepto = array(
                'claveProducto' => $concepto->getInv_cproducto(),                                               // Clave de Producto, viene de CClaveProdServ
                'noIdentificacion'=> $concepto->getClave(),                                                     // Número de producto o servicio, SKU, clave o equivalente propio del Emisor
                'cantidad' => $concepto->getCantidad(),                                                         // Cantidad de producto
                'claveUnidad' => $concepto->getInv_cunidad(),                                                   // Clave de la unidad de medida empleada, viene de CClaveUnidad
                'descripcion' => $cDescripcion,                                                                 // Descripcion del bien o servicio
                'valorUnitario' => $concepto->getPrecio(),                                                      // Costo unitario antes de impuestos y descuentos
                'importe' => round($concepto->getSubtotal(), 2),                                                // Importe total, debe resultar de multiplicar el costo unitario por la cantidad
                'descuento' => $concepto->getDescuento(),
                'traslados' => $traslados,
                'retenciones' => array()
            );

            array_push($conceptos, $concepto);
        }//foreach concepto

        $impuestos = array();
        foreach ($timpuestos as $key=>$value) {
            if ($value['Impuesto']!='003' || $this->desgloseIEPS==TRUE) {
                array_push($impuestos, $value);
            }
        }

        $cfdiRelacionados = NULL;
        if ($this->relacion->hasRelated()) {
            $relaciones = array();
            $relacion = array(
                'uuid'=>$this->relacion->getUuid()
            );
            array_push($relaciones, $relacion);
            $cfdiRelacionados = array(
                'tipo'=>$this->relacion->getTipoRelacion(),
                'cfdis'=>$relaciones
            );
        }

        $cfdi= array(
            'tipo'=> $this->tipoDocumento,                                          // Tipo de CFDI (Omicrom)
            'version' => "3.3",                                                     // Versión del CFDI *
            'serie' => $this->comprobante->getSerie()==null ? 
                        $this->comprobante->getSerie() : "",          
            'cfdiRelacionados' => $cfdiRelacionados,                                // Serie del CFDI
            'folio' => $this->comprobante->getId(),                                 // Folio del CDFI
            'fecha' => $this->comprobante->getFecha(),                              // Fecha de emisión del comprobante
            'metodoDePago' => $this->metodoDePago->getClave(),                      // Método de pago, viene de CMetodoPago *
            'subTotal' => $STotal,                                                  // Sub total. Total antes de impuestos y descuentos.
            'descuento' => $Descuento,                                              // Descuento
            'total' => $STotal 
                        + $RIVATotal 
                        + ($this->desgloseIEPS==TRUE ? $RIEPSTotal : 0)
                        - $Descuento,                                               // Total despues del impuestos y descuentos
            'formaDePago' => ($this->receptor->getFormadepago()=='98' ? 'NA' 
                        : $this->receptor->getFormadepago()),                       // Forma de pago Viene de CFormaPago
            'tipoDeComprobante' => 'I',                                             // Tipo de comprobante, viene de CTipoDeComprobante *
            'moneda' => 'MXN',                                                      // Moneda MXN. Viene de CMoneda *
            'tipoDeCambio' => '1',                                                  // Tipo de cambio. Requerido si Moneda es diferente de 'MXN'
            'emisor' => array(
                        'rfc' => strtoupper($this->emisor->getRfc()),               // RFC
                        'razonSocial' => strtoupper($this->emisor->getCia()),       // Razón Social
                        'regimenFiscal' => $this->emisor->getClave_regimen()),      // Régimen Fiscal, viene de CRegimenFiscal *
            'receptor' => array(
                        'rfc' => strtoupper($this->receptor->getRfc()),             // RFC
                        'razonSocial' => $this->receptor->getNombre(),              // Razon social
                        'usoCFDI' => $this->comprobante->getUsocfdi()),             // Uso del CFDI, viene de CUsoCFDI
            'lugarExpedicion' => $this->emisor->getCodigo(),                        // Codigo Postal
            'conceptos' => $conceptos,                                              // Conceptos amparados por el CFDI
            'observaciones' => array($this->comprobante->getObservaciones(),
                $descripcion),
            'impuestos' => array(
                        'traslados' => $impuestos,
                        'TotalImpuestosTrasladados' => $RIVATotal 
                                + ($this->desgloseIEPS==TRUE ? $RIEPSTotal : 0),
                        'TotalImpuestosRetenidos' => 0.00));
        return $cfdi;
    }//getCFDIJson

    function getCfdi33JaxbJson() {

        // Inicializa los totalizadores
        $Subtotal = 0.00;
        $Total = 0.00;

        $ITTotal = 0.00;   // Impuesto trasladado total
        $IRTotal = 0.00;   // Impuesto retenido total

        $ITIEPSTotal = 0.00; // IEPS trasladado total

        $TTasaIVA  = 0;
        $TTasaISR  = 0;
        $TTasaIEPS = 0;

        $conceptos = array();
        // Impuestos trasladados
        $timpuestos = array();
        // Impuestos retenidos
        $rimpuestos = array();

        /* @var $concepto FacturaConceptoVO */
        foreach ($this->conceptos as $concepto) {

            $stotal      = round($concepto->getCantidad() * $concepto->getPrecio(), 2);

            $Subtotal   += $stotal;
            $Total      += round($concepto->getTotal(), 2);

            $IRTotal     += $concepto->getRetencioniva() + $concepto->getImpisr();
            $ITTotal     += $concepto->getImpiva() + $concepto->getImpieps();

            $ITIEPSTotal += round($concepto->getImpieps(), 2);

            $TTasaIVA   = $concepto->getFactoriva();
            $TTasaIEPS  = $concepto->getFactorieps();
            $TTasaISR   = $concepto->getFactorisr();

            $cDescripcion = ucwords(strtolower($concepto->getDescripcion()));

            // Retenciones -----------------------------------------------------------------------------------------
            if (array_key_exists('IVA', $rimpuestos)) {
                $rimpuestos['IVA']['Importe']   += round($concepto->getRetencioniva(), 2);
            } else {
                $rimpuestos['IVA'] = array(
                    'Impuesto'      => '002',
                    'TipoFactor'    => 'Tasa',
                    'TasaOCuota'    => $TTasaIVA,
                    'Importe'       => round($concepto->getRetencioniva(), 2)
                );
            }

            if (array_key_exists('ISR', $rimpuestos)) {
                $rimpuestos['ISR']['Importe']   += round($concepto->getImpisr(), 2);
            } else {
                $rimpuestos['ISR'] = array(
                    'Impuesto'      => '001',
                    'TipoFactor'    => 'Tasa',
                    'TasaOCuota'    => $TTasaISR,
                    'Importe'       => round($concepto->getImpisr(), 2)
                );
            }

            $retenciones = array();
            $rIva = array(
                'Base' => round($stotal, 2),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => $concepto->getFactoriva(),
                'Importe' => round($concepto->getRetencioniva(), 2) 
            );

            $rIsr = array(
                'Base' => round($stotal, 2),
                'Impuesto' => '001',
                'TipoFactor' => 'Cuota',
                'TasaOCuota' => $concepto->getFactorisr(),
                'Importe' => round($concepto->getImpisr(), 2) 
            );

            if ($rIva['Importe']>0) {
                array_push($retenciones, $rIva);
            }

            if ($rIsr['Importe']>0) {
                array_push($retenciones, $rIsr);
            }

            // Traslados -------------------------------------------------------------------------------------------
            if (array_key_exists('IVA', $timpuestos)) {
                $timpuestos['IVA']['Importe']   += round($concepto->getImpiva(), 2);
            } else {
                $timpuestos['IVA'] = array(
                    'Impuesto'      => '002',
                    'TipoFactor'    => 'Tasa',
                    'TasaOCuota'    => $TTasaIVA,
                    'Importe'       => round($concepto->getImpiva(), 2)
                );
            }

            if ($TTasaIEPS>0) {
                if (array_key_exists($TTasaIEPS, $timpuestos)) {
                    $timpuestos[$TTasaIEPS]['Importe']   += round($concepto->getImpieps(), 2);
                } else {
                    $timpuestos[$TTasaIEPS] = array(
                        'Impuesto'      => '003',
                        'TipoFactor'    => 'Cuota',
                        'TasaOCuota'    => $TTasaIEPS,
                        'Importe'       => round($concepto->getImpieps(), 2)
                    );
                }
            }

            $traslados = array();
            $tIva = array(
                'Base' => round($stotal, 2),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => $concepto->getFactoriva(),
                'Importe' => round($concepto->getImpiva(), 2) 
            );

            $tIeps = array(
                'Base' => round($stotal, 2),
                'Impuesto' => '003',
                'TipoFactor' => 'Cuota',
                'TasaOCuota' => $concepto->getFactorieps(),
                'Importe' => round($concepto->getImpieps(), 2) 
            );

            array_push($traslados, $tIva);
            if ($tIeps['Importe']>0 && $this->desgloseIEPS===TRUE) {
                array_push($traslados, $tIeps);
            }

            $concepto = array(
                'ClaveProdServ' => $concepto->getInv_cproducto(),                                               // Clave de Producto, viene de CClaveProdServ
                'NoIdentificacion'=> $concepto->getClave(),                                                     // Número de producto o servicio, SKU, clave o equivalente propio del Emisor
                'Cantidad' => round($concepto->getCantidad(), 3),                                               // Canitidad de producto
                'ClaveUnidad' => $concepto->getInv_cunidad(),                                                   // Clave de la unidad de medida empleada, viene de CClaveUnidad
                'Descripcion' => $cDescripcion,                                                                 // Descripcion del bien o servicio
                'ValorUnitario' => round($concepto->getPrecio() 
                        + ($this->desgloseIEPS===FALSE ? $concepto->getFactorieps() : 0.00), 6),                // Costo unitario antes de impuestos y descuentos
                'Importe' => round($concepto->getCantidad() * $concepto->getPrecio() 
                    + ($this->desgloseIEPS===FALSE ? $concepto->getImpieps() : 0.00), 2),                       // Importe total, debe resultar de multiplicar el costo unitario por la cantidad
                'Impuestos' => array(
                    'Traslados' => array('Traslado' => $traslados),
                    'Retenciones' => array('Retencion' => $retenciones))
            );

            array_push($conceptos, $concepto);
        }//foreach concepto

        error_log("Agregando las retenciones");
        $gretenciones = array();
        foreach ($rimpuestos as $key=>$value) {
            error_log("Impuesto Retenido " . $value['Impuesto'] . " importe " . $value['Importe']);
            if ($value['Importe']>0) {
                array_push($gretenciones, $value);
            }
        }

        error_log("Agregando los traslados");
        $gtraslados = array();
        foreach ($timpuestos as $key=>$value) {
            error_log("Impuesto Trasladado " . $value['Impuesto'] . " importe " . $value['Importe']);
            if ($value['Importe']>0 && 
                    ($value['Impuesto']!=='003' || $this->desgloseIEPS===TRUE)) {
                array_push($gtraslados, $value);
            }
        }

        $cfdiRelacionados = NULL;
        if ($this->relacion->hasRelated()) {
            $relaciones = array();
            $relacion = array(
                'UUID'=>$this->relacion->getUuid()
            );
            array_push($relaciones, $relacion);
            $cfdiRelacionados = array(
                'TipoRelacion' => $this->relacion->getTipoRelacion(),
                'CfdiRelacionado' => $relaciones
            );
        }

        //'tipo'=> "FA",                                                                 // Tipo de CFDI (Omicrom)
        $cfdi= array(
            'CfdiRelacionados' => $cfdiRelacionados,                                     // Serie del CFDI
            'Emisor' => array(
                    'Rfc' => strtoupper($this->emisor->getRfc()),                        // RFC
                    'Nombre' => strtoupper($this->emisor->getNombre()),                  // Razón Social
                    'RegimenFiscal' => $this->emisor->getClave_regimen()),               // Régimen Fiscal, viene de CRegimenFiscal *
            'Receptor' => array(
                    'Rfc' => strtoupper($this->receptor->getRfc()),                      // RFC
                    'Nombre' => $this->receptor->getNombre(),                            // Razon social
                    'UsoCFDI' => $this->getReceptor()->getUsoCFDI()),                    // Uso del CFDI, viene de CUsoCFDI
            'Conceptos' => $conceptos,                                                 // Conceptos apmarados por el CFDI
            'Impuestos' => array(
                    'Retenciones' => $gretenciones,
                    'Traslados' => $gtraslados,
                    'TotalImpuestosTrasladados' => round($ITTotal 
                                - ($this->desgloseIEPS===FALSE ? $ITIEPSTotal : 0.00), 2),
                    'TotalImpuestosRetenidos' => round($IRTotal, 2)),
            'Version' => "3.3",                                                          // Versión del CFDI *
            'Serie' =>  "",                                                              // Serie del CFDI
            'Folio' => $this->comprobante->getFolio(),                                   // Folio del CDFI
            'Fecha' => $this->comprobante->getFecha(),                                   // Fecha de emisión del comprobante
            'FormaPago' => ($this->receptor->getFormadepago()=='98' ? 'NA' 
                        : $this->receptor->getFormadepago()),                            // Forma de pago Viene de CFormaPago
            'SubTotal' => round($Subtotal 
                        + ($this->desgloseIEPS===FALSE ? $ITIEPSTotal : 0.00), 2),       // Sub total. Total antes de impuestos y descuentos.
            'Descuento' => 0.00,                                                         // Descuento
            'Moneda' => 'MXN',                                                           // Moneda MXN. Viene de CMoneda *
            'TipoCambio' => '1',                                                         // Tipo de cambio. Requerido si Moneda es diferente de 'MXN'
            'Total' => round($Total, 2),                                                 // Total despues del impuesto
            'TipoDeComprobante' => 'I',                                                  // Tipo de comprobante, viene de CTipoDeComprobante *
            'MetodoPago' => "PUE",                                                       // Método de pago, viene de CMetodoPago *
            'LugarExpedicion' => $this->emisor->getCodigo());                            // Codigo Postal
        return $cfdi;
    }//getCfdiJaxbJson

    private function __conceptosToString() {
        $conceptos = "[";
        $i = 0;
        foreach ($this->conceptos as $concepto) {
            $conceptos = $conceptos . ($i++==0 ? "" : ", ") . $concepto;
        }
        return $conceptos . "]";
    }//__conceptosToString

    public function __toString() {
        return "FacturaVO=comprobante=".$this->comprobante
                .", metodoDePago=".$this->metodoDePago
                .", emisor=".$this->emisor
                .", receptor=".$this->receptor
                .", conceptos=".$this->__conceptosToString()."}";
    }//__toString

}//FacturaVO
