<?php
require_once("softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

/**
 * Description of Paginador
 *
 * @author rolando
 */
class Paginador {

    private $id;                // id del módulo en la tabla qrys
    private $additionalFields;  // Campos adicionales a lo definido en qrys
    private $from;  // Valor por default froms de qrys
    private $joins;             // Joins a tablas adicionales a lo definido en qrys
    private $groups;            // Group by statement
    private $additionalCriteria; // Condiciones adicionales de filtrado 
    private $sortField;         // Campos definidos del orden en la consulta
    private $sortFieldQ;        // Campos definidos del orden en la consulta
    private $sortType;          // Tipo de ordenamiento ASC o DESC
    private $criteria;          // Palabras para el filtro
    private $criteriaField;     // Campo definido para el filtro
    private $criteriaOperator;  // Operador de búsqueda
    private $currentPage;       // Página actual
    private $return;            // Return URL
    private $connection;        // Database connnection
    private $tableContents;     // Fields definition array
    private $queryPage;         //Indica el query a ejecutar

    /* @var $dataSet mysqli_result */
    private $dataSet;           // Data set
    private $dataRow;           // Current row
    private $configRow;         // Configuration

    /**
     */

    /**
     * Paginador. Módulo de paginación. Concentra en una clase las consultas y procesamiento de las definiciones de qry.
     * @param int $id ID del módulo en la tabla qrys
     * @param string $additionalFields Campos adicionales a lo definido en qrys (separados por coma simple de acuerdo a la sintaxis de SQL)
     * @param string $joins Tablas adicionales a las definidas en qrys (se espera una sentencia join con campos de unión de acuerdo a la sitaxis de SQL)
     * @param string $groups Condicion para agrupar los registros de acuerdo a la sintaxis de SQL
     * @param string $additionalCriteria Condiciones adicionales de filtrado
     * @param string $sortField Camṕo de ordenamiento
     * @param string $criteriaField Campo de búsqueda
     * @param string $criteria Palablas clave de la búsqueda
     * @param string $sortType Tipo de ordenamiento (ASC o DESC de acuerdo a la sitaxis de SQL)
     * @param int $page Página requerida
     * @param string $criteriaOperator Operador de búsqueda
     * @param string $return Return URL
     * @param strinf $from Tabla que sustituye a la de DB de la tabla qrys
     */
    function __construct($id, $additionalFields, $joins, $groups, $additionalCriteria, $sortField, $criteriaField, $criteria = "", $sortType = "ASC", $page = 0, $criteriaOperator = "REGEXP", $return = "", $from = null) {

        $this->id = $id;
        $this->additionalFields = empty($additionalFields) ? "" : ", " . $additionalFields;
        $this->additionalCriteria = utils\Utils::uempty($additionalCriteria);
        $this->joins = utils\Utils::uempty($joins);
        $this->groups = utils\Utils::uempty($groups);

        $this->criteria = str_replace(array('(', ')', '[', ']'), array('\\\(', '\\\)', '\\\[', '\\\]'), $criteria);
        $this->sortField = utils\Utils::uempty($sortField, "1");
        $this->sortFieldQ = $sortField;
        $this->sortType = strtoupper(utils\Utils::uempty($sortType, "ASC"));
        $this->criteriaField = $criteriaField;
        $this->criteriaOperator = $criteriaOperator;
        $this->currentPage = utils\Utils::uempty($page, 0);
        $this->return = $return;
        $this->from = $from;
        $this->connection = utils\IConnection::getConnection();

        if (strpos($sortField, " as")) {
            $tokens = explode(" as", $sortField);
            $this->sortFieldQ = trim($tokens['1']);
        }
        //error_log($this->sortField);
        //error_log($this->sortFieldQ);
        if($_REQUEST["criteria"] === "ini"){
             $_SESSION["NewRow"] = 15;
        }
        if ($_REQUEST["rowCnt"] > 4) {
            $_SESSION["NewRow"] = $_REQUEST["rowCnt"];
        } 
        $this->readDefinitions();
        $this->tableContents['pageSize'] = $_SESSION["NewRow"];

        $this->configureQuery();
    }

    /**
     * getDataRow Devuelve el renglón actual
     * @return array Arreglo de datos del renglón actual del data set
     */
    public function getDataRow() {
        return $this->dataRow;
    }

    /**
     * 
     * @return array Arreglo con las definiciones de la tabla
     */
    function getTableContents() {
        return $this->tableContents;
    }

    function setTableContents($tableContents) {
        $this->tableContents = $tableContents;
    }

    private function readDefinitions() {

        $statement = $this->connection->query("SELECT cabeceras, campos, tablas, condiciones, paginas, campos_adicional, uniones FROM querys WHERE id = " . $this->id);
        $this->configRow = $statement->fetch_array();

        $this->tableContents['headers'] = explode(",", $this->configRow['cabeceras']);
        $this->tableContents['pageSize'] = $_SESSION["NewRow"];
    }

    private function configureQuery() {
        try {
            $criteriaClause = " WHERE TRUE";
            $criteriaClause .= empty($this->criteria) ? "" : " AND " . $this->criteriaField . " " . $this->criteriaOperator . " '" . $this->criteria . "'";
            $criteriaClause .= empty($this->additionalCriteria) ? "" : " AND " . $this->additionalCriteria;

            //$sqlSize = "SELECT COUNT( * ) size FROM " . $this->configRow['tablas'] . " " . $this->joins . $criteriaClause . " " . $this->configRow['condiciones'] . " " . $this->groups;
            $sqlSize = "SELECT COUNT( * ) size FROM " . ($this->from == null ? $this->configRow['tablas'] : $this->from) . " " . $this->joins . $criteriaClause . " " . $this->configRow['condiciones'] . " ";
            //error_log($sqlSize);
            if (($statement = $this->connection->query($sqlSize))) {
                $rs = $statement->fetch_assoc();

                $this->tableContents['dataCount'] = $rs['size'];
                $this->tableContents['totalPages'] = ceil($rs['size'] / $this->tableContents['pageSize']);
            } else {
                error_log($this->connection->error);
                $this->tableContents['dataCount'] = 0;
                $this->tableContents['totalPages'] = 0;
            }
            $this->currentPage = empty($this->currentPage) ? $this->tableContents['totalPages'] : $this->currentPage;
            $this->currentPage = $this->currentPage > $this->tableContents['totalPages'] ? $this->tableContents['totalPages'] : $this->currentPage;

            $minRow = $this->tableContents['pageSize'] * ( ( $this->currentPage == 0 ? $this->tableContents['totalPages'] : $this->currentPage ) - 1 );
            $minRow = ($this->tableContents['dataCount'] - $minRow) < $this->tableContents['pageSize'] ? $this->tableContents['dataCount'] - $this->tableContents['pageSize'] : $minRow;
            $minRow = $minRow >= 0 ? $minRow : 0;

            $this->tableContents['sqlData'] = "SELECT " . $this->configRow['campos'] . " " . $this->additionalFields . (!empty($this->configRow['campos_adicional']) ? ", " . $this->configRow['campos_adicional'] : "")
                    . " FROM " . ($this->from == null ? $this->configRow['tablas'] : $this->from) . " " . $this->joins
                    . " " . (!empty($this->configRow['uniones']) ? " " . $this->configRow['uniones'] : "")
                    . $criteriaClause . " " . $this->configRow['condiciones']
                    . " " . $this->groups
                    . " ORDER BY " . $this->sortFieldQ . " " . $this->sortType;
            $queryPage = $this->tableContents['sqlData'] . " LIMIT " . $minRow . "," . $this->tableContents['pageSize'];
            $this->setQueryPage($queryPage);
            //error_log($queryPage);
            $this->dataSet = $this->connection->query($queryPage);
        } catch (Exception $ex) {
            error_log($ex);
        } finally {
            if ($this->connection->errno > 0) {
                error_log($this->connection->error);
                error_log($queryPage);
            }
        }
    }

    /**
     * Establece los encabezados. para módulos con columnas adiconales se requiere definir los encabezados izquierdo y derecho.
     * @param array $izq Arreglo de encabezados izquierdos (adicionales a la configuración del módulo en qrys)
     * @param array $der Arreglo de encabezados derechos (adicionales a la configuración del módulo en qrys)
     * @return string Código HTML del header
     */
    public function headers($izq = array(), $der = array()) {

        $headers = "<tr>";

        for ($i = 0; $i < sizeof($izq); $i++) {
            $headers .= "<th>" . $izq[$i] . "</th>";
        }

        for ($i = 0; $i < sizeof($this->tableContents['headers']); $i += 3) {

            $location = $this->sortField == trim($this->tableContents['headers'][$i + 1]) ?
                    utils\HTTPUtils::self() . "?sortField=" . urlencode($this->tableContents['headers'][$i + 1]) . "&sortType=" . ($this->sortType === "ASC" ? "DESC" : "ASC") :
                    utils\HTTPUtils::self() . "?sortField=" . urlencode($this->tableContents['headers'][$i + 1]) . "&sortType=ASC";
            if ($this->sortField == trim($this->tableContents['headers'][$i + 1])) {
                $stype = $this->sortType === 'ASC' ? "asc" : "desc";
                $headers .= "<th class=\"clickable choosed\" onclick=\"location='" . $location . "'\">";
                $headers .= "<div style=\"float:left; width:80%;\">" . $this->tableContents['headers'][$i] . "</div>";
                $headers .= "<div style=\"float:right; width:20%;\"><i class=\"fa fa-sort-amount-" . $stype . "\" aria-hidden=\"true\"></i></div>";
                $headers .= "</th>";
            } else {
                $headers .= "<th class=\"clickable\" onclick=\"location='" . $location . "'\">" . $this->tableContents['headers'][$i] . "</th>";
            }
        }

        for ($i = 0; $i < sizeof($der); $i++) {
            $headers .= "<th>" . $der[$i] . "</th>";
        }

        $headers .= "</tr>";

        if ($this->dataSet->num_rows == 0) {
            $headers .= "<tr><td colspan=\"100%\" style=\"text-align: center\"><strong>No se encontraron resultados</strong></td></tr>";
        }
        return $headers;
    }

    /**
     * 
     * @param array $titles Títulos de la columna
     * @return string Código HTML del renglón
     */
    public function formatRow($titles = array()) {

        $row = "";
        for ($i = 0, $x = 0; $i < sizeof($this->tableContents['headers']); $i += 3, $x++) {

            $titleIndex = $this->tableContents['headers'][$i + 1];
            $title = empty($titles[$titleIndex]) ? "" : $titles[$titleIndex];

            $format = $this->tableContents['headers'][$i + 2];

            // Data format
            $value = $this->dataRow[$x];

            if (strpos($format, "N") !== false) {
                $value = number_format($value, 3, ".", ",");
            } elseif (strpos($format, "P") !== false) {
                $value = "$ " . number_format($value, 2, ".", ",");
            } elseif (strpos($format, "I") !== false) {
                $value = number_format($value, 0, ".", "");
            } elseif (strpos($format, "V") !== false) {
                $value = number_format($value, 3);
            } elseif (strpos($format, "M") !== false) {
                $value = trim(mb_convert_case($value, MB_CASE_UPPER, "UTF-8"));
            } elseif (strpos($format, "C") !== false) {
                $value = trim(mb_convert_case($value, MB_CASE_TITLE, "UTF-8"));
            } elseif (strpos($format, "L") !== false) {
                $value = trim($value);
            }

            // Text align
            $align = "left";
            if (strpos($format, "r") !== false) {
                $align = "right";
            } elseif (strpos($format, "c") !== false) {
                $align = "center";
            }
            // Text decoration
            $decoration = "";
            if (strpos($format, "b") !== false) {
                $decoration = "font-weight: bold;";
            } elseif (strpos($format, "i") !== false) {
                $decoration = "font-weight: italic;";
            }
            $row .= "<td style=\"text-align: " . $align . ";" . $decoration . "\" " . ( empty($title) ? "" : "title=\"" . $title . "\"" ) . ">" . $value . "</td>";
        }
        return $row;
    }

    public function next() {
        if ($this->dataSet->num_rows == 0) {
            return false;
        }
        $this->dataRow = $this->dataSet->fetch_array();
        return $this->dataRow != false;
    }

    function getQueryPage() {
        return $this->queryPage;
    }

    function setQueryPage($queryPage) {
        $this->queryPage = $queryPage;
    }

    /**
     * Establece el pie de tabla. Contadores de registro y páginas y el control de navegación.
     * @param boolean $add Bandera para mostrar el link agregar 
     * @param array $nLink Arreglo de links adicionales
     * @param boolean $export Bandera para mostrar el link de generación de reporte
     * @param boolean $reload Bandera para mostrar el link de Reestablecer página
     * @param int $maxSize Tamaño máximo del contro de navegación
     * @param boolean $showPages Defini si se muestra la paginación o solo links
     * @return string Código HTML del footer
     */
    public function footer($add, $nLink = array(), $export = true, $reload = true, $maxSize = 11, $showPages = true) {

        $maxSize = $maxSize > $this->tableContents['totalPages'] ? $this->tableContents['totalPages'] : $maxSize;
        $startPage = $this->currentPage - floor($maxSize / 2);    // Coloca la página seleccionada al centro del paginador si es posible
        $startPage = $startPage > 0 ? $startPage : 1;           // Verifica que la página inicial sea mayor o igual a uno
        $endPage = $startPage + $maxSize - 1;                  // La página final es la página inicial más el tamaño máximo
        $endPage = $endPage > $this->tableContents['totalPages'] ? $this->tableContents['totalPages'] : $endPage;
        $startPage = ($endPage - $startPage) < $maxSize ?
                (($endPage - $startPage) <= $this->tableContents['totalPages'] ? ($endPage - $maxSize + 1) : $startPage) : $startPage;

        $footer = "<table class=\"pagerfooter\">";

        if ($showPages) {

            $footer .= "<tr>"
                    . "<td width=\"30%\">&nbsp</td>"
                    . "<td align=\"center\" >Registros:  " . number_format($this->tableContents['dataCount'], 0) . "</td>"
                    . "<td align=\"right\">Página " . $this->currentPage . " de " . $this->tableContents['totalPages'] . "</td>"
                    . "</tr>"
                    . "<tr>"
                    . "<td colspan=\"100%\ style=\"text-align: center;\"><div class=\"indexer\"><ul>";

            if ($startPage > ceil($maxSize / 2) && $this->tableContents['totalPages'] > 1) {
                $footer .= "<li><a href=\"" . utils\HTTPUtils::self() . "?page=1\" title=\"Primero\"><i class=\"icon fa fa-fw fa-fast-backward\" aria-hidden=\"true\"></i></a></li>";
            }
            if ($startPage > 1 && $this->tableContents['totalPages'] > 1) {
                $footer .= "<li><a href=\"" . utils\HTTPUtils::self() . "?page=" . ($this->currentPage - 1) . "\" title=\"Anterior\"><i class=\"icon fa fa-fw fa-backward\" aria-hidden=\"true\"></i></a></li>";
            }

            for ($i = $startPage, $j = 1; $i <= $this->tableContents['totalPages'], $i <= $endPage; $i++, $j++) {
                if ($this->currentPage == $i) {
                    $footer .= "<li><span class=\"pageselected\">[" . sprintf('%02d', $i) . "]</span></li>";
                } else {
                    $footer .= "<li><a href=\"" . utils\HTTPUtils::self() . "?page=" . $i . "\"> " . sprintf('%02d', $i) . "</a></li>";
                }
            }

            if (($this->tableContents['totalPages'] - $endPage) > 0) {
                $footer .= "<li><a href=\"" . utils\HTTPUtils::self() . "?page=" . ($this->currentPage + 1) . "\" title=\"Siguiente\"><i class=\"icon fa fa-fw fa-forward\" aria-hidden=\"true\"></i></a></li>";
            }

            if (($this->tableContents['totalPages'] - $endPage) > floor($maxSize / 2)) {
                $footer .= "<li><a href=\"" . utils\HTTPUtils::self() . "?page=" . $this->tableContents['totalPages'] . "\" title=\"Último\"><i class=\"icon fa fa-fw fa-fast-forward\" aria-hidden=\"true\"></i></a></li>";
            }

            $footer .= "</ul></div></td></tr>";
        }
        $footer .= "<tr><td colspan=\"100%\" style=\"text-align: left; padding: 5px;\">";

        if ($add) {
            $cLink = substr(utils\HTTPUtils::self(), 0, strrpos(utils\HTTPUtils::self(), ".")) . 'e.php';
            $footer .= "<a class=\"tablelink\" href=\"" . $cLink . "?id=NUEVO\"><i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> Agregar</a>";
        }

        //error_log($this->return);
        if (!empty($this->return)) {
            $footer .= "<a class=\"tablelink\" href=\"" . $this->return . "\"><i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar</a>";
        }

        if (is_array($nLink) && count($nLink)) {
            foreach ($nLink as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $footer .= "<a class=\"tablelink\" href=\"" . $value . "\">" . $key . "</a>";
                }
            }
        }

        if ($export) {
            $footer .= "<a class=\"tablelink\" href=\"bajarep.php?cSql=" . rawurlencode($this->tableContents['sqlData']) . "\"><i class=\"icon fa fa-lg fa-download\" aria-hidden=\"true\"></i> Exportar</a>";
        }

        if ($reload) {
            $footer .= "<a class=\"tablelink\" href=\"" . utils\HTTPUtils::self() . "?criteria=ini\" title=\"Actualiza la pantalla\"><i class=\"icon fa fa-lg fa-refresh\" aria-hidden=\"true\"></i> Restablecer</a>";
        }

        $footer .= "</td><td style='text-align:right;'>No. Filas <input type='number' value='15' id='NumColumns' style='border:1px solid #1E1E1E;width:45px;font-size:11px;color:#353535;border-radius: 5px;' min='5'></td></tr></table>";

        return $footer;
    }

    public function filter() {

        $num = (sizeof($this->tableContents['headers']) / 3) + 1;
        $filter = "<form name=\"autoForm\" id=\"autoForm\" method=\"post\" action=\"" . utils\HTTPUtils::self() . "\">"
                . "<table class=\"quicksearch\">"
                . "<tr>"
                . "<td rowspan=\"2\" align=\"left\">B&uacute;squeda r&aacute;pida:<br>"
                . "<div style=\"position: relative;\">"
                . "<input type=\"text\" size=\"20\" class=\"text\" placeholder='Ingresar palabra(s)' name=\"criteria\" id=\"autocomplete\" style=\"text-align: left;\"/>"
                . "</div>"
                . "<div id=\"autocomplete-suggestions\"></div>"
                . "</td>"
                . "<td colspan=\"" . $num . "\" align=\"center\">Seleccione el criterio de b&uacute;squeda</td>"
                . "</tr>"
                . "<tr>";

        for ($i = 0; $i < sizeof($this->tableContents["headers"]); $i += 3) {
            $criteriaField = trim($this->tableContents['headers'][$i + 1]);
            if ($criteriaField == $this->criteriaField) {
                $filter .= "<td><input type=\"radio\"  class=\"botonAnimatedMin\" name=\"criteriaField\" value=\"" . $criteriaField . "\" checked>&nbsp;" . $this->tableContents['headers'][$i] . "</td>";
            } else {
                $filter .= "<td><input type=\"radio\" class=\"botonAnimatedMin\" name=\"criteriaField\" value=\"" . $criteriaField . "\">&nbsp;" . $this->tableContents['headers'][$i] . "</td>";
            }
        }

        $filter .= "<td align='center'>"
                . "<input type=\"submit\" name=\"Boton\" value=\"Enviar\" class=\"nombre_cliente\">"
                . "<input type=\"hidden\" name=\"pagina\" value=\"1\">"
                . "</td>"
                . "</td></tr></table>"
                . "</form>";
        return $filter;
    }

    public function script() {
        ?>
        <script>
            $(document).ready(function () {
                $("#autocomplete").
                        suggestionTool(
                                $("#autoForm"),
                                "<?= preg_replace("/\s\s+/", "\\n", ($this->from == null ? $this->configRow['tablas'] : $this->from) . " " . $this->joins) ?>",
                                function () {
                                    var orderValue = $("input[name='criteriaField']:checked").val();
                                    return orderValue.indexOf(" as ") >= 0 ? orderValue.split(" as ")[0] : orderValue;
                                },
                                "<?= $this->additionalCriteria ?>");
            });
        </script>
        <?php
    }

}
