<?php

/*
 * FacturaConceptosDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('FacturaConceptoVO.php');

class FacturaConceptosDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }
    
    public function retrieveConceptos($folio) {
        $conceptos = array();
        $sql = "SELECT 
                        SUBQ.idnvo id,
                        SUBQ.producto,
                        SUBQ.ticket,
                        IFNULL(com.clave, SUBQ.id) clave,
                        SUBQ.descripcion,
                        SUBQ.umedida,
                        SUBQ.inv_cproducto,
                        SUBQ.inv_cunidad,
                        SUBQ.ieps,
                        SUBQ.iva,
                        SUBQ.cantidad,
                        SUBQ.precio,
                        SUBQ.base_iva,
                        SUBQ.base_ieps,
                        CAST(SUBQ.iva AS DECIMAL(10, 6)) factoriva,
                        CAST(SUBQ.ieps AS DECIMAL(10, 6)) factorieps,
                        SUBQ.impiva impiva,
                        SUBQ.impieps impieps,
                        SUBQ.impdescuento descuento,
                        SUBQ.subtotal - SUBQ.impdescuento - SUBQ.ivadescuento subtotal,
                        --SUBQ.subtotal - SUBQ.impdescuento - SUBQ.ivadescuento + SUBQ.impiva + SUBQ.impieps total,
                        SUBQ.total total,
                        SUBQ.total - (SUBQ.subtotal - SUBQ.impdescuento - SUBQ.ivadescuento + SUBQ.impiva + SUBQ.impieps) diferencia
                FROM(
                                SELECT 
                                        fcd.idnvo,
                                        fcd.producto,
                                        inv.id,
                                        inv.descripcion,
                                        inv.umedida,
                                        inv.inv_cproducto,
                                        inv.inv_cunidad,
                                        fcd.ticket,
                                        round( fcd.precio, 4 ) precio,
                                        fcd.preciob,
                                        fcd.iva,
                                        fcd.ieps,
                                        round( fcd.cantidad, 4 ) cantidad,
                                        fcd.descuento,
                                        fcd.importe total,
                                        ( fcd.cantidad * round( fcd.precio, 4 ) ) subtotal,
                                        ( fcd.cantidad * round( fcd.precio, 4 ) * (1 - IFNULL(descuento, 0)/100) ) base_iva,
                                        ( fcd.cantidad ) base_ieps,
                                        ( fcd.cantidad * round( fcd.precio, 4 ) * (1 - IFNULL(descuento, 0)/100) * fcd.iva ) impiva,
                                        ( fcd.cantidad * fcd.ieps ) impieps,
                                        ( fcd.cantidad * round( fcd.precio, 4 ) * fcd.iva * IFNULL(descuento, 0)/100 ) ivadescuento,
                                        ( fcd.cantidad * ( round( fcd.precio, 4 ) + fcd.ieps ) * IFNULL(descuento, 0)/100 ) impdescuento
                                FROM (
                                        SELECT 
                                           fcd.idnvo,
                                           fcd.ticket,
                                           fcd.producto,
                                           fcd.iva,
                                           fcd.ieps,
                                           fcd.preciob,
                                           ( fcd.preciob-fcd.ieps )/(1+fcd.iva) precio,
                                           fcd.importe/fcd.preciob cantidad,
                                           fcd.descuento,
                                           fcd.importe
                                        FROM fcd 
                                        WHERE fcd.id = ".$folio.") fcd
                                JOIN inv ON fcd.producto=inv.id
                                ) SUBQ
                LEFT JOIN com ON com.descripcion LIKE SUBQ.descripcion";
        error_log($sql);
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $concepto = new FacturaConceptoVO();
                $concepto->setId($rs['id']);
                $concepto->setClave($rs['clave']);
                $concepto->setProducto($rs['producto']);
                $concepto->setDescripcion($rs['descripcion']);
                $concepto->setTicket($rs['ticket']);
                $concepto->setCantidad($rs['cantidad']);
                $concepto->setPrecio($rs['precio']);
                $concepto->setIva($rs['iva']);
                $concepto->setIeps($rs['ieps']);
                $concepto->setUmedida($rs['umedida']);
                $concepto->setFactoriva($rs['factoriva']);
                $concepto->setFactorieps($rs['factorieps']);
                $concepto->setInv_cproducto($rs['inv_cproducto']);
                $concepto->setInv_cunidad($rs['inv_cunidad']);
                $concepto->setSubtotal($rs['subtotal']);
                $concepto->setBaseIva($rs['base_iva']);
                $concepto->setBaseIeps($rs['base_ieps']);
                $concepto->setDescuento($rs['descuento']);
                $concepto->setImpiva($rs['impiva']);
                $concepto->setImpieps($rs['impieps']);
                $concepto->setTotal($rs['total']);
                array_push($conceptos, $concepto);
            }
        }
        error_log(mysqli_error($this->conn));
        return $conceptos;
    }
    
    public function createConceptosRM($folio, $rm) {
        $executed = FALSE;
        $sql = "
        INSERT INTO fcd (id, producto, cantidad, precio, iva, ieps, importe, ticket, tipoc, preciob)
        SELECT
                ?,
                idProducto,
                cantidad,
                precioUnitario,
                iva IVA,
                ieps IEPS,
                importe,
                id,
                quantifier,
                precio
           FROM (
                    SELECT
                            SUBQ.idProducto,
                            SUBQ.cantidad,
                            round(SUBQ.preciouu, 4) AS precioUnitario,
                            SUBQ.iva IVA,
                            SUBQ.ieps IEPS,
                            round(round(SUBQ.cantidad, 3)*round(SUBQ.preciouu, 4), 6) importe,
                            SUBQ.id,
                            SUBQ.quantifier,
                            SUBQ.precio
                    FROM (
                                SELECT
                                      inv.id idProducto,
                                      round(volumen, 3) cantidad,
                                      (rm.precio-rm.ieps)/(1+rm.iva) preciouu,
                                      rm.iva,
                                      round(rm.ieps, 4) ieps,
                                      round(rm.pesos, 4) pesos,
                                      rm.id,
                                      'I' quantifier,
                                      rm.precio
                                FROM rm
                                JOIN com ON rm.producto = com.clavei
                                JOIN inv ON com.descripcion = inv.descripcion
                                WHERE rm.uuid = '-----'
                                AND rm.pesos > 0
                                AND rm.tipo_venta = 'D' AND (rm.id = ?)
                                UNION ALL
                                SELECT
                                        vtaditivos.clave idProducto,
                                        round(vtaditivos.cantidad, 3) cantidad,
                                        round(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                                        vtaditivos.iva,
                                        round(0.0000, 4) ieps,
                                        round(vtaditivos.total, 4) pesos,
                                        vtaditivos.referencia id,
                                        'C' quantifier,
                                        vtaditivos.unitario precio
                                FROM vtaditivos
                                JOIN cia ON 1=1
                                JOIN rm ON rm.id = vtaditivos.referencia
                                WHERE rm.uuid = '-----'
                                AND rm.tipo_venta = 'D' AND (vtaditivos.referencia = ?)
                    ) SUBQ
            ) TOT
        ";
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("sss", 
                    $folio,
                    $rm,
                    $rm);
            $executed = $ps->execute();
            $ps->close();
        }
        return $executed;
    }
    
    public function updateTotalesFC($id) {
        $sql = "
            UPDATE fc 
            JOIN (
               SELECT 
                  cantidad, total, importe, iva, total-importe-iva ieps 
               FROM (
                  SELECT 
                     round( sum( cantidad ), 3) cantidad,
                     round( sum( total ), 2) total,
                     round( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
                     round( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ) * factoriva, 2) iva
                  FROM (
                     SELECT 
                        iva factoriva,
                        ieps factorieps,
                        cantidad,
                        cantidad * preciob total,
                        preciob
                     FROM fcd WHERE id = ?
                  ) sfcd
               ) tfcd
            ) fcd ON TRUE
            SET 
               fc.cantidad = fcd.cantidad,
               fc.importe = fcd.importe,
               fc.iva = fcd.iva,
               fc.ieps = fcd.ieps,
               fc.total = fcd.total
            WHERE fc.id = ?
        ";
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("ss", 
                    $id,
                    $id);
            $executed = $ps->execute();
            $ps->close();
        }
        return $executed;
    }
}
