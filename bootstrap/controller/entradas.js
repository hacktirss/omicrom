/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getEntradas.php";
var paramValidator = "Entradas";
var modalAdd = "";
var modalEdit = "#modal-entradas";
var modalOperation = "#modal-entradas-operaciones";
var modalListas = "#modal-entradas-listas";


$("body").on("shown.bs.modal", modalEdit, function (e) {
    var event = $(e.relatedTarget);
    var Identificador = event.data("identificador");
    var modalTitle = "Entradas del sistema";

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
            jQuery.each(lista, function (name, value) {
                var back = "";
                if (actual === value.id) {
                    console.log(value.clave);
                    back = "style='background: yellow'";
                }
                modal.find("table tbody").append("<tr " + back + " onclick=\"setValueFromList('" + Identificador + "', '" + value.id + "');\"><td class=\"pointer\" title=\"Click aqui para seleccionar clave [" + value.clave + "]\">" + value.clave + "</td><td>" + value.permiso + "</td><td>" + value.descripcion + "</td></tr>");
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
    if (control === "PROVEEDORES_TRANSPORTE") {
        $("#Transporte").val(value);
    } else if (control === "TERMINALES_ALMACENAMIENTO") {
        $("#Terminal").val(value);
    } 
    $(modalListas).modal("toggle");
}

function getValueFromList(control) {
    var value = "";
    if (control === "PROVEEDORES_TRANSPORTE") {
        value = $("#Transporte").val();
    } else if (control === "TERMINALES_ALMACENAMIENTO") {
        value = $("#Terminal").val();
    } 
    return value;
}