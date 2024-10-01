<?php

include_once 'data/UsuarioDAO.php';

class Usuarios {

    const LEN_PASSWORD = 8;
    const REPEAT_PASSWORD = 1; //Numero de veces que se puede repetir una contrasenia
    const VALIDITY = 6; //Numero de meses que durara la contraseña activa
    const RESPONSE_VALID = "OK";
    const SPECIAL_CHARS = "$&*@#?<>_-";
    const MAX_INTENTS_LOGIN = 5; //Número máximo de intentos permitidos para una cuenta
    const SESSION_USERNAME = "U";
    const SESSION_PASSWORD = "P";

    private $usuarioVO;
    private $usuarioDAO;
    private $usuarioPwdVO;
    private $usuarioPwdDAO;

    function __construct() {
        $this->usuarioDAO = new UsuarioDAO();
        $this->usuarioPwdDAO = new UsuarioPwdDAO();
    }

    /**
     * 
     * @return UsuarioVO
     */
    function getUsuarioVO() {
        return $this->usuarioVO;
    }

    /**
     * Crea un nuevo usuario y su perfil correspondiente
     * @param UsuarioVO $usuarioVO
     * @return string Respuesta del proceso
     */
    public function addUser($usuarioVO) {
        if ($this->usuarioDAO->findByUname($usuarioVO->getUsername()) == null) {
            $response = $this->validatePassword($usuarioVO);
            if ($response === self::RESPONSE_VALID) {
                if ($this->usuarioDAO->create($usuarioVO) > 0) {
                    return self::RESPONSE_VALID;
                } else {
                    return "Ocurrio un error al agregar el usuario.";
                }
            } else {
                return $response;
            }
        } else {
            return "El nombre de usuario solicitado ya esta en uso.";
        }
    }

    /**
     * Actualiza los datos del usuario y su perfil
     * @param UsuarioVO $usuarioVO
     * @return string Respuesta del proceso
     */
    public function updateUser($usuarioVO) {
        if ($this->usuarioDAO->findByUname($usuarioVO->getUsername(), $usuarioVO->getId()) == null) {
            if ($this->usuarioDAO->update($usuarioVO)) {
                return self::RESPONSE_VALID;
            } else {
                return "Ocurrio un error al actualizar el usuario";
            }
        } else {
            return "El nombre de usuario solicitado ya esta en uso.";
        }
    }

    /**
     * Elimina un usuario especifico, asi como su perfil e historal de contraseñas.
     * @param int $idUsuario
     */
    public function removeUser($idUsuario) {
        if ($this->usuarioDAO->remove($idUsuario)) {
            return self::RESPONSE_VALID;
        } else {
            return "Ocurrio un error al eliminar el usuario";
        }
    }

    /**
     * Obtiene los datos de usuario y perfil
     * @param int $idUsuario
     * @return UsuarioVO
     */
    public function getUser($idUsuario) {
        return $this->usuarioDAO->retrieve($idUsuario);
    }

    /**
     * Obtiene los datos de usuario y perfil
     * @param string $uname
     * @return UsuarioVO
     */
    public function getUserByName($uname) {
        return $this->usuarioDAO->findByUname($uname);
    }

    /**
     * 
     * @return array Obtiene todos los usuarios activos
     */
    public function getAllUsers() {
        return $this->usuarioDAO->getAll();
    }

    /**
     * 
     * @param string $name Nombre del usuario
     * @param string $password Contraseña del usuario
     * @return UsuarioVO
     */
    public function login($name, $password) {
        return $this->usuarioDAO->finfByUnameAndPasswordEncrypt($name, $password);
    }

    /**
     * Retorna usuario en sesion
     * @param string $name
     * @param string $password
     * @return UsuarioVO
     */
    public function check($name, $password) {
        return $this->usuarioDAO->finfByUnameAndPasswordEncrypt($name, $password);
    }

    /**
     * 
     * @param UsuarioVO $usuarioVO
     * @return boolean
     */
    public function lastActivity($usuarioVO) {
        return $this->usuarioDAO->updateLastActivity($usuarioVO);
    }

    /**
     * Actualiza los datos del ultimo acceso del usuario
     * @param UsuarioVO $usuarioVO
     * @return string Mensaje de operacion
     */
    public function loginLastAccess($usuarioVO) {
        if ($this->usuarioDAO->updateLastLogin($usuarioVO)) {
            return self::RESPONSE_VALID;
        } else {
            return "Ocurrio un error al actualizar el usuario";
        }
    }

    public function deadSession($usuarioVO) {
        if ($this->usuarioDAO->updateAlive($usuarioVO)) {
            return self::RESPONSE_VALID;
        } else {
            return "Ocurrio un error al actualizar el usuario";
        }
    }

