<?php

/**
 * Description of BonificacionDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since ago 2023
 */
include_once ('data/ProductoDAO.php');
include_once ('data/PuntosDAO.php');
include_once ('data/PeriodoPuntosDAO.php');
include_once ('data/Cobranza_beneficiosDAO.php');

use com\softcoatl\utils as utils;

/**
 * Define los difentes comportamientos de las bonificaciónes que se tienen contempladas para el sistema
 */
class BonificacionDAO {
    /*     * Version 1 Omicrom
     * Funcion creada para calcular los puntos generados y consumidos por un cliente
     * Podemos consumir puntos y consultar puntos (consumidos,generados y restantes)
     * 
     * @param int $Cliente Llave primaria o identificador del cliente
     * @param int $Producto Llave primaria del inv
     * @param string $Respuesta Saber que proceso generar y el tipo de respuesta. Valores: 'Consumo','Consulta'
     * @return string
     */

    function calculaBonificacionClientes_vPuntos($Cliente, $Producto, $Respuesta) {
        $PpDAO = new PeriodoPuntosDAO();
        $PpVO = new PeriodoPuntosVO();
        $productoDAO = new ProductoDAO();
        $productoVO = new ProductoVO();

        $productoVO = $productoDAO->retrieve($Producto);
        $PuntosNecesarios = $productoVO->getPrecio();
        $PpVO = $PpDAO->retrieve_vPuntos();
        $PuntosPor = $PpVO->getTipo_concentrado() === TiposCobroDeBeneficio::Importe ? "importe" : "volumen";

        $FechaPuntos = " DATE(rm.fecha_venta) >= DATE ('" . $PpVO->getFecha_inicial() . "') AND ";
        $Fechapunto = "  DATE(fecha) > DATE('" . $PpVO->getFecha_inicial() . "') ";

        $CalculaPuntos = $this->calculaPuntos($PuntosPor, $PpVO->getId(), $Fechapunto, $FechaPuntos, $PpVO->getLimite_inferior(), $Cliente);
        $Calculo = utils\IConnection::execSql($CalculaPuntos);

        $ExisteRegistro = "SELECT cliente FROM saldopuntos WHERE cliente = '$Cliente'";
        $Existencia = utils\IConnection::execSql($ExisteRegistro);
        if ($Respuesta === "Consumo") {
            return $this->utilizaPuntos($Calculo["puntos"], $Calculo["puntosConsumidos"], $PuntosNecesarios, $Cliente, $Producto, $PpVO->getId());
        } else {
            $Array["Puntos"] = $Calculo["puntos"];
            $Array["PuntosConsumidos"] = $Calculo["puntosConsumidos"];
            $Array["PuntosProducto"] = $PuntosNecesarios;
            return $Array;
        }
    }

    /**
     * Sql que obtiene los puntos (Generados,Consumidos, Restantes)
     * 
     * @param string $PuntosPor Tipo de valor que toma de referencia (importe,volumen)
     * @param int $IdPp Llave primaria del periodo_puntos
     * @param string $Fechapunto Periodo de fechas generadas desde la funcion calculaBonificacionClientes_vPuntos()
     * @param string $FechaPuntos Periodo de fechas generadas desde la funcion calculaBonificacionClientes_vPuntos()
     * @param string $LimiteInf Cantida minima para poder juntar el importe de un ticket
     * @param int $Cliente Llave primaria de cli
     * @return string Sql ya armado
     */
    function calculaPuntos($PuntosPor, $IdPp, $Fechapunto, $FechaPuntos, $LimiteInf, $Cliente) {

        $CalculaPuntos = "SELECT ROUND(sum((rm." . $PuntosPor . " * IF(rm.puntos=0,1,rm.puntos))/(SELECT monto_promocion FROM periodo_puntos WHERE id= " . $IdPp . ")),0 ) puntos,"
                . "IFNULL(Pts.smpts,0) puntosConsumidos "
                . "FROM rm LEFT JOIN cli ON cli.id = rm.cliente LEFT JOIN "
                . "(SELECT SUM(puntos) smpts,cliente FROM omicrom.puntos WHERE $Fechapunto "
                . "AND id_periodo = " . $IdPp . "   GROUP  BY cliente) Pts "
                . "ON cli.id = Pts.cliente "
                . "LEFT JOIN cia ON TRUE LEFT JOIN com ON rm.producto = com.clavei WHERE $FechaPuntos  "
                . "cli.id = $Cliente AND rm.importe > " . $LimiteInf . "  GROUP BY cli.id ORDER BY cli.tipodepago;";
        return $CalculaPuntos;
    }

    /**
     * Funcion creada para hacer insert o update a puntos acumulados por el cliente
     * 
     * @param int $CalculoPuntos Total de puntos existentes
     * @param int $CalculoPConsumidos Total de puntos consumidos
     * @param int  $PuntosNecesarios Puntos necesarios para la bonificación
     * @param int $Cliente Id de tabla cli
     * @param int $Producto Id de la tabla inv
     * @param int $Periodo_P Id tabla periodo_puntos
     * @return string
     */
    function utilizaPuntos($CalculoPuntos, $CalculoPConsumidos, $PuntosNecesarios, $Cliente, $Producto, $Periodo_P) {
        $ExisteRegistro = "SELECT cliente FROM saldopuntos WHERE cliente = '$Cliente'";
        $Existencia = utils\IConnection::execSql($ExisteRegistro);
        if ($CalculoPuntos - $CalculoPConsumidos >= $PuntosNecesarios) {
            $insertPuntos = "INSERT INTO puntos (cliente,fecha,producto,puntos,status,id_periodo) 
                    VALUES($Cliente,NOW(),$Producto,'$PuntosNecesarios','Cerrada','" . $Periodo_P . "')";
            utils\IConnection::execSql($insertPuntos);
            if ($Existencia["cliente"] > 0) {
                $ModifSaldoP = "UPDATE saldopuntos SET acumulado = " . $CalculoPuntos . ","
                        . "consumido = " . $CalculoPConsumidos + $PuntosNecesarios . " WHERE cliente='$Cliente'";
            } else {
                $ModifSaldoP = "INSERT INTO saldopuntos (cliente,acumulado,consumido) "
                        . "VALUES ($Cliente," . $CalculoPuntos . "," . $CalculoPuntos . ");";
            }
            utils\IConnection::execSql($ModifSaldoP);
            return utils\Messages::MESSAGE_DEFAULT;
        }
    }

    /*
     * Version 2 "Monederos Omicrom"
     */


    /*
     * Funcion Regresar datos de bonificacion
     * @param int $CntPuntos Cantidad de puntos a tomar de sus puntos
     * @param int $Ticket Id tabla rm
     * @return string
     */

    function calculaBonificacion($CntPuntos, $Ticket) {
        $MontoP = utils\IConnection::execSql("SELECT monto_promocion FROM periodo_puntos WHERE tipo_periodo ='A';");
        $ImporteEnPuntos = number_format($CntPuntos / $MontoP["monto_promocion"], 2);
        $ImporteTicket = utils\IConnection::execSql("SELECT importe FROM rm WHERE id = " . $Ticket);
        $Total = number_format($ImporteTicket["importe"] - $ImporteEnPuntos, 2);
        $Html = "<table style='width:100%;margin-top:15px;background-color:#D5D8DC;border-radius:5px;' class='texto_tablas'><tr><th>Id</th><th>Importe</th><th>Descuento</th><th>Total</th></tr>";
        $Html .= "<tr><td>" . $Ticket . "</td><td>" . number_format($ImporteTicket["importe"], 2)
                . "</td><td>$ImporteEnPuntos</td><td>$Total</td></tr></table>";
        return $Html;
    }

    /*
     * Funcion para ingresar la bonificacion
     * @param int $CntPuntos Cantidad de puntos a tomar de sus puntos
     * @param int $IdUnidad Id tabla unidades
     * @param int $Ticket Id tabla rm
     * @return array
     */

    function ingresaBonificacion($CntPuntos, $IdUnidad, $Ticket) {
        $Cobranza_beneficiosVO = new Cobranza_beneficiosVO();
        $Cobranza_beneficiosDAO = new Cobranza_beneficiosDAO();
        $Cobranza_beneficiosVO = $Cobranza_beneficiosDAO->retrieve($Ticket, "id_ticket_beneficio");
        if (!($Cobranza_beneficiosVO->getPuntos() > 0)) {
            $MontoP = utils\IConnection::execSql("SELECT monto_promocion FROM periodo_puntos WHERE tipo_periodo ='A';");
            $ImporteEnPuntos = number_format($CntPuntos / $MontoP["monto_promocion"], 2);
            $RestarPuntos = "SELECT id, puntos, consumido, id_unidad, puntos - consumido restantes "
                    . "FROM beneficios WHERE id_unidad = " . $IdUnidad . " "
                    . "AND puntos > consumido ORDER BY id ASC;";
            $rPp = utils\IConnection::getRowsFromQuery($RestarPuntos);
            $sumP = 0;
            $TtPuntos = $CntPuntos;
            $Corte = true;
            foreach ($rPp as $rs) {
                if ($sumP < $TtPuntos) {
                    if ($Corte) {
                        $ValCom = $sumP + $rs["restantes"];
                        if ($ValCom < $TtPuntos) {
                            $Puntos_restantes = $rs["restantes"];
                            $sumP += $rs["restantes"];
                        } else {
                            $Puntos_restantes = $TtPuntos - $sumP;
                            $Corte = false;
                        }
                        $UpdateBonificacion = "UPDATE beneficios SET consumido = consumido + $Puntos_restantes WHERE id = " . $rs["id"];

                        $Cobranza_beneficios2VO = new Cobranza_beneficiosVO();
                        $Cobranza_beneficios2VO->setId_beneficio($rs["id"]);
                        $Cobranza_beneficios2VO->setPuntos($Puntos_restantes);
                        $Cobranza_beneficios2VO->setId_ticket_beneficio($Ticket);
                        $Cobranza_beneficios2VO->setTm("C");
                        $Cobranza_beneficiosDAO->create($Cobranza_beneficios2VO);
                        utils\IConnection::execSql($UpdateBonificacion);
                    }
                }
            }
            $UpdateRm = "UPDATE rm SET descuento = '$ImporteEnPuntos' WHERE id = '" . $Ticket . "'";
            utils\IConnection::execSql($UpdateRm);
            $display["Html"] = utils\Messages::MESSAGE_DEFAULT;
            $display["Return"] = true;
        } else {
            $display["Html"] = "El ticket ingresado ya tiene una bonificacion";
            $display["Return"] = false;
        }
        return $display;
    }

