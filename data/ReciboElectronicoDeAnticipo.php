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
    require_once ('ReciboAnticipoDAO.php');
    require_once ('mysqlUtils.php');

    class ReciboElectronicoDeAnticipo {

        /* @var $comprobante \com\softcoatl\cfdi\v40\schema\Comprobante40 */
        private $comprobante;

        function __construct($idPago) {
            $reciboPagoDAO = new ReciboAnticipoDAO($idPago);
            $this->comprobante = $reciboPagoDAO->getComprobante();
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

    }//FacturaDetisa
}//com\detisa\omicrom