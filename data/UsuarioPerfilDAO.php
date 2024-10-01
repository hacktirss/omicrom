<?php

include_once ('mysqlUtils.php');
include_once ('UsuarioPerfilVO.php');
include_once ('UsuarioVO.php');
include_once ('BasicEnum.php');

/**
 * Description of UsuarioPerfilDAO
 *
 * @author 3PX89LA_RS5
 */
class UsuarioPerfilDAO {

    const PERFIL_ADMIN = "Administrador";
    const PERFIL_DEFAULT = "Operador";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * Insert new config 
     * @param UsuarioVO $usuarioVO = UsuarioVO
     * @return int Id Config
     */
    public function create($usuarioVO) {
        $sql = "INSERT INTO conf_users ("
                . "id_user, "
                . "estacion, "
                . "cxc, "
                . "catalogos, "
                . "reportes, "
                . "menuLateral, "
                . "cambioTurno, "
                . "graficas, "
                . "cxp, "
                . "polizas, "
                . "configuracion "
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $usuarioPerfilVO = $this->initPerfil($usuarioVO->getTeam());

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssss",
                    $usuarioVO->getId(),
                    $usuarioPerfilVO->getMenuEstacion(),
                    $usuarioPerfilVO->getMenuCxc(),
                    $usuarioPerfilVO->getMenuCatalogos(),
                    $usuarioPerfilVO->getMenuReportes(),
                    $usuarioPerfilVO->getMenuLateral(),
                    $usuarioPerfilVO->getMenuCambioTurno(),
                    $usuarioPerfilVO->getMenuGraficas(),
                    $usuarioPerfilVO->getMenuCxp(),
                    $usuarioPerfilVO->getMenuPolizas(),
                    $usuarioPerfilVO->getMenuConfiguracion()
            );
            $id = $ps->execute() ? $ps->insert_id : -1;
            error_log(mysqli_error($this->conn));
            $ps->close();
        }
        return $id;
    }
    
    /**
     * Get all config
     * @param int $idUsuario
     * @return \UsuarioPerfilVO
     */
    public function retrieve($idUsuario) {
        $usuarioPerfilVO = new UsuarioPerfilVO;
        $sql = "SELECT * FROM conf_users WHERE id_user = " . $idUsuario;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $usuarioPerfilVO->setId($rs['id']);
            $usuarioPerfilVO->setIdUsuario($rs['id_user']);
            $usuarioPerfilVO->setMenuEstacion($rs['estacion']);
            $usuarioPerfilVO->setMenuCxc($rs['cxc']);
            $usuarioPerfilVO->setMenuCatalogos($rs['catalogos']);
            $usuarioPerfilVO->setMenuReportes($rs['reportes']);
            $usuarioPerfilVO->setMenuLateral($rs['menuLateral']);
            $usuarioPerfilVO->setMenuCambioTurno($rs['cambioTurno']);
            $usuarioPerfilVO->setMenuGraficas($rs['graficas']);
            $usuarioPerfilVO->setMenuCxp($rs['cxp']);
            $usuarioPerfilVO->setMenuPolizas($rs['polizas']);
            $usuarioPerfilVO->setMenuConfiguracion($rs['configuracion']);
        }

        return $usuarioPerfilVO;
    }
    
    /**
     * 
     * @param int $idUsuario
     * @return boolean
     */
    public function remove($idUsuario) {
        $sql = "DELETE FROM conf_users WHERE id_user = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s",
                    $idUsuario
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param UsuarioVO $usuarioVO
     * @return boolean
     */
    public function update($usuarioVO) {
        $usuarioPerfilVO = $this->initPerfil($usuarioVO->getTeam());
        $sql = "UPDATE conf_users SET "
                . "estacion = ?, "
                . "cxc = ?, "
                . "catalogos = ?, "
                . "reportes = ?, "
                . "menuLateral = ?, "
                . "cambioTurno = ?, "
                . "graficas = ?, "
                . "cxp = ?, "
                . "polizas = ?, "
                . "configuracion = ? "
                . "WHERE id_user = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssss",
                    $usuarioPerfilVO->getMenuEstacion(),
                    $usuarioPerfilVO->getMenuCxc(),
                    $usuarioPerfilVO->getMenuCatalogos(),
                    $usuarioPerfilVO->getMenuReportes(),
                    $usuarioPerfilVO->getMenuLateral(),
                    $usuarioPerfilVO->getMenuCambioTurno(),
                    $usuarioPerfilVO->getMenuGraficas(),
                    $usuarioPerfilVO->getMenuCxp(),
                    $usuarioPerfilVO->getMenuPolizas(),
                    $usuarioPerfilVO->getMenuConfiguracion(),
                    $usuarioVO->getId()
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param string $profile
     * @return \UsuarioPerfilVO
     */
    public function initPerfil($profile) {
        $usuarioPerfilVO = new UsuarioPerfilVO;

        if ($profile === "Administrador") {
            $usuarioPerfilVO->setMenuEstacion("1111110111111111100000000");
            $usuarioPerfilVO->setMenuCxc("1111111111111111100000000");
            $usuarioPerfilVO->setMenuCatalogos("1111011000000000000000000");
            $usuarioPerfilVO->setMenuReportes("1110111111111111011110000");
            $usuarioPerfilVO->setMenuLateral("1111111000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("1100000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("1111111111111000000000000");
            $usuarioPerfilVO->setMenuCxp("1111111110000000000000000");
            $usuarioPerfilVO->setMenuPolizas("1111110000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("1111111000000000000000000");
        } elseif ($profile === "Supervisor") {
            $usuarioPerfilVO->setMenuEstacion("1111110111111111100000000");
            $usuarioPerfilVO->setMenuCxc("1111111111111111100000000");
            $usuarioPerfilVO->setMenuCatalogos("1111011000000000000000000");
            $usuarioPerfilVO->setMenuReportes("1110111111111111011110000");
            $usuarioPerfilVO->setMenuLateral("1111111000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("1100000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("1111111111100000000000000");
            $usuarioPerfilVO->setMenuCxp("1111111110000000000000000");
            $usuarioPerfilVO->setMenuPolizas("1111110000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("1111111000000000000000000");
        } elseif ($profile === "Operador") {
            $usuarioPerfilVO->setMenuEstacion("1111110111111111100000000");
            $usuarioPerfilVO->setMenuCxc("1111111111111111100000000");
            $usuarioPerfilVO->setMenuCatalogos("1111011000000000000000000");
            $usuarioPerfilVO->setMenuReportes("1110111111111111011110000");
            $usuarioPerfilVO->setMenuLateral("1111111000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("1100000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("1111111111100000000000000");
            $usuarioPerfilVO->setMenuCxp("1111111110000000000000000");
            $usuarioPerfilVO->setMenuPolizas("0000000000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("0000000000000000000000000");
        } elseif ($profile === "Auditor") {
            $usuarioPerfilVO->setMenuEstacion("1111100111111111100000000");
            $usuarioPerfilVO->setMenuCxc("0000000000000000000000000");
            $usuarioPerfilVO->setMenuCatalogos("1111011000000000000000000");
            $usuarioPerfilVO->setMenuReportes("1110111111111111011110000");
            $usuarioPerfilVO->setMenuLateral("1000100000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("0000000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("1111111111100000000000000");
            $usuarioPerfilVO->setMenuCxp("0000000000000000000000000");
            $usuarioPerfilVO->setMenuPolizas("1111110000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("0000000000000000000000000");
        } elseif ($profile === "General"){
            $usuarioPerfilVO->setMenuEstacion("1111110111111100100000000");
            $usuarioPerfilVO->setMenuCxc("1111111111111111100000000");
            $usuarioPerfilVO->setMenuCatalogos("1111011000000000000000000");
            $usuarioPerfilVO->setMenuReportes("1110111100100000000000000");
            $usuarioPerfilVO->setMenuLateral("1111111000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("1100000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("1111100000000000000000000");
            $usuarioPerfilVO->setMenuCxp("0000000000000000000000000");
            $usuarioPerfilVO->setMenuPolizas("0000000000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("0000000000000000000000000");
        } else{
            $usuarioPerfilVO->setMenuEstacion("1100100000000000100000000");
            $usuarioPerfilVO->setMenuCxc("0000000000000000000000000");
            $usuarioPerfilVO->setMenuCatalogos("0101000000000000000000000");
            $usuarioPerfilVO->setMenuReportes("0000001000000000000000000");
            $usuarioPerfilVO->setMenuLateral("1111011000000000000000000");
            $usuarioPerfilVO->setMenuCambioTurno("1100000000000000000000000");
            $usuarioPerfilVO->setMenuGraficas("0000000000000000000000000");
            $usuarioPerfilVO->setMenuCxp("0000000000000000000000000");
            $usuarioPerfilVO->setMenuPolizas("0000000000000000000000000");
            $usuarioPerfilVO->setMenuConfiguracion("0000000000000000000000000");
        }

        return $usuarioPerfilVO;
    }

}

abstract class PerfilesUsuarios extends BasicEnum {

    const ADMINISTRADOR = "Administrador";
    const SUPERVISOR = "Supervisor";
    const OPERADOR = "Operador";
    const AUDITOR = "Auditor";
    const FACTURACION = "Facturacion";

}