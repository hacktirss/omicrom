<?php

use com\softcoatl\utils as utils;

/**
 * Description of SanitizeUtil
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 * 
 * Esta extensión filtra los datos bien sea para validarlos o para sanearlos. 
 * Es especialmente útil cuando los datos de origen contienen datos desconocidos (o externos), 
 * como entradas facilitadas por el usuario. Por ejemplo, estos datos pueden venir de un formulario HTML.
 *
 * Principalmente hay dos tipos de filtrado: validación y saneamiento.
 *
 * La validación se usa para validar o comprobar si los datos cumplen ciertos requisitos. 
 * Por ejemplo, pasándole FILTER_VALIDATE_EMAIL determinará si los datos son una dirección de correo válida, 
 * pero no realizará ningún cambio en los datos.
 *
 * El saneamiento limpiará los datos, de modo que los modificará eliminando los caracteres no deseados. 
 * Por ejemplo, pasándole FILTER_SANITIZE_EMAIL eliminará los caracteres que no son apropiados 
 * para una dirección de correo electrónico. Sin embargo, no valida los datos.
 * 
 * Las banderas se usan opcionalmente tanto con la validación como con el saneamiento para adaptar 
 * el comportamiento según las necesidades. 
 * Por ejemplo, al pasarle FILTER_FLAG_PATH_REQUIRED mientras se filtra un URL, 
 * se obliga a que esté presente una ruta (como /foo en http://example.org/foo).
 */
//Singleton class
class SanitizeUtil {

    //private static $instance = null;
    private $request;

    public static function getInstance() {
        /* if (self::$instance == null):
          self::$instance = new SanitizeUtil();
          endif;
          return self::$instance; */
        return new SanitizeUtil();
    }

    public function __construct() {
        $this->request = utils\HTTPUtils::getRequest();
    }

    /**
     * Devuelve TRUE para "1", "true", "on" y "yes". 
     * Devuelve FALSE en caso contrario.
     *
     * Si FILTER_NULL_ON_FAILURE está declarado, se devolverá FALSE sólo para 
     * "0", "false", "off", "no", y "", y NULL para cualquier valor no booleano.
     * 
     * @opciones default
     * @banderas FILTER_NULL_ON_FAILURE
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeBoolean($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_STRING);
            $var = filter_var($value, FILTER_VALIDATE_BOOLEAN, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida una dirección de correo electrónico.
     *
     * En general, se valildan direcciones de correo electrónico con la sintaxis de RFC 822, 
     * con la excepción de no admitir el plegamiento de comentarios y espacios en blanco.
     * 
     * @opciones default
     * @banderas 
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeEmail($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_EMAIL);
            $var = filter_var($value, FILTER_VALIDATE_EMAIL, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * 	Valida si el valor es un float.
     *
     * @opciones default,decimal
     * @banderas FILTER_FLAG_ALLOW_THOUSAND
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeFloat($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $var = filter_var($value, FILTER_VALIDATE_FLOAT, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida un valor como integer, opcionalmente desde el rango especificado, 
     * y lo convierte a int en case de éxito.
     *
     * @opciones default, min_range, max_range
     * @banderas FILTER_FLAG_ALLOW_OCTAL, FILTER_FLAG_ALLOW_HEX
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeInt($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_NUMBER_INT);
            $var = filter_var($value, FILTER_VALIDATE_INT, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
                //$var = 0;
            }
        }
        return $var;
    }

    /**
     * Valida si es valor es una dirección IP, opcionalmente se puede indicar 
     * que sea sólo IPv4 o IPv6 o que no sea de rangos privados o reservados.
     *
     * @opciones default
     * @banderas FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE, 
     *           FILTER_FLAG_NO_RES_RANGE
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeIP($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_STRING);
            $var = filter_var($value, FILTER_VALIDATE_IP, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida una dirección MAC.
     *
     * @opciones default
     * @banderas
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeMAC($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_STRING);
            $var = filter_var($value, FILTER_VALIDATE_MAC, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida el valor contra regexp, una expresión regular Perl-compatible.
     *
     * @opciones default,regexp
     * @banderas
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeRegexp($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_STRING);
            $var = filter_var($value, FILTER_VALIDATE_REGEXP, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida si su valor es una URL (de acuerdo con » http://www.faqs.org/rfcs/rfc2396), 
     * opcionalmente con componentes necesarios. Se ha de tener cuidado ya que un URL válida 
     * podría no especificar el protocolo HTTP http://, 
     * por lo que podrían ser necesarias validaciones posteriores para determinar que 
     * el URL utiliza un protocolo esperado, p.ej., ssh:// o mailto:. 
     * Nótese que esta función sólo buscará para ser validadas URLs ASCII; 
     * los nombres de dominio internacionales (que contienen no-ASCII caracteres) 
     * fallarán en la validación.
     *
     * @opciones default
     * @banderas FILTER_FLAG_PATH_REQUIRED, FILTER_FLAG_QUERY_REQUIRED
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeUrl($variable_name, $options = null) {
        try {
            $value = filter_var($this->request->getAttribute($variable_name), FILTER_SANITIZE_URL);
            $var = filter_var($value, FILTER_VALIDATE_URL, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return $var;
    }

    /**
     * Valida cualquier cadena recuperada.
     *
     * @opciones default
     * @banderas FILTER_FLAG_NO_ENCODE_QUOTES, FILTER_FLAG_STRIP_LOW, 
     * FILTER_FLAG_STRIP_HIGH, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_HIGH, 
     * FILTER_FLAG_ENCODE_AMP
     * 
     * @param String $variable_name Nombre de la variable a revisar.

     * @param Array $options Conjunto asociativo de opciones o disyunción bit a bit de banderas. 
     * Si el filtro acepta opciones, se pueden proporcionar banderas en el campo "banderas" de la matriz. 
     * @return type Parametro validado
     */
    public function sanitizeString($variable_name, $options = null) {
        if(is_null($options)){
            $options = "UTF-8";
        }
        try {
            $value = $this->request->getAttribute($variable_name);
            //error_log($value);
            $var = htmlspecialchars ($value, ENT_QUOTES, $options);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if (empty($var)) {
                error_log("Input[$variable_name] Parameter Invalid (" . $this->request->getAttribute($variable_name) . ")");
            }
        }
        return trim($var);
    }

    /**
     * Convierte todas las entidades HTML a sus caracteres correspondientes
     * 
     * @charset UTF-8
     * 
     * @param type $variable cadena a convertir
     * @return type $variable transformada 
     */
    public function decodeString($variable) {
        try {
            $var = html_entity_decode($variable, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
            //error_log($var);
        } catch (Exception $ex) {
            error_log($ex);
        }
        return $var;
    }

}
