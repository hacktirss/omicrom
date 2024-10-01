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
 
    require_once ('cfdi33/Comprobante.php');
    require_once ('ReciboPagoDAO.php');
    require_once ('mysqlUtils.php');

    class ReciboElectronicoDePago {

        /* @var $comprobante \com\softcoatl\cfdi\v33\schema\Comprobante */
        private $comprobante;

        function __construct($idPago) {
            $reciboPagoDAO = new ReciboPagoDAO($idPago);
            $this->comprobante = $reciboPagoDAO->getComprobante();
        }//constructor

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

    }//FacturaDetisa
}//com\detisa\omicrom