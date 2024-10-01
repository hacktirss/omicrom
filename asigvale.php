<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("services/BoletosService.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$usuarioSesion = getSessionUsuario();

$selectFacturas = "select cli.nombre,b.idnvo,b.secuencia,b.codigo,b.importe importe_vale,
                    b.ticket ticket1,b.importe1 ,b.ticket2, b.importe2,
                    b.importecargado,b.ticket ,b.ticket2, (b.importe - b.importecargado) saldo
                from genbol g inner join cli on g.cliente = cli.id
                inner join boletos b on g.id = b.id 
                where vigente = 'Si'  and cli.id = $cli and b.importe > 0 ; ";

$registros = utils\IConnection::getRowsFromQuery($selectFacturas);

$Titulo = "Asignacion de vales para cliente " . $cli;
error_log("El valor de conta es " . $conta);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#txtValor").val("<?= $conta ?>");
            });

            function actualizarValor(estaChequeado, valor) {

                // Variables.
                var total = <?= $importe ?>;
                var suma_actual = <?= $conta ?>;
                var campo_resultado = document.getElementById('txtValor');
                valor = parseFloat(valor);


                // Obtener la suma que pueda tener el campo 'txtValor'.
                try {
                    if (campo_resultado != null) {

                        if (isNaN(campo_resultado.value)) {
                            campo_resultado.value = 0;
                        }

                        suma_actual = parseFloat(campo_resultado.value);
                    }
                } catch (ex) {
                    alert('No existe el campo de la suma.');
                }

                // Determinar que: si el check está seleccionado "checked"
                // entonces, agregue el valor a la variable "suma_actual";
                // de lo contrario, le resta el valor del check a "suma_actual".
                if (estaChequeado == true) {
                    suma_actual = suma_actual + valor;
                } else {
                    suma_actual = suma_actual - valor;
                }

                // Colocar el resultado de las operaciones anteriores de vuelta
                // al campo "txtValor".
                campo_resultado.value = suma_actual;

                if (suma_actual > total) {
                    alert('La suma seleccionada sobrepasa el importe favor de verificar favor de quitar la seleccion y dar enviar');
                } else {
                    campo_resultado.value = suma_actual;
                }
                return suma_actual;

            }
            function doSearch() {
                const tableReg = document.getElementById('datos');
                const searchText = document.getElementById('searchTerm').value.toLowerCase();
                let total = 0;
                // Recorremos todas las filas con contenido de la tabla
                for (let i = 1; i < tableReg.rows.length; i++) {
                    // Si el td tiene la clase "noSearch" no se busca en su cntenido
                    if (tableReg.rows[i].classList.contains("noSearch")) {
                        continue;
                    }
                    let found = false;
                    const cellsOfRow = tableReg.rows[i].getElementsByTagName('td');
                    // Recorremos todas las celdas
                    for (let j = 0; j < cellsOfRow.length && !found; j++) {
                        const compareWith = cellsOfRow[j].innerHTML.toLowerCase();
                        // Buscamos el texto en el contenido de la celda
                        if (searchText.length == 0 || compareWith.indexOf(searchText) > -1) {
                            found = true;
                            total++;
                        }
                    }
                    if (found) {
                        tableReg.rows[i].style.display = '';
                    } else {
                        // si no ha encontrado ninguna coincidencia, esconde la
                        // fila de la tabla
                        tableReg.rows[i].style.display = 'none';
                    }
                }
                // mostramos las coincidencias
                const lastTR = tableReg.rows[tableReg.rows.length - 1];
                const td = lastTR.querySelector("td");
                lastTR.classList.remove("hide", "red");
                if (searchText == "") {
                    lastTR.classList.add("hide");
                } else if (total) {
                    td.innerHTML = "Se ha encontrado " + total + " coincidencia" + ((total > 1) ? "s" : "");
                } else {
                    lastTR.classList.add("red");
                    td.innerHTML = "No se han encontrado coincidencias";
                }

            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <?php echo "<div class='mensajes'>$Msj</div>"; ?>
            <div id="DatosEncabezado">
                <table id="tbl-buys" aria-hidden="true">
                    <tr>
                        <td>&nbsp; Id: <?= $idT ?>&nbsp;</td>
                        <td>&nbsp; Cliente: <?= $nombre ?>&nbsp;</td>
                        <td>&nbsp; Importe <?= $importe ?>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp; Buscar: <input id="searchTerm" type="text" onkeyup="doSearch()" />&nbsp;</td>
                        <td>&nbsp; Importe en vales: <input type="text" readonly id="txtValor" value="<?= $conta ?>" />&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <form action="asigvale.php" method="post">
                <div id="Reportes">
                    <table id="datos" aria-hidden="true">
                        <thead>
                            <tr >
                                <td colspan="12">Vales disponibles </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"/></td>
                                <td>Vale</td>
                                <td>Secuencia</td>
                                <td>Codigo</td>
                                <td>Importe</td>
                                <td>Ticket</td>
                                <td>Importe1</td>
                                <td>Ticket2</td>
                                <td>Importe2</td>
                                <td>Importe Consumido</td>
                                <td>Saldo</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><input type="checkbox" id="chk_1" value=<?= $rg["codigo"] ?> name="valores[]" onclick="actualizarValor(this.checked, <?= $rg["saldo"] ?>);"/></td>
                                    <td><?= $rg["idnvo"] ?></td>
                                    <td><?= $rg["secuencia"] ?></td>
                                    <td><?= $rg["codigo"] ?></td>
                                    <td><?= $rg["importe_vale"] ?></td>
                                    <td><?= $rg["ticket1"] ?></td>
                                    <td><?= $rg["importe1"] ?></td>
                                    <td><?= $rg["ticket2"] ?></td>
                                    <td><?= $rg["importe2"] ?></td>
                                    <td><?= $rg["importecargado"] ?></td>
                                    <td><?= $rg["saldo"] ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="submit" name="Boton" value="enviar" id="Boton">
                </div>
            </form>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><em class="icon fa fa-lg fa-print"></em></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>

    </body>
</html>

