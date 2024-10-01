<?php

include_once ('mysqlUtils.php');
include_once ('UsuarioVO.php');
include_once ('UsuarioPwdDAO.php');
include_once ('UsuarioPerfilDAO.php');
include_once ('BasicEnum.php');

/**
 * Description of UsuarioDAO
 *
 * @author Tirso Bautista Anaya
 */
class UsuarioDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "authuser";
    const LEVEL_MASTER = 9;

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return UsuarioVO
     */
    public function create($objectVO, $isUser = true) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "name, "
                . "uname, "
                . "passwd, "
                . "rol, "
                . "team, "
                . "level, "
                . "status, "
                . "lastlogin,"
                . "lastactivity,"
                . "feclave, "
                . "logincount, "
                . "mail "
                . ") "
                . "VALUES(?, ?, MD5(?), ?, ?, ?, ?, NOW(), NOW(), CURRENT_DATE(), 0, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssissss",
                    $objectVO->getNombre(),
                    $objectVO->getUsername(),
                    $objectVO->getPassword(),
                    $objectVO->getRol(),
                    $objectVO->getTeam(),
                    $objectVO->getLevel(),
                    $objectVO->getStatus(),
                    $objectVO->getMail()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
            } else {
                error_log($this->conn->error);
                $ps->close();
            }
        }

        if ($isUser) {
            $objectVO->setId($id);
            if ($id > 0) {
                $usuarioPwdDAO = new UsuarioPwdDAO();
                $usuarioPwdVO = new UsuarioPwdVO();
                $usuarioPwdVO->setIdUsuario($id);
                $usuarioPwdVO->setPassword($objectVO->getPassword());
                $usuarioPwdDAO->create($usuarioPwdVO);
            }

            if ($id > 0) {
                return $this->createProfileUser($objectVO->getId());
            }
        }
        return $id;
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @param int $count
     * @return boolean
     */
    public function changePassword($objectVO, $count = 0) {
        $sql = "UPDATE authuser "
                . "SET passwd = MD5(?),"
                . "feclave = ADDDATE(CURDATE(), INTERVAL " . Usuarios::VALIDITY . " MONTH),"
                . "locked = 0,"
                . "alive = 0,"
                . "logincount = logincount + $count "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("si", $objectVO->getPassword(), $objectVO->getId()
            );
            if ($ps->execute()) {
                error_log("Change password [ " + $objectVO->getId() + " ]");
                $usuarioPwdDAO = new UsuarioPwdDAO();
                $usuarioPwdVO = new UsuarioPwdVO();
                $usuarioPwdVO->setIdUsuario($objectVO->getId());
                $usuarioPwdVO->setPassword($objectVO->getPassword());
                $usuarioPwdVO->setCreation($this->getCurrentDate());
                if ($usuarioPwdDAO->update($objectVO->getId())) {
                    if ($usuarioPwdDAO->create($usuarioPwdVO) > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 
     * @param array $rs
     * @return \UsuarioVO
     */
    public function fillObject($rs) {
        $objectVO = new UsuarioVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setNombre($rs["name"]);
            $objectVO->setUsername($rs["uname"]);
            $objectVO->setPassword($rs["passwd"]);
            $objectVO->setRol($rs["rol"]);
            $objectVO->setTeam($rs["team"]);
            $objectVO->setLevel($rs["level"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setLastlogin($rs["lastlogin"]);
            $objectVO->setLastactivity($rs["lastactivity"]);
            $objectVO->setCount($rs["logincount"]);
            $objectVO->setCreation($rs["feclave"]);
            $objectVO->setLocked($rs["locked"]);
            $objectVO->setMail($rs["mail"]);
            $objectVO->setAlive($rs["alive"]);
            $objectVO->setDifference($rs["difference"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $username
     * @param int $idObjectVO
     * @return \UsuarioVO
     */
    public function findByUname($username, $idObjectVO = 0) {
        $sql = "SELECT " . self::TABLA . ".*, 0 difference FROM " . self::TABLA . " WHERE uname = '" . $username . "' AND status = '" . StatusUsuario::ACTIVO . "'";
        if ($idObjectVO > 0) {
            $sql .= " AND id != " . $idObjectVO;
        }
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            //error_log($objectVO);
            return $objectVO;
        }
        return null;
    }

    /**
     * 
     * @param string $uname
     * @param string $password
     * @return UsuarioVO
     */
    public function finfByUnameAndPassword($uname, $password) {
        $objectVO = new UsuarioVO();
        $sql = "SELECT " . self::TABLA . ".*, ((authuser.lastactivity + INTERVAL 10 MINUTE) - NOW()) difference FROM " . self::TABLA . " "
                . "WHERE uname = '" . $uname . "' AND passwd = MD5('" . $password . "') AND status = '" . StatusUsuario::ACTIVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            //error_log($objectVO);
            return $objectVO;
        }
        return null;
    }

    /**
     * 
     * @param string $uname
     * @param string $password
     * @return UsuarioVO
     */
    public function finfByUnameAndPasswordEncrypt($uname, $password) {
        $objectVO = new UsuarioVO();
        $sql = "SELECT " . self::TABLA . ".*,0 difference FROM " . self::TABLA . " "
                . "WHERE uname = '" . $uname . "' AND passwd = '" . $password . "' AND status = '" . StatusUsuario::ACTIVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            //error_log($objectVO);
            return $objectVO;
        }
        return null;
    }

    /**
     * 
     * @return array List users active
     */
    public function getAll() {
        $array = array();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " WHERE status = '" . StatusUsuario::ACTIVO . "' AND level < 9 ";
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        }
        return $array;
    }

    public function getCurrentDate() {
        return date("Y-m-d H:i:s");
    }

    /**
     * 
     * @param int $idUsuario
     * @return boolean
     */
    public function remove($idUsuario) {
        $sql = "DELETE FROM authuser WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $idUsuario
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param string $idObjectVO
     * @param string $field
     * @return type
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new UsuarioVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = " . $idObjectVO;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            //error_log($objectVO);
            return $objectVO;
        }
        return null;
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return boolean
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "name = ?, "
                . "uname = ?, "
                . "rol = ?, "
                . "team = ?, "
                . "mail = ?, "
                . "locked = ?, "
                . "alive = ?, "
                . "level = ?, "
                . "status = ?, "
                . "fecha_modificacion = now() "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssissiiisi",
                    $objectVO->getNombre(),
                    $objectVO->getUsername(),
                    $objectVO->getRol(),
                    $objectVO->getTeam(),
                    $objectVO->getMail(),
                    $objectVO->getLocked(),
                    $objectVO->getAlive(),
                    $objectVO->getLevel(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return $this->createProfileUser($objectVO->getId());
            } else {
                error_log($this->conn->error);
            }
        }
        return false;
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return boolean
     */
    public function updateLastLogin($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "lastlogin = NOW(), "
                . "lastactivity = NOW(), "
                . "logincount = logincount + 1, "
                . "locked = 0,"
                . "alive = 1 "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $objectVO->getId());
            return $ps->execute();
        }
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return boolean
     */
    public function updateLocked($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "locked = locked + 1 "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $objectVO->getId());
            return $ps->execute();
        }
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return boolean
     */
    public function updateAlive($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "alive = 0 "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $objectVO->getId());
            return $ps->execute();
        }
    }

    /**
     * 
     * @param UsuarioVO $objectVO
     * @return boolean
     */
    public function updateLastActivity($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "lastactivity = NOW() "
                . "WHERE id = ? ";
        $id = $objectVO->getId();
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $id);
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO
     * @return boolean
     */
    public function createProfileUser($idObjectVO) {
        $sql = "INSERT INTO authuser_cnf (id_user, id_menu, permisos, editable) 
                SELECT authuser.id, menus_perfil.id_menu, menus_perfil.permisos, menus_perfil.permisos editable
                FROM authuser, menus_perfil
                WHERE TRUE 
                AND authuser.rol = menus_perfil.id_rol
                AND authuser.id = ?
                ORDER BY authuser.id, menus_perfil.id_menu
                ON DUPLICATE KEY UPDATE authuser_cnf.permisos = menus_perfil.permisos, authuser_cnf.editable = menus_perfil.permisos;";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $idObjectVO);
            return $ps->execute();
        }
    }

}

abstract class StatusUsuario extends BasicEnum {

    const ACTIVO = "active";
    const INACTIVO = "inactive";

}

abstract class StatusSesion extends BasicEnum {

    const ALIVE = 1;
    const DEAD = 0;

}

abstract class typeTeam extends BasicEnum {

    const ADMINISTRADOR = "Administrador";
    const SUPERVISOR = "Supervisor";
    const OPERADOR = "Operador";
    const AUDITOR = "Auditor";
    const GENERAL = "General";
    const FACTURACION = "Facturacion";
    const CONTABILIDAD = "Contabilidad";
    const CLIENTE = "Cliente";

}

abstract class TipoPermisos extends BasicEnum {

    const UNAVAILABLE = 0;
    const CONDITIONED = 1;
    const FREE = 2;

}
