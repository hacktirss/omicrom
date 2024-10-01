$(document).ready(function () {
    var CantidadTr = cantidadDeTr();
    $("#AgregaContado").click(function () {
        if ($("#Ticket").val() === "" && $("#Fecha").val() === "") {
            alert("Favor de ingresar algún valor");
            return false;
        }
        var dateFactura = $("#FechaAct").val();
        dateFactura = dateFactura.split(" ");
        mesFactura = dateFactura[0].split("-");
        var dateVContado = $("#FechaFCn").val();
        dateVContado = dateVContado.split(" ");
        mesVContado = dateVContado[0].split("-");
        AnioFactura = mesFactura[0];
        Factura = mesFactura[1];
        AnioContado = mesVContado[0];
        Contado = mesVContado[1];
        if (Factura !== Contado) {
            if (confirm("¿Seguro de facturar ventas del mes no. " + Contado + " en factura del mes " + Factura + "? Verificar fechas asignadas")) {
                return true;
            } else {
                return false;
            }
        } else {
            if ((CantidadTr > 0 & $("#Fecha").val() !== "") && $("#Ticket").val() === "") {
                if (confirm("Los registros capturados hasta el momento seran eliminados. ¿Desea continuar?")) {
                    return true;
                }
                return false;
            }
        }
    });
    $("#AgregarTarjeta").click(function () {
        console.log($("#FechaI").val() + " " + $("#FechaF").val());
        if ($("#FechaI").val() === "" && $("#FechaF").val() === "") {
            alert("Favor de ingresar algún valor");
            return false;
        }
        if (($("#FechaI").val() === "" && $("#FechaF").val() !== "") || ($("#FechaI").val() !== "" && $("#FechaF").val() === "")) {
            alert("Favor de no dejar campos en blanco");
            return false;
        }

        if (CantidadTr > 0) {
            if (confirm("Los registros capturados hasta el momento seran eliminados. ¿Desea continuar?")) {
                return true;
            }
            return false;
        }
    });
    $("#AgregarMonedero").click(function () {
        if ($("#FechaIM").val() === "" && $("#FechaFM").val() === "") {
            alert("Favor de ingresar algún valor");
            return false;
        }
        if (($("#FechaIM").val() === "" && $("#FechaFM").val() !== "") || ($("#FechaIM").val() !== "" && $("#FechaFM").val() === "")) {
            alert("Favor de no dejar campos en blanco");
            return false;
        }

        if (CantidadTr > 0) {
            if (confirm("Los registros capturados hasta el momento seran eliminados. ¿Desea continuar?")) {
                return true;
            }
            return false;
        }
    });
    $("#AgregarAceites").click(function () {

        if ($("#FechaII").val() === "" && $("#FechaFF").val() === "") {
            alert("Favor de ingresar algún valor");
            return false;
        }
        if (($("#FechaII").val() === "" && $("#FechaFF").val() !== "") || ($("#FechaII").val() !== "" && $("#FechaFF").val() === "")) {
            alert("Favor de no dejar campos en blanco");
            return false;
        }
        if (CantidadTr >= 0) {
            if (confirm("Los registros capturados hasta el momento seran eliminados. ¿Desea continuar?")) {
                return true;
            }
            return false;
        }
    });
});

function cantidadDeTr() {
    var MyRows = $('table#Tabla_Fac').find('tbody').find('tr');
    for (var i = 0; i < MyRows.length; i++) {
        var MyIndexValue = $(MyRows[i]).find('td:eq(1)').html();
    }
    return MyIndexValue;
}
