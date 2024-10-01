<?php

/*
 * NotaCreditoVO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2017
 */

include_once ('CiaVO.php');
include_once ('ClientesVO.php');
include_once ('RelacionesVO.php');
include_once ('MetodoDePagoVO.php');
include_once ('NotaCreditoConceptoVO.php');
include_once ('NcVO.php');

class NotaCreditoVO {
    /* @var $emisor CiaVO */
    private $emisor;
    /* @var $receptor ClientesVO */
    private $receptor;
    /* @var $comprobante NcVO */
    private $comprobante;
    /* @var $conceptos array */
    private $conceptos;
    /* @var $relacion RelacionesVO */
    private $relacion;

    /** @var $tipoDocumento String */
    private $tipoDocumento = "CR";

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
     * @return NcVO
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
        /* @var $concepto NotaCreditoConceptoVO */
        foreach ($this->conceptos as $concepto) {
            $precio = $concepto->getPrecio();
            error_log("-----------------------------" . $concepto->getPrecio());
            error_log("-----------------------------" . $precio);
            if ($concepto->getProducto()<=10) {
                $FlagPrecio  = $precio . "-" . $concepto->getFactorieps();
                if (array_key_exists($FlagPrecio, $combustibles)) {
                    /* @var $aconcepto NotaCreditoConceptoVO */
                    $combustibles[$FlagPrecio]->setCantidad($combustibles[$FlagPrecio]->getCantidad() + $concepto->getCantidad());
                    $combustibles[$FlagPrecio]->setSubtotal($combustibles[$FlagPrecio]->getSubtotal() + ($concepto->getCantidad() * $precio));
                    $combustibles[$FlagPrecio]->setTotal($combustibles[$FlagPrecio]->getTotal() + $concepto->getTotal());

                } else {
                    $combustibles[$FlagPrecio] = new NotaCreditoConceptoVO();

                    $combustibles[$FlagPrecio]->setId($concepto->getId());
                    $combustibles[$FlagPrecio]->setClave($concepto->getClave());
                    $combustibles[$FlagPrecio]->setProducto($concepto->getProducto());
                    $combustibles[$FlagPrecio]->setDescripcion($concepto->getDescripcion() . " * ");
                    $combustibles[$FlagPrecio]->setInv_cproducto($concepto->getInv_cproducto());
                    $combustibles[$FlagPrecio]->setInv_cunidad($concepto->getInv_cunidad());
                    $combustibles[$FlagPrecio]->setFactoriva($concepto->getFactoriva());
                    $combustibles[$FlagPrecio]->setFactorieps($concepto->getFactorieps());
                    $combustibles[$FlagPrecio]->setPrecio($precio);
                    $combustibles[$FlagPrecio]->setDescuento(0);

                    $combustibles[$FlagPrecio]->setCantidad($concepto->getCantidad());
                    $combustibles[$FlagPrecio]->setSubtotal($concepto->getCantidad() * $precio);
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

        // Redondeo
        foreach ($aconceptos as $concepto) {
            $concepto->setBaseIva(round($concepto->getSubtotal(), 2));
            $concepto->setBaseIeps(round($concepto->getCantidad(), 2));
            $concepto->setSubtotal(round($concepto->getSubtotal(), 2));
            $concepto->setTotal(round($concepto->getTotal(), 2));
            $concepto->setImpieps(round($concepto->getCantidad() * $concepto->getFactorieps(), 2));
            $concepto->setImpiva(round($concepto->getSubtotal() * $concepto->getFactoriva(), 2));
        }

        // Ajuste
        $CSTotal         = 0;
        $CDescuento      = 0;
        $CTTotal         = 0;

        $CITIEPSTotal     = 0;
        $CITIVATotal      = 0;
        /* @var $concepto FacturaConceptoVO */
        foreach ($aconceptos as $concepto) {
            error_log($concepto);
            $CTTotal     = $concepto->getTotal();
            $CSTotal     = $concepto->getSubtotal();
            $CITIEPSTotal = $concepto->getImpieps();
            $CITIVATotal  = $concepto->getImpiva();

            error_log("IVA Concepto => " . $CITIVATotal);
            error_log("IEPS Concepto => " . $CITIEPSTotal);
            error_log("Subtotal Concepto => " . $CSTotal);
            error_log("Total Concepto => " . $CTTotal);

            $CCTotal = $CSTotal + $CITIEPSTotal + $CITIVATotal;

            error_log("Total reportado (redondeado a dos decimales) " . $CTTotal);
            error_log("Total calculado (redondeado a dos decimales) " . $CCTotal);

            $CDiferencia = round($CTTotal - $CCTotal, 2);

            if ($CDiferencia <> 0) {
                error_log("Concepto => " . $concepto);
                $concepto->setSubtotal($concepto->getSubtotal() + $CDiferencia);
                $concepto->setCantidad(round($concepto->getSubtotal()/$concepto->getPrecio(), 4));
                error_log("Concepto ajustado => " . $concepto);
            } else {
                error_log("No hay diferencia entre el total calculado y el reportado");
            }
        }

        /* @var $concepto FacturaConceptoVO */
        foreach ($aconceptos as $concepto) {
            $STotal     += $concepto->getSubtotal();
            $Descuento  += $concepto->getDescuento();
            $TTotal     += $concepto->getTotal();

            $RIEPSTotal += $concepto->getImpieps();
            $RIVATotal  += $concepto->getImpiva();

            $TTasaIVA   = $concepto->getFactoriva();
            $TTasaIEPS  = $concepto->getFactorieps();

            $cDescripcion = ucwords(strtolower($concepto->getDescripcion()));

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
                'claveProducto' => $concepto->getInv_cproducto(),                                                           // Clave de Producto, viene de CClaveProdServ
                'noIdentificacion'=> $concepto->getClave(),                                                                 // Numero de producto o servicio, SKU, clave o equivalente propio del Emisor
                'cantidad' => $concepto->getCantidad(),                                                                     // Cantidad de producto
                'claveUnidad' => $concepto->getInv_cunidad(),                                                               // Clave de la unidad de medida empleada, viene de CClaveUnidad
                'descripcion' => $cDescripcion,                                                                             // Descripcion del bien o servicio
                'valorUnitario' => $concepto->getPrecio() + ($this->desgloseIEPS==TRUE ? 0 : $concepto->getFactorieps()),   // Costo unitario antes de impuestos y descuentos
                'importe' => $concepto->getSubtotal() + ($this->desgloseIEPS==TRUE ? 0 : $concepto->getImpieps()),          // Importe total, debe resultar de multiplicar el costo unitario por la cantidad
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
            'version' => "3.3",                                                     // Version del CFDI *
            'serie' => $this->comprobante->getSerie()==null ? 
                        $this->comprobante->getSerie() : "",          
            'cfdiRelacionados' => $cfdiRelacionados,                                // Serie del CFDI
            'folio' => $this->comprobante->getId(),                                 // Folio del CDFI
            'fecha' => $this->comprobante->getFecha(),                              // Fecha de emision del comprobante
            'metodoDePago' => $this->comprobante->getMetododepago(),                // M�todo de pago, viene de CMetodoPago *
            'subTotal' => $STotal + ($this->desgloseIEPS==TRUE ? 
                    0 : $RIEPSTotal),                                               // Sub total. Total antes de impuestos y descuentos.
            'total' => $TTotal,                                                     // Total despues del impuestos y descuentos
            'formaDePago' => ($this->comprobante->getFormadepago()=='98' ? 'NA' 
                        : $this->comprobante->getFormadepago()),                    // Forma de pago Viene de CFormaPago
            'tipoDeComprobante' => 'E',                                             // Tipo de comprobante, viene de CTipoDeComprobante *
            'moneda' => 'MXN',                                                      // Moneda MXN. Viene de CMoneda *
            'tipoDeCambio' => '1',                                                  // Tipo de cambio. Requerido si Moneda es diferente de 'MXN'
            'emisor' => array(
                        'rfc' => strtoupper($this->emisor->getRfc()),               // RFC
                        'razonSocial' => strtoupper($this->emisor->getCia()),       // Raz�n Social
                        'regimenFiscal' => $this->emisor->getClave_regimen()),      // R�gimen Fiscal, viene de CRegimenFiscal *
            'receptor' => array(
                        'rfc' => strtoupper($this->receptor->getRfc()),             // RFC
                        'razonSocial' => $this->receptor->getNombre(),              // Razon social
                        'usoCFDI' => $this->comprobante->getUsocfdi()),             // Uso del CFDI, viene de CUsoCFDI
            'lugarExpedicion' => $this->emisor->getCodigo(),                        // Codigo Postal
            'conceptos' => $conceptos,                                              // Conceptos amparados por el CFDI
            'impuestos' => array(
                        'traslados' => $impuestos,
                        'TotalImpuestosTrasladados' => $RIVATotal 
                                + ($this->desgloseIEPS==TRUE ? $RIEPSTotal : 0),
                        'TotalImpuestosRetenidos' => 0.00));
        return $cfdi;
    }//getCFDIJson
    
    public function __toString() {
        return  "NotaCreditoVO={emisor=".$this->emisor
                    .",receptor=".$this->receptor
                    .",comprobante=".$this->comprobante
                    .",conceptos=".print_r($this->conceptos, true)
                    .",relacion=".$this->relacion;
    }

}//FacturaVO
