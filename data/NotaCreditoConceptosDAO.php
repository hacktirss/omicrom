<?php

/*
 * NotaCreditoConceptosDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2017
 */

include_once ('mysqlUtils.php');
include_once ('NotaCreditoConceptoVO.php');

class NotaCreditoConceptosDAO {
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
                        SUBQ.subtotal subtotal,
                        SUBQ.total total,
                        SUBQ.total - (SUBQ.subtotal + SUBQ.impiva + SUBQ.impieps) diferencia
                FROM(
                                SELECT 
                                        ncd.idnvo,
                                        ncd.producto,
                                        inv.id,
                                        inv.descripcion,
                                        inv.umedida,
                                        inv.inv_cproducto,
                                        inv.inv_cunidad,
                                        round( ncd.precio, 4 ) precio,
                                        ncd.preciob,
                                        ncd.iva,
                                        ncd.ieps,
                                        round( ncd.cantidad, 4 ) cantidad,
                                        ncd.importe total,
                                        ( ncd.cantidad * round( ncd.precio, 4 ) ) subtotal,
                                        ( ncd.cantidad * round( ncd.precio, 4 ) ) base_iva,
                                        ( ncd.cantidad ) base_ieps,
                                        ( ncd.cantidad * round( ncd.precio, 4 ) * ncd.iva ) impiva,
                                        ( ncd.cantidad * ncd.ieps ) impieps
                                FROM (
                                        SELECT 
                                           ncd.idnvo,
                                           ncd.producto,
                                           ncd.iva,
                                           ncd.ieps,
                                           ncd.preciob,
                                           ( ncd.preciob-ncd.ieps )/(1+ncd.iva) precio,
                                           ncd.importe/ncd.preciob cantidad,
                                           ncd.importe
                                        FROM ncd 
                                        WHERE ncd.id = ".$folio.") ncd
                                JOIN inv ON ncd.producto=inv.id
                                ) SUBQ
                LEFT JOIN com ON com.descripcion LIKE SUBQ.descripcion";
        error_log($sql);
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $concepto = new NotaCreditoConceptoVO();
                $concepto->setId($rs['id']);
                $concepto->setClave($rs['clave']);
                $concepto->setProducto($rs['producto']);
                $concepto->setDescripcion($rs['descripcion']);
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
                $concepto->setImpiva($rs['impiva']);
                $concepto->setImpieps($rs['impieps']);
                $concepto->setTotal($rs['total']);
                array_push($conceptos, $concepto);
            }
        }
        error_log(mysqli_error($this->conn));
        return $conceptos;
    }
}