    /**
     * 
     * @param UsuarioVO $usuarioVO
     * @param int $conteo Indica si aumenta el logincount
     * @return string Mensaje de operación
     */
    public function changePasswordUser($usuarioVO, $counteo = 0) {
        $response = $this->validatePassword($usuarioVO);
        if ($response === self::RESPONSE_VALID) {
            if ($this->usuarioDAO->changePassword($usuarioVO, $counteo)) {
                $response = self::RESPONSE_VALID;
            } else {
                $response = "A ocurrido un error al cambiar su contraseña.";
            }
        }

        return $response;
    }

    /**
     * 
     * @param UsuarioVO $usuarioVO
     * @return string Mensaje de validación
     */
    public function validatePassword($usuarioVO) {
        $response = self::RESPONSE_VALID;
        $pPassword = $usuarioVO->getPassword();
        $patternLower = '/[a-z]+/m';
        $patternUpper = '/[A-Z]+/m';
        $patternNumber = '/[0-9]+/m';
        $patternSpecial = "[" . self::SPECIAL_CHARS . "]+";

        if (self::REPEAT_PASSWORD > 0) {
            $this->usuarioPwdVO = new UsuarioPwdVO();
            $this->usuarioPwdVO->setIdUsuario($usuarioVO->getId());
            $this->usuarioPwdVO->setPassword($usuarioVO->getPassword());
            $count = $this->usuarioPwdDAO->countPassword($this->usuarioPwdVO);
            if ($count >= self::REPEAT_PASSWORD) {
                $response = "La contraseña ya ha sido utilizada con anterioridad.";
            }
        }

        if (strlen($pPassword) < self::LEN_PASSWORD) {
            $response = "Contraseña invalida, la longitud minima es " + self::LEN_PASSWORD;
        }

        if (!preg_match($patternLower, $pPassword)) {
            $response = "Contraseña invalida, debe contener al menos una minuscula";
        }

        if (!preg_match($patternUpper, $pPassword)) {
            $response = "Contraseña invalida, debe contener al menos una mayuscula";
        }

        if (!preg_match($patternNumber, $pPassword)) {
            $response = "Contraseña invalida, debe contener al menos un numero";
        }

        if (!$this->validateSpecialChars($patternSpecial, $pPassword)) {
            $response = "Contraseña invalida, debe contener al menos un caracter especial";
        }
        //error_log("Response valid password:" . $response);
        return $response;
    }

    public function validateSpecialChars($pattern, $string) {
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            if (strpos($pattern, $char) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function lineamientosPassword() {
        $html = "";
        $html .= "<div id='lineamientos' style='text-align: left;'>";
        $html .= "<strong> Lineamientos para una Contraseña Segura </strong>";
        $html .= "<ul>";
        $html .= "<li> Tener una longitud de, al menos, 8 caracteres.</li>";
        $html .= "<li> Combinar letras, números y caracteres especiales</li>";
        $html .= "<li> Deberá contener al menos una mayúscula y una minúscula.</li>";
        $html .= "<li> Deberá contener al menos un número y un carácter especial. <br/>Caracteres permitidos: <strong>" . str_replace("\\", "", self::SPECIAL_CHARS) . "</strong></li>";
        //$html .= "<li> Las letras no deberán formar palabras conocidas.</li>";
        //$html .= "<li> Los número no deberán ser de secuencias conocidas, como la fecha de nacimiento o de aniversario.</li>";
        //$html .= "<li> Se podrá utlizar la misma contraseña hasta " . self::REPEAT_PASSWORD . " veces.</li>";
        //$html .= "<li> No escribir su contraseña en ningun medio de respaldo: papel, archivo electrónico, celular, etc.</li>";
        //$html .= "<li> No transmitir una contraseña propia a nadie, por ningun medio, ni de papel, ni electrónico.</li>";
        $html .= "<li> No podrá utilizar contraseñas anteriores</li>";
        $html .= "<li> La vigencia de la contraseña será de " . self::VALIDITY . " meses.</li>";
        $html .= "</ul>";
        $html .= "</div>";

        echo $html;
    }

    public static function comboUsuarios($nombreSelect, $adicional = "", $AddStyle = "") {
        $html = "";
        $usuarios = new Usuarios();
        $array = $usuarios->getAllUsers();
        $html = "&nbsp;<select name='" . $nombreSelect . "' id='" . $nombreSelect . "' class='texto_tablas' $AddStyle>";
        if ($adicional !== "") {
            $html .= "<option value='" . $adicional . "'> " . $adicional . " </option>";
        }
        foreach ($array as $item) {
            $html .= "<option value='" . $item->getId() . "'> " . $item->getNombre() . " </option>";
        }
        $html .= "</select>";
        return $html;
    }

}
