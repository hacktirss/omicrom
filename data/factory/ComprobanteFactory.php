<?php

/*
 * Comprobante40DAO
 * GlobalFAE®
 * © 2018, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since feb 2018
 */

namespace com\omicrom\cfdi\factory;

use com\detisa\cfdi\factory\Comprobante40Factory;

class ComprobanteFactory {

    public static function getFactory($vCfdi) {
        switch ($vCfdi) {
            case "v4":
                return new Comprobante40Factory();
                break;
        }
    }

}
