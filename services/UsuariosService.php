<?php

#Librerias
include_once ('data/UsuarioDAO.php');
include_once ('data/BitacoraDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "configusers.php?";

$usuarios = new Usuarios();
$BitacoraDAO = new BitacoraDAO();
$BitacoraVO = new BitacoraVO();
if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $usuarioVO = new UsuarioVO();
    $usuarioVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($usuarioVO->getId())) {
        $usuarioVO = $usuarios->getUser($usuarioVO->getId());
    }

    $usuarioVO->setNombre($sanitize->sanitizeString("Name"));
    if (strlen($sanitize->sanitizeString("SCliente")) > 3) {
        $VarDesc = explode(".", $sanitize->sanitizeString("SCliente"));
        $usuarioVO->setNombre($VarDesc[0] . ".- " . $usuarioVO->getNombre());
        $usuarioVO->setRol(8);
        $usuarioVO->setLevel(2);
    } else {
        $usuarioVO->setRol($sanitize->sanitizeInt("Rol"));
        ($usuarioVO->getRol() == 1) ? $usuarioVO->setLevel(8) : $usuarioVO->setLevel(6);
    }
    $usuarioVO->setUsername($sanitize->sanitizeString("Uname"));
    $usuarioVO->setMail($sanitize->sanitizeEmail("Mail"));
    $usuarioVO->setStatus($sanitize->sanitizeString("Status"));

    $team = utils\IConnection::execSql("SELECT perfil FROM authuser_rol WHERE id = " . $usuarioVO->getRol());
    $usuarioVO->setTeam($team["perfil"]);

    //error_log(print_r($request, TRUE));
    try {

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $usuarioVO->setPassword($sanitize->sanitizeString("Passwd"));

            $response = $usuarios->addUser($usuarioVO);
            if ($response === Usuarios::RESPONSE_VALID) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CREACION USUARIO: " . $usuarioVO->getNombre());
            } else {
                $Msj = $response;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {

            $usuarioVO = $usuarios->getUser($usuarioVO->getId());
            $CountBit = utils\IConnection::execSql("SELECT count(0) no FROM bitacora_eventos WHERE usuario LIKE '%" . $usuarioVO->getNombre() . "%' "
                            . "OR usuario LIKE '%" . $usuarioVO->getUsername() . "%'");

            if ($CountBit["no"] == 0) {
                $usuarioVO->setNombre($sanitize->sanitizeString("Name"));
                $usuarioVO->setUsername($sanitize->sanitizeString("Uname"));
                $Msj = "";
            } else {
                $Msj = "<br>El usuario ya tiene registros en bitacora de eventos y el nombre ni el usuario es posible modificar!";
            }
            $usuarioVO->setMail($sanitize->sanitizeEmail("Mail"));
            $usuarioVO->setRol($sanitize->sanitizeInt("Rol"));
            $usuarioVO->setStatus($sanitize->sanitizeString("Status"));
            $usuarioVO->setTeam($team["perfil"]);
            $response = $usuarios->updateUser($usuarioVO);
            if ($response === Usuarios::RESPONSE_VALID) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE . " " . $Msj;
                error_log("LLEGAAAAAAAAAAAAAAAAAAA______________");
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION USUARIO: " . $usuarioVO->getNombre());
            } else {
                $Msj = $response . " " . $Msj;
            }
        } elseif ($request->getAttribute("Boton") === "Cambiar contraseña") {
            $usuarioVO->setPassword($sanitize->sanitizeString("Passwd"));

            $response = $usuarios->changePasswordUser($usuarioVO);
            if ($sanitize->sanitizeString("Passwd") === $sanitize->sanitizeString("PasswdC")) {
                if ($response === Usuarios::RESPONSE_VALID) {
                    $Msj = "Se ha cambiado la contraseña con EXITO!";
                    $usuarioDAO = new UsuarioDAO();
                    $usuarioVO = $usuarioDAO->retrieve($usuarioVO->getId());
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIO DE CONTRASEñA Usr :"
                            . $usuarioVO->getId() . " " . $usuarioVO->getNombre());
                } else {
                    $Msj = $response;
                }
            } else {
                $Msj = "Error: Las contraseñas tienen que ser la misma, favor de verificar";
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en usuarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->getAttribute("BotonCli") === "Actualiza Status") {
    $usuarioVO = new UsuarioVO();
    $usuarioDAO = new UsuarioDAO();
    $usuarioVO = $usuarioDAO->retrieve($sanitize->sanitizeInt("busca"));
    $usuarioVO->setStatus($sanitize->sanitizeString("Status"));
    if ($usuarioDAO->update($usuarioVO)) {
        BitacoraDAO::getInstance()->saveLog($usuarioVO->getNombre(), "ADM", "ACTUALIZACION USUARIO: " . $usuarioVO->getNombre(), $usuarioVO->getNombre());
        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
    } else {
        $Msj = utils\Messages::RESPONSE_ERROR;
    }
}

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE) {
            $selectPermisos = "SELECT * FROM authuser_cnf WHERE id_user = " . $sanitize->sanitizeInt("busca");
            $rows = utils\IConnection::getRowsFromQuery($selectPermisos);

            foreach ($rows as $row) {
                $permisos = returnSelected($row["id_menu"]);
                $updateCnf = "UPDATE authuser_cnf SET editable = RPAD('$permisos', 32, 0) WHERE id = " . $row["id"];
                //error_log($updateCnf);
                if ($mysqli->query($updateCnf)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en usuarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("Cambiar") && $request->getAttribute("Cambiar") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "cambio_pass.php?";

    $usuarioVO = new UsuarioVO();
    $usuarioVO->setId($usuarioSesion->getId());
    if (is_numeric($usuarioVO->getId())) {
        $usuarioVO = $usuarios->getUser($usuarioVO->getId());
    }
    //error_log(print_r($usuarioVO, TRUE));
    try {

        if ($request->getAttribute("Cambiar") === "Cambiar contraseña") {
            $currentPassword = utils\HTTPUtils::getSessionValue(Usuarios::SESSION_PASSWORD);

            if (md5($request->getAttribute("actual")) === $currentPassword) {
                $usuarioVO->setPassword($request->getAttribute("nuevo"));

                $response = $usuarios->changePasswordUser($usuarioVO);
                if ($response === Usuarios::RESPONSE_VALID) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), 'ADM', 'CAMBIO DE CONTRASEñA: ' . $usuarioSesion->getUsername());
                    $Return = "index.php?";
                    $Msj = 4;
                } else {
                    $Msj = $response;
                }
            } else {
                $Msj = "Credenciales invalidas, la contraseña actual ingresada no es correcta.";
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), 'ADM', 'INTENTO DE CAMBIO DE CONTRASEñA: ' . $usuarioSesion->getUsername());
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en usuarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");
    $usuarioVO = $usuarios->getUser($cId);

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $usuarioVO->setLevel(99);
            $usuarioVO->setStatus(StatusUsuario::INACTIVO);
            //error_log(print_r($usuarioVO, TRUE));
            $response = $usuarios->updateUser($usuarioVO);
            if ($response === Usuarios::RESPONSE_VALID) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ELIMINACION DE USUARIO: " . $cId);
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            }
        } elseif ($request->getAttribute("op") === "unlock") {
            $usuarioVO->setLocked(0);
            $usuarioVO->setAlive(0);
            $response = $usuarios->updateUser($usuarioVO);
            if ($response === Usuarios::RESPONSE_VALID) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "DESBLOQUEO DE USUARIO: " . $cId);
                $Msj = "Registro desbloqueado con Exito!";
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en usuarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

/**
 * 
 * @global utils\IConnection $mysqli
 * @global utils\HTTPUtils $request
 * @param int $idSubmenu
 * @return int
 */
function returnSelected($idSubmenu) {
    global $mysqli, $request;
    $i = 0;
    $arreglo = $var = null;

    $selectSubmenu = "
            SELECT menus.nombre,submenus.submenu,submenus.id 
            FROM submenus 
            LEFT JOIN menus ON menus.id=submenus.menu 
            WHERE menus.id = $idSubmenu 
            ORDER BY submenus.posicion";
    $sesult = $mysqli->query($selectSubmenu);

    while ($rg = $sesult->fetch_array()) {
        $arreglo[$i] = 0;
        $submenu = str_replace(" ", "_", $rg[submenu]) . $rg[id];
        if ($request->hasAttribute($submenu)) {
            $arreglo[$i] = 1;
        }
        $var .= $arreglo[$i];
        $i++;
    }

    return $var;
}
