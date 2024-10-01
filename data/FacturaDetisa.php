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
 
    //require_once ('cfdi33/Comprobante.php');
    require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
    require_once ('FacturaDAO.php');
    require_once ('mysqlUtils.php');

    class FacturaDetisa {

        /* @var $facturaDAO FacturaDAO */
        private $facturaDAO;
        /* @var $comprobante \com\softcoatl\cfdi\v40\schema\Comprobante40 */
        private $comprobante;
        /* @var $comprobanteTimbrado \com\softcoatl\cfdi\v40\schema\Comprobante40 */
        private $comprobanteTimbrado;

        function __construct($idFactura) {

            $this->facturaDAO = new FacturaDAO($idFactura);
            $this->comprobante = $this->facturaDAO->getComprobante();
        }//constructor

        /**
         * 
         * @return \com\softcoatl\cfdi\v40\schema\Comprobante40
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
    }//FacturaDetisa
}//com\detisa\omicrom