/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getCombustible.php";
var paramValidator = "Combustible";
var modalAdd = "";
var modalEdit = "#modal-combustibles";
var modalOperation = "#modal-combustibles-operaciones";
var modalListas = "#modal-combustibles-listas";


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
    var subControl = $("#Cve_producto_sat option:selected").text().split("|");

    $(".Identificador").val(Identificador);
    $(".ParamValidator").val(METHOD_GET + METHOD_GET);

    modal.find(".modal-title").html(modalTitle + ' <i class="fa fa-list"></i>');

    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {"validator": paramValidator, "paramValidator": METHOD_GET + METHOD_GET, "Identificador": Identificador, "SubIdentificador": (Identificador === "CLAVES_SUBPRODUCTO" ? $.trim(subControl[0]) : 0)},
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
    if (control === "CLAVES_PRODUCTO") {
        $("#Cve_producto_sat").val(value);
    } else if (control === "CLAVES_SUBPRODUCTO") {
        $("#Cve_sub_producto_sat").val(value);
    }
    $(modalListas).modal("toggle");
}

function getValueFromList(control) {
    var value = "";
    if (control === "CLAVES_PRODUCTO") {
        value = $("#Cve_producto_sat").val();
    } else if (control === "CLAVES_SUBPRODUCTO") {
        value = $("#Cve_sub_producto_sat").val();
    }
    return value;
}

function fillSubProduct(optionSelected) {
    var subControl = $("#Cve_producto_sat option:selected").text().split("|");
    //console.log("[" + $.trim(subControl[0]) + "]");
    //console.log(optionSelected);
    $.ajax({
        type: "POST",
        url: "bootstrap/ajax/getCombustible.php",
        data: {"validator": paramValidator, "paramValidator": METHOD_GET + METHOD_GET, "Identificador": "CLAVES_SUBPRODUCTO", "SubIdentificador": $.trim(subControl[0])},
        beforeSend: function (xhr, opts) {
            $("#Cve_sub_producto_sat").empty();
        },
        success: function (data) {
            var array = JSON.parse(data);
            //console.log(array);
            var lista = array.rows;
            jQuery.each(lista, function (name, value) {
                $("#Cve_sub_producto_sat").append("<option value='" + value.clave + "'>" + value.id + " | " + value.clave + " | " + value.descripcion_corta + "</option>");
            });
            if (optionSelected !== null) {
                $("#Cve_sub_producto_sat").val(optionSelected);
            }
        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
}

function gasConEtanol(optionSelected) {
    var clave = $("#Cve_producto_sat").val();
    if (clave === "PR07" || clave === "PR03") {
        if (optionSelected === "No") {
            $("#Radio-Si").html('<i class="icon fa fa-lg fa-circle-o"></i> Si');
            $("#Radio-No").html('<i class="icon fa fa-lg fa-check-circle-o"></i> No');
            $("#TieneEtanol").hide();
            //$("#TieneEtanol").addClass("OcultaCampo");
        } else {
            $("#Radio-Si").html('<i class="icon fa fa-lg fa-check-circle-o"></i> Si');
            $("#Radio-No").html('<i class="icon fa fa-lg fa-circle-o"></i> No');
            $("#TieneEtanol").show();
            //$("#TieneEtanol").removeClass("OcultaCampo");
        }
    } else {
        $("#TieneEtanol").hide();
    }
}

function siCombustible() {
    var clave = $("#Cve_producto_sat").val();
    if (clave === "PR07") {
        $("#TieneOctanaje").show();
    } else {
        $("#TieneOctanaje").hide();
    }
}

function getTipoCombustible(tipo) {
    if (tipo === "PR07") {
        return "Gasolina";
    } else if (tipo === "PR03") {
        return "Diesel";
    } else {
        return "Otros";
    }
}

function getEstado(estado) {
    if (estado === "Si") {
        return "Activo";
    } else {
        return "Inactivo";
    }
}

function Muestra(dato, clave) {
    if (clave === "EDS") {
        if (dato === "PR07") {
            $("#Octanaje").show();
            $("#Fosil").show();
            $("#subProducto").show();
            $("#Contiene_Etanol").show();
        } else if (dato === "PR03") {
            $("#Fosil").show();
            $("#subProducto").show();
        } else if (dato === "PR08") {
            $("#Gravedad").show();
            $("#Azufre").show();
            $("#Densid").show();
            $("#subProducto").show();
        } else if (dato === "PR09") {
            $("#Molar").show();
            $("#Calorifico").show();
            $("#subProducto").show();
        } else if (dato === "PR11") {
            $("#Fosil").show();
            $("#subProducto").show();
        } else if (dato === "PR12") {
            $("#Propano").show();
            $("#Butano").show();
            $("#subProducto").hide();
        }
    } else if (clave === "RCN" || clave === "TDP") {
        if (dato === "PR08") {
            $("#Gravedad").show();
            $("#Azufre").show();
        } else if (dato === "PR09") {
            $("#Molar").show();
            $("#Calorifico").show();
        }
    }
}