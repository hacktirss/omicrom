<?php

/*
 * FacturaConceptosDAO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');

class ComplementoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }
    
    public function getComplemento($idComplemento, $idFactura) {
        $sql = "SELECT C.nombre, A.nombre, IFNULL(valor, defecto) "
                . "FROM complementos C "
                . "JOIN complemento_attr A ON C.id = A.id_complemento "
                . "LEFT JOIN complemento_val V ON A.id_complemento = V.id_complemento AND A.id = V.id_atributo AND V.id_fc_fk = ? "
                . "WHERE A.id_complemento = ?";
        $valores = array();
        if (($ps=$this->conn->prepare($sql))) {
            $ps->bind_param("ss",
                    $idFactura,
                    $idComplemento);
            if ($ps->execute()) {
                $complemento = NULL;
                $atributo = NULL;
                $valor = NULL;
                
                $ps->bind_result($complemento, $atributo, $valor);
                while ($ps->fetch()) {
                    $valores[$atributo] = $valor;
                }//for each row
            }
        }
        return $valores;
    }

    public function setAtributo($idComplemento, $idAtributo, $idFactura, $valor) {
        $executed = FALSE;
        $sqlComplemento = "INSERT INTO complemento_val (id_complemento, id_atributo, id_fc_fk, valor) VALUES(?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "valor = VALUES(valor)";
        if (($ps=$this->conn->prepare($sqlComplemento))) {
            $ps->bind_param("ssss",
                    $idComplemento,
                    $idAtributo,
                    $idFactura,
                    $valor);
            $executed = $ps->execute();
            $ps->close();
        }
        return $executed;
    }
}
