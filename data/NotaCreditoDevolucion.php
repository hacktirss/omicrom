<?php

/*
 * NotaCreditoDevolucion
 * detifac®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since dic 2017
 */

namespace com\detisa\omicrom;
 
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
//require_once ('NotaCreditoDAO.php');
require_once ('NotaCreditoDevolucionDAO.php');
require_once ('mysqlUtils.php');

class NotaCreditoDevolucion {

    /* @var $comprobante \com\softcoatl\cfdi\v33\schema\Comprobante */
    private $comprobante;

    function __construct($idNotaDeCredito) {

        $notaCreditoDAO = new NotaCreditoDevolucionDAO($idNotaDeCredito);
        $this->comprobante = $notaCreditoDAO->getComprobante();
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

}//NotaCreditoDevolucion