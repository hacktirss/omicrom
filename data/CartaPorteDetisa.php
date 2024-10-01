<?php

/*
 * CartaPorteDetisa
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since dic 2022
 */

namespace com\detisa\omicrom {

    require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
    require_once ('CartaPorteDAO.php');
    require_once ('CartaPorteIngresoDAO.php');
    require_once ('mysqlUtils.php');

    class CartaPorteDetisa {
        /* @var $cartaPorteDAO CartaPorteDAO */

        private $cartaPorteDAO;
        /* @var $comprobante \com\softcoatl\cfdi\v33\schema\Comprobante */
        private $comprobante;
        /* @var $comprobanteTimbrado \com\softcoatl\cfdi\v33\schema\Comprobante */
        private $comprobanteTimbrado;

        function __construct($folio, $origen) {
            if ($origen === "CPI") {
                $this->cartaPorteDAO = new CartaPorteIngresoDAO($folio, $origen);
            } else {
                $this->cartaPorteDAO = new CartaPorteDAO($folio, $origen);
            }
            $this->comprobante = $this->cartaPorteDAO->getComprobante();
        }

        /**
         * 
         * @return \com\softcoatl\cfdi\v33\schema\Comprobante
         */
        function getComprobante() {
            return $this->comprobante;
        }

        function setComprobante($comprobante) {
            $this->comprobante = $comprobante;
        }

        function setComprobanteTimbrado($comprobanteTimbrado) {
            $this->comprobanteTimbrado = $comprobanteTimbrado;
        }

        function update() {

//            $this->facturaDAO->updateFC($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
//            $this->facturaDAO->updateRM($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
//            $this->facturaDAO->updateVTA($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
        }

        function save($clavePAC) {

//            $this->facturaDAO->insertFactura($this->comprobanteTimbrado, $clavePAC);
        }

    }

    //FacturaDetisa
}//com\detisa\omicrom