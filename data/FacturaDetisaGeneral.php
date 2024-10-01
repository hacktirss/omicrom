<?php

/*
 * FacturaDetisa
 * detifac®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since dic 2017
 */

namespace com\detisa\omicrom {
 
    require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
    require_once ('FacturaGeneralDAO.php');
    require_once ('mysqlUtils.php');

    class FacturaDetisaGeneral {

        private $facturaDAO;
        private $comprobante;
        private $comprobanteTimbrado;

        function __construct($idFactura) {

            $this->facturaDAO = new FacturaGeneralDAO($idFactura);
            $this->comprobante = $this->facturaDAO->getComprobante();
        }

        /**
         * 
         * @return \com\softcoatl\cfdi\v40\schema\Comprobante
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
            
            $this->facturaDAO->updateFC($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
            $this->facturaDAO->updateRM($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
            $this->facturaDAO->updateVTA($this->comprobanteTimbrado->getFolio(), $this->comprobanteTimbrado->getTimbreFiscalDigital()->getUUID());
        }
        
        function save($clavePAC) {

            $this->facturaDAO->insertFactura($this->comprobanteTimbrado, $clavePAC);
        }
    }
}