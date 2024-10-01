/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getParametros.php";
var paramValidator = "Parametros";
var modalAdd = "";
var modalEdit = "#modal-parametros";
var modalOperation = "#modal-parametros-operaciones";
var modalListas = "#modal-parametros-listas";


$("body").on("shown.bs.modal", modalEdit, function (e) {
    var event = $(e.relatedTarget);
    var Identificador = event.data("identificador");
    var modalTitle = "Parametros del sistema";

    $(".Identificador").val(Identificador);
    var modal = $(this);

    if ($.isNumeric(Identificador)) {
        $(".ParamValidator").val(METHOD_PUT);
        modal.find(".modal-title").html(modalTitle + ' <i class="fa fa-edit"></i>');

        $.ajax({
            type: "POST",
            url: fileAjax,
            data: {"validator": paramValidator, "paramValidator": METHOD_GET, "Identificador": Identificador},
            beforeSend: function (xhr, opts) {

            },
            success: function (data) {
                var array = JSON.parse(data);
                console.log(array);

            },
            error: function (jqXHR, ex) {
                console.log("Status: " + jqXHR.status);
                console.log("Uncaught Error.\n" + jqXHR.responseText);
                console.log(ex);
            }
        });
    }
});


$("body").on("shown.bs.modal", modalListas, function (e) {
    var event = $(e.relatedTarget);
    var Identificador = event.data("identificador");
    var modalTitle = "Listas definidas por el SAT ";
    var modal = $(this);

    $(".Identificador").val(Identificador);
    $(".ParamValidator").val(METHOD_GET + METHOD_GET);

    modal.find(".modal-title").html(modalTitle + ' <i class="fa fa-list"></i>');

    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {"validator": paramValidator, "paramValidator": METHOD_GET + METHOD_GET, "Identificador": Identificador},
        beforeSend: function (xhr, opts) {

        },
        success: function (data) {
            var array = JSON.parse(data);
            //console.log(array);
            var lista = array.rows;
            var actual = getValueFromList(Identificador);
            modal.find("table tbody").empty();
            cnt = 0;
            jQuery.each(lista, function (name, value) {
                cnt++;
                var back = "";
                if (cnt % 2 == 0) {
                    back = "";
                } else {
                    back = "style='background: #D5D8DC'";
                }

                if (actual === value.clave) {
                    console.log(value.clave);
                    back = "style='background: yellow'";
                }
                modal.find("table tbody").append("<tr " + back + "><td onclick=\"setValueFromList('" + Identificador + "', '" + value.clave + "');\" class=\"pointer\" title=\"Click aqui para seleccionar clave [" + value.clave + "]\" style=\"text-align:center;\">" + value.clave + "</td><td>" + value.descripcion + "</td></tr>");
            });

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