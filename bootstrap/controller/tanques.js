/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getTanques.php";
var paramValidator = "Tanques";
var modalAdd = "";
var modalEdit = "#modal-tanques";
var modalOperation = "#modal-tanques-operaciones";
var modalListas = "#modal-tanques-listas";


$("body").on("shown.bs.modal", modalEdit, function (e) {
    var event = $(e.relatedTarget);
    var Identificador = event.data("identificador");
    var modalTitle = "Tanques del sistema";

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
                if (actual === value.clave) {
                    back = "style='background: yellow'";
                }
                modal.find("table tbody").append("<tr " + back + "><td onclick=\"setValueFromList('" + Identificador + "', '" + value.clave + "');\" class=\"pointer\" title=\"Click aqui para seleccionar clave [" + value.clave + "]\">" + value.clave + "</td><td>" + value.descripcion + "</td></tr>");
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
    if (control === "CLAVES_TANQUES") {
        $("#Prefijo_sat").val(value);
    } else if (control === "CLAVES_SISTEMAS_MEDICION") {
        $("#Sistema_medicion").val(value);
    }    
    $(modalListas).modal("toggle");
}

function getValueFromList(control) {
    var value = "";
    if (control === "CLAVES_TANQUES") {
        value = $("#Prefijo_sat").val();
    } else if (control === "CLAVES_SISTEMAS_MEDICION") {
        value = $("#Sistema_medicion").val();
    }
    return value;
}