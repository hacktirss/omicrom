<?php

include_once ('mysqlUtils.php');
include_once ('UsuarioPwdVO.php');
/**
 * Description of UsuarioPwdDAO
 *
 * @author 3PX89LA_RS5
 */
class UsuarioPwdDAO {
    private $conn;
    
    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }
    
    /**
     * 
     * @param type $idUsuario = Id User
     * @return \UsuarioPwdVO
     */
    public function retrieve($idUsuario) {
        $usuarioPwdVO = new UsuarioPwdVO;
        $sql = "SELECT * FROM authuser_pwd WHERE id_user = " . $idUsuario;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $usuarioPwdVO->setId($rs['id']);
            $usuarioPwdVO->setIdUsuario($rs['id_user']);
            $usuarioPwdVO->setPassword($rs['passwd']);
            $usuarioPwdVO->setCreation($rs['creation']);
            $usuarioPwdVO->setActive($rs['active']);
        }
        error_log($usuarioPwdVO);
        return $usuarioPwdVO;
    }
    
    /**
     * 
     * @param type $usuarioPwdVO = UsuarioPwdVO
     * @return new Id User
     */
    public function create($usuarioPwdVO) {
        $sql = "INSERT INTO authuser_pwd ("
            . "id_user, "
            . "passwd, "
            . "creation "
            . ") "
            . "VALUES(?, MD5(?), NOW())";
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("ss", 
                $usuarioPwdVO->getIdUsuario(),
                $usuarioPwdVO->getPassword()
                );
            $id = $ps->execute() ? $ps->insert_id : -1;
            error_log(mysqli_error($this->conn));
            $ps->close();
        }
        return $id;
    }
    
    /**
     * 
     * @param type $idUsuario = UsuarioPwdVO
     * @return true o false
     */
    public function update($idUsuario) {
        $sql = "UPDATE authuser_pwd SET "
            . "active = 0 "
            . "WHERE id_user = ? ";
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("s", 
                $idUsuario
                );
            return $ps->execute();
        }
    }
    
    /**
     * 
     * @param type $idUsuario = Id User
     * @return true o false
     */
    public function remove($idUsuario) {
        $sql = "DELETE FROM authuser_pwd WHERE id_user = ? ";
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("s",
                $idUsuario
                );
            return $ps->execute();
        }
    }
    
    /**
     * 
     * @param type $usuarioPwdVO = UsuarioPwdVO
     * @return int = number of matches
     */
    public function countPassword($usuarioPwdVO) {
        $count = 0;
        $sql = "SELECT COUNT(*) num FROM authuser_pwd "
             . "WHERE id_user = " . $usuarioPwdVO->getIdUsuario() . " "
             . "AND passwd = MD5('" . $usuarioPwdVO->getPassword() . "')";
     
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $count = $rs['num'];
        }

        return $count;
    }
}