    function acumulaPuntos($Ticket, $IdUnidad) {
        $Sql = "call omicrom.tipo_monedero($Ticket,'P','$IdUnidad');";
        $Msj = utils\IConnection::execSql($Sql);
        return $Msj;
    }

    function obtenBeneficioEnProductos($IdProducto, $PuntosDisponibles, $IdUnidad) {
        $Cobranza_beneficiosVO = new Cobranza_beneficiosVO();
        $Cobranza_beneficiosDAO = new Cobranza_beneficiosDAO();
        $Sql = "SELECT precio FROM inv WHERE id = " . $IdProducto;
        $precio = utils\IConnection::execSql($Sql);
        if ($precio["precio"] <= $PuntosDisponibles) {

            $RestarPuntos = "SELECT beneficios.id, beneficios.puntos, beneficios.consumido, beneficios.id_unidad,"
                    . " beneficios.puntos - beneficios.consumido restantes,u.cliente "
                    . "FROM beneficios LEFT JOIN unidades u on u.id=id_unidad WHERE id_unidad = " . $IdUnidad . " "
                    . "AND puntos > consumido ORDER BY id ASC;";
            $rPp = utils\IConnection::getRowsFromQuery($RestarPuntos);
            $sumP = 0;
            $TtPuntos = $precio["precio"];
            $Corte = true;
            foreach ($rPp as $rs) {
                if ($sumP < $TtPuntos) {
                    if ($Corte) {
                        $ValCom = $sumP + $rs["restantes"];
                        if ($ValCom < $TtPuntos) {
                            $Puntos_restantes = $rs["restantes"];
                            $sumP += $rs["restantes"];
                        } else {
                            $Puntos_restantes = $TtPuntos - $sumP;
                            $Corte = false;
                        }
                        $UpdateBonificacion = "UPDATE beneficios SET consumido = consumido + $Puntos_restantes WHERE id = " . $rs["id"];

                        $Cobranza_beneficios2VO = new Cobranza_beneficiosVO();
                        $Cobranza_beneficios2VO->setId_beneficio($rs["id"]);
                        $Cobranza_beneficios2VO->setPuntos($Puntos_restantes);
                        $Cobranza_beneficios2VO->setId_ticket_beneficio($IdProducto);
                        $Cobranza_beneficios2VO->setTm("A");
                        error_log(print_r($Cobranza_beneficios2VO, true));
                        $Cobranza_beneficiosDAO->create($Cobranza_beneficios2VO);
                        utils\IConnection::execSql($UpdateBonificacion);
                    }
                }
            }
            $display["Html"] = "Beneficios obtenidos";
            $display["Return"] = true;
        } else {
            $display["Html"] = "Beneficios negados";
            $display["Return"] = false;
        }
        return $display;
    }

    function inicializaPuntos($IdUnidad, $Puntos) {
        $SqlIdPp = "SELECT id FROM periodo_puntos WHERE tipo_periodo='A' LIMIT 1";
        $Idpp = utils\IConnection::execSql($SqlIdPp);
        $Create = "INSERT INTO beneficios (id_pp,id_unidad,id_consumo,tipo_consumo,puntos,consumido,tipo) 
                        VALUES (" . $Idpp["id"] . ",$IdUnidad,1,'A',$Puntos,0,'P');";
        utils\IConnection::execSql($Create);
    }

}
