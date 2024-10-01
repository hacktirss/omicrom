/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getUnidades.php";
var paramValidator = "Unidades";
var modalAdd = "";
$(".close").click(function (e) {
    $("#modal-parametros-listas").fadeOut('slow');
});
$("#DepositoId").click(function (e) {
    var event = $(e.relatedTarget);
    var Cliente = $("#ClienteNo").val();
    var IdPago = $("#IdPago").val();
    var modalTitle = "Agregar saldo a unidades";
    var modal = $("#modal-unidades");
    $("#modal-parametros-listas").fadeIn('slow');
    modal.find(".modal-title").html(modalTitle);
    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {"Cliente": Cliente, "IdPago": IdPago},
        beforeSend: function (xhr, opts) {
        },
        success: function (data) {
            var array = JSON.parse(data);
            console.log(array);
            var lista = array.rows;
            var listaLog = array.listaLog;
            var pago = array.PagoAplicado;
            modal.find("table tbody").empty();
            var a = 0;
            var inicolor = "#EAECEE";
            var color = "";
            var importe = 0;
            jQuery.each(pago, function (name, value) {
                importe = value.aplicado;
            });
            jQuery.each(lista, function (name, value) {
                if (a % 2 == 0) {
                    color = inicolor;
                } else {
                    color = "";
                }
                if (value.importeDelPago == null) {
                    impPago = 0;
                } else {
                    impPago = value.importeDelPago;
                }

                modal.find("table tbody").append("<tr style='height:25px;' bgcolor='" + color + "'>\n\
                <td style='border-left:1px solid #566573;padding-left:20px;'>" + value.id + "</td>\n\
                <td>" + value.descripcion + "</td>\n\
                <td>" + value.impreso + "</td>\n\
                <td align='right'>" + value.importe + "</td>\n\
                <td align='right'>" + Number.parseFloat(impPago).toFixed(2) + "</td>\n\
                <td align='right' style='padding-right:10px;border-right:1px solid #566573;'>\n\
                <form name='formulario1' id='formulario1' method='post' action=''>\n\
                <input type='text' name='SaldoSum'>\n\
                <input type='hidden' name='IdUnidad' value='" + value.id + "'>\n\
                <input type='hidden' name='idPago' value='" + $("#IdPago").val() + "'>\n\
                <input type='hidden' name='op' value='ActualizaSaldo'>\n\
                <input type='submit' name='Boton' value='Actualizar'></td>\n\
                </form>\n\
                </tr>");
                a++;
            });
            modal.find("table tbody").append("<tr><th colspan='6' style='height:25px;border-top:1px solid #566573;'></th></tr>");
            var Sum = 0;
            jQuery.each(listaLog, function (name, value) {
                if (a % 2 == 0) {
                    color = inicolor;
                } else {
                    color = "";
                }
                Sum = parseFloat(Sum) + parseFloat(value.importeDelPago);
                a++;
            });
            var restante = 0;
            restante = importe - Sum;
            modal.find("table tbody").append("<tr><th colspan='2' style='height:50px;border-top:1px solid #566573;'></th>\n\
            <th style='border-top:1px solid #566573;' colspan='2'>Pago : " + importe + "</th>\n\
            <th style='border-top:1px solid #566573;'>Restante : " + restante + "</th>\n\
            <th style='border-top:1px solid #566573;'> Total : " + Sum + "</th></tr>");
        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
});
function setValueFromList(control, value) {
    if (control === "CLAVES_CARACTER") {
        $("#Caracter_sat").val(value);
    } else if (control === "CLAVES_INSTALACION") {
        $("#Clave_instalacion").val(value);
    } else if (control === "CLAVES_PERMISO") {
        $("#Modalidad_permiso").val(value);
    } else if (control === "CLAVES_DUCTOS") {
        $("#Clave_ductos00").val(value);
    } else if (control === "CLAVES_MEDIOS_TRANSPORTE") {
        $("#Clave_ductos01").val(value);
    } else if (control === "CLAVES_PRODUCTOS") {
        $("#Clave_producto").val(value);
    }
    $(modalListas).modal("toggle");
}

function getValueFromList(control) {
    var value = "";
    if (control === "CLAVES_CARACTER") {
        value = $("#Caracter_sat").val();
    } else if (control === "CLAVES_INSTALACION") {
        value = $("#Clave_instalacion").val();
    } else if (control === "CLAVES_PERMISO") {
        value = $("#Modalidad_permiso").val();
    } else if (control === "CLAVES_DUCTOS") {
        value = $("#Clave_ductos00").val();
    } else if (control === "CLAVES_MEDIOS_TRANSPORTE") {
        value = $("#Clave_ductos01").val();
    } else if (control === "CLAVES_PRODUCTO") {
        value = $("#Clave_producto").val();
    }
    return value;
}