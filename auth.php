<?php

#Librerias
include_once ("data/mysqlUtils.php");
include_once ("./libnvo/Usuarios.php");

class Auth {

    const RESPONSE_VALID = "OK";
    const IS_EXPIRED = 0;

    private $conn;
    private $usuarios;
    private $username;
    private $password;

    function __construct() {
        $this->conn = getConnection();
        $this->usuarios = new Usuarios();
    }

    function __destruct() {
        $this->conn->close();
        $this->usuarios = null;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    /**
     * 
     * @return UsuarioVO
     */
    public function authenticate() {
        $usuarioVO = $this->usuarios->login($this->username, $this->password);

        if ($usuarioVO == null) {
            return null;
        } else {
            if ($usuarioVO->getCount() > 0) {
                $this->usuarios->loginLastAccess($usuarioVO);
            }
            return $usuarioVO;
        }
    }

    /**
     * OK if result is success
     * @return string 
     */
    public function page_check() {
        $usuarioVO = $this->usuarios->check($this->username, $this->password);
        if ($usuarioVO == null || $usuarioVO->getAlive() == StatusSesion::DEAD) {
            return null;
        }
        $this->usuarios->lastActivity($usuarioVO);
        return self::RESPONSE_VALID;
    }

    /**
     * FALSE if still is validity
     * @param type $usuarioVO = UsuarioVO
     * @return boolean 
     */
    public function isExpired($usuarioVO) {
        if ($usuarioVO != null) {
            $currentDate = strtotime(date("Y-m-d"));
            $login = strtotime($usuarioVO->getCreation());
            if ($currentDate > $login) {
                return true;
            }
        }
        return false;
    }

}
