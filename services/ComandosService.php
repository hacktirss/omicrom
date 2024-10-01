<?php

#Librerias
include_once ('data/CtDAO.php');
include_once ('data/ProductoDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('data/ComandoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "gencomando.php?";

$comandoDAO = new ComandoDAO();

if ($request->hasAttribute("op")) {
    try {
        $busca = $sanitize->sanitizeInt("busca");
        $posicion = $sanitize->sanitizeInt("busca");
        $pesos = $sanitize->sanitizeString("Pesos");
        $centavos = $sanitize->sanitizeString("Centavos");
        $producto = $sanitize->sanitizeString("Producto");
        $fecha = $sanitize->sanitizeString("Fecha");
        $litros = $sanitize->sanitizeInt("Litros");
        $mili_litros = $sanitize->sanitizeInt("Mililitros");
        $tanque = $sanitize->sanitizeBoolean("Tanque");
        $numVentas = $sanitize->sanitizeInt("Numventas");

        $Return .= "busca=" . $busca;

        $selectData = "
                    SELECT man.lado,man.productos,man.isla,estado_posiciones.estado,islas.corte,islas.status,
                    man.marca, listas_valor.dispensario, variables_corporativo.valor longitud, MAX(ROUND(com.precio,2)) precio
                    FROM islas,man
                    LEFT JOIN estado_posiciones ON man.posicion = estado_posiciones.posicion
		    LEFT JOIN com ON TRUE
                    LEFT JOIN (
                        SELECT llave_lista_valor, UPPER(valor_lista_valor) dispensario
                        FROM listas, listas_valor
                        WHERE listas.id_lista = listas_valor.id_lista_lista_valor
                        AND listas.nombre_lista = 'MARCA DISPENSARIOS'
                    ) listas_valor ON listas_valor.llave_lista_valor = man.marca
                    LEFT JOIN variables_corporativo ON TRUE AND llave = 'preset_length'
                    WHERE TRUE AND man.isla = islas.isla AND man.posicion = '$busca'";
        $Variables = utils\IConnection::execSql($selectData);
        $Dispensario = $Variables["dispensario"];

        $LimiteImporte = 9990;
        $LimiteVolumen = (int) ($LimiteImporte / $Variables["precio"]);
        $limite = 4;

        if ($Dispensario === "GILBARCO" || $Dispensario === "TEAM" && $Variables["longitud"] == 7) {
            $LimiteImporte = 99990;
            $LimiteVolumen = (int) ($LimiteImporte / $Variables["precio"]);
            $limite = 5;
        }


        if ($request->getAttribute("op") == 1 || $request->getAttribute("op") == 2) { /* Bloqueo y desbloqueo */
            $operacion = "D";
            if ($request->getAttribute("op") == 1) {
                $operacion = "B";
            }

            $sintaxis = utils\IConnection::execSql("SELECT sintaxis,descripcion FROM lista_comandos WHERE comando = '$operacion'");

            $Comando = $operacion . cZeros($posicion, 2);

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera(1);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion($sintaxis[descripcion]);

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                $Msj = $sintaxis[descripcion];
                if (!($mysqli->query("UPDATE man SET conteoventas = numventas WHERE posicion = '$posicion'"))) {
                    error_log($mysqli->error);
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 3) {/* Cambio de precios */
            $operacion = "P";
            $sintaxis = utils\IConnection::execSql("SELECT sintaxis,descripcion FROM lista_comandos WHERE comando = '$operacion'");

            $Man = utils\IConnection::execSql("SELECT manguera FROM man_pro WHERE posicion = '$posicion' AND producto = '$producto'");

            $Comando = $operacion . cZeros($posicion, 2) . $Man[manguera] . cZeros($pesos, 2) . cZeros($centavos, 2);

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera($Man[manguera]);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion($sintaxis[descripcion]);

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIO DE PRECIO, ID: " . $id);
                $Msj = $sintaxis[descripcion];
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 4) { /* Enviar preset */

            if ($request->getAttribute("Boton") === "Enviar Importe") {
                if ($pesos > 0 && $pesos <= $LimiteImporte) {
                    $operacion = "$";
                    $sintaxis = utils\IConnection::execSql("SELECT sintaxis,descripcion FROM lista_comandos WHERE comando = '$operacion'");

                    $Man = utils\IConnection::execSql("SELECT manguera FROM man_pro WHERE posicion = '$posicion' AND producto = '$producto'");

                    $Comando = $operacion . cZeros($posicion, 2) . $Man[manguera] . cZeros($pesos, $limite) . cZeros($centavos, 2);

                    $comandoVO = new ComandoVO();
                    $comandoVO->setPosicion($posicion);
                    $comandoVO->setManguera($Man[manguera]);
                    $comandoVO->setComando($Comando);
                    $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
                    $comandoVO->setDescripcion("Prefijado");

                    if (($id = $comandoDAO->create($comandoVO)) > 0) {
                        $Msj = $sintaxis[descripcion];
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ENVIO DE PRESET POR IMPORTE, ID: " . $id);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = "El importe es invalido";
                }
            } elseif ($request->getAttribute("Boton") === "Enviar Volumen") {
                if ($litros > 0 && $litros <= $LimiteVolumen) {
                    $operacion = "V";
                    $sintaxis = utils\IConnection::execSql("SELECT sintaxis,descripcion FROM lista_comandos WHERE comando = '$operacion'");

                    $Man = utils\IConnection::execSql("SELECT manguera FROM man_pro WHERE posicion = '$posicion' AND producto = '$producto'");

                    $Comando = $operacion . cZeros($posicion, 2) . $Man[manguera] . cZeros($litros, $limite) . cZeros($mili_litros, 2);

                    $comandoVO = new ComandoVO();
                    $comandoVO->setPosicion($posicion);
                    $comandoVO->setManguera($Man[manguera]);
                    $comandoVO->setComando($Comando);
                    $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
                    $comandoVO->setDescripcion("Prefijado");

                    if (($id = $comandoDAO->create($comandoVO)) > 0) {
                        $Msj = $sintaxis[descripcion];
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ENVIO DE PRESET POR VOLUMEN, ID: " . $id);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = "El volumen es invalido";
                }
            } elseif ($request->getAttribute("Boton") === "Tanque Lleno") {

                if ($tanque) {
                    $operacion = "$";
                    $sintaxis = utils\IConnection::execSql("SELECT sintaxis,descripcion FROM lista_comandos WHERE comando = '$operacion'");

                    $Man = utils\IConnection::execSql("SELECT manguera FROM man_pro WHERE posicion = '$posicion' AND producto = '$producto'");

                    $Comando = $operacion . cZeros($posicion, 2) . $Man[manguera] . cZeros($LimiteImporte, $limite) . cZeros(99, 2);

                    $comandoVO = new ComandoVO();
                    $comandoVO->setPosicion($posicion);
                    $comandoVO->setManguera($Man[manguera]);
                    $comandoVO->setComando($Comando);
                    $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
                    $comandoVO->setDescripcion("Prefijado");

                    if (($id = $comandoDAO->create($comandoVO)) > 0) {
                        $Msj = $sintaxis[descripcion];
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ENVIO DE PRESET POR TANQUE LLENO, ID: " . $id);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = "Error al marcar el check de tanque lleno";
                }
            }
        } else if ($request->getAttribute("op") == 5) { /* Cambiar a modo programacion */
            $operacion = "M";
            $Comando = $operacion . cZeros($posicion, 2);
            $Msj = "Modo programacion";

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera(1);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion($Msj);

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 6) { /* Habilitar posicion */

            if (!($mysqli->query("UPDATE estado_posiciones SET estado = '-' WHERE WHERE posicion = '$posicion'"))) {
                error_log($mysqli->error);
            }
            $Comando = "GL";
            $Msj = "Habilitar";

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera(99);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion($Msj);

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 7) {
            if ($mysqli->query("UPDATE man SET numventas = $numVentas, conteoventas = $numVentas WHERE id = $posicion")) {
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 9) {
            echo "<script language='javascript'>setTimeout('self.close();',100)</script>";
        } else if ($request->getAttribute("op") == 10) {
            $operacion = "F";
            $Comando = $operacion . cZeros($posicion, 2) . "4";

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera(1);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion("400 Pulsos x lt");

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") == 11) {
            $operacion = "F";
            $Comando = $operacion . cZeros($posicion, 2) . "3";

            $comandoVO = new ComandoVO();
            $comandoVO->setPosicion($posicion);
            $comandoVO->setManguera(1);
            $comandoVO->setComando($Comando);
            $comandoVO->setFecha_programada(date("Y-m-d H:i:s"));
            $comandoVO->setDescripcion("323 Pulsos x lt");

            if (($id = $comandoDAO->create($comandoVO)) > 0) {
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } catch (Exception $ex) {
        error_log("Error en envio de comandos: " . $ex);
    } finally {
        if ($mysqli->errno > 0) {
            error_log($mysqli->error);
        }
        if (!is_null($Return)) {
            //header("Location: $Return");
        }
    }
}
