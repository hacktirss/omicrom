
function transformarMayusculas(input) {
    input.value = input.value.toUpperCase();
}

function validarFormulario(form) {
    console.log(form);
    // form.preventDefault();
    return true;
}

function validaNumero(input) {
    var regex = /^-?\d+\.?\d*$/;
    if (!regex.test(input)) {
        return false;
    }
    return true;
}

function validateFieldWithLabel(identifier) {
    $("label[for=" + identifier + "]").html("");
    $("#" + identifier).css("border", "");
    if (!validaNumero($("#" + identifier).val())) {
        $("label[for=" + identifier + "]").html("Favor de ingresar un número valido!").css("color", "red");
        $("#" + identifier).focus().css("border", "2px solid red");
        return false;
    }
    return true;
}

function validaUuid(input) {
    if (!/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/.test(input)) {
        return false;
    }
    return true;
}
function validateFieldUuid(form) {
    var uuid = form.find(".form-uuid").val();
    form.find(".form-uuid-label").html("");
    form.find(".form-uuid").css("border", "");
    if (!validaUuid(uuid)) {
        form.find(".form-uuid").focus().css("border", "2px solid red");
        form.find(".form-uuid-label").html("Favor de ingresar un UUID valido!").css("color", "red");
        return false;
    }
    return true;
}

function validaInputByCatatalogo(input, catalogo) {
    var regexTerminal = /^(PL\/[0-9]{1,5}\/DIS\/OM\/[0-9]{4})|(PL\/[0-9]{1,5}\/ALM\/[0-9]{4})|(PQ\/[0-9]{1,5}\/ALM\/[0-9]{4})|(PL\/[0-9]{1,5}\/ALM\/AE\/[0-9]{4})|(G\/[0-9]{1,5}\/ALM\/[0-9]{4})|(P\/[0-9]{1,5}\/ALM\/[0-9]{4})|(LP\/[0-9]{1,5}\/DIST\/AUT\/[0-9]{4})|(LP\/[0-9]{1,5}\/DIST\/PLA\/[0-9]{4})|(LP\/[0-9]{1,5}\/DIST\/DUC\/[0-9]{4})|(G\/[0-9]{1,5}\/LPD\/[0-9]{4})|(LP\/[0-9]{1,5}\/ALM\/[0-9]{4})|(G\/[0-9]{1,5}\/LPA\/[0-9]{4})|(LP\/[0-9]{1,5}\/DIST\/REP\/[0-9]{4})|(PL\/[0-9]{1,5}\/DIS\/DUC\/[0-9]{4})$/;
    var regexTransporte = /^(PL\/[0-9]{1,5}\/TRA\/OM\/[0-9]{4})|(PL\/[0-9]{1,5}\/TRA\/DUC\/[0-9]{4})|(PL\/[0-9]{1,5}\/TRA\/TM\/[0-9]{4})|(PQ\/[0-9]{1,5}\/TRA\/DUC\/[0-9]{4})|(G\/[0-9]{1,5}\/TUP\/[0-9]{4})|(G\/[0-9]{1,5}\/SAB\/[0-9]{4})|(G\/[0-9]{1,5}\/TRA\/OM\/[0-9]{4})|(G\/[0-9]{1,5}\/TRA\/[0-9]{4})|(GN\/[0-9]{1,5}\/P\/TRA\/DUC\/[0-9]{4})|(GN\/[0-9]{1,5}\/TRA\/DUC\/[0-9]{4})$/;
    var regexPermiso = /PL\/\d{4,5}\/EXP\/ES\/20\d{2}$/;

    if (catalogo === "TERMINALES_ALMACENAMIENTO" && regexTerminal.test(input.val())) {
        return true;
    } else if (catalogo === "PROVEEDORES_TRANSPORTE" && regexTransporte.test(input.val())) {
        return true;
    } else if (catalogo === "VARIABLES_EMPRESA" && regexPermiso.test(input.val())) {
        return true;
    }
    return false;
}

function setHtmlByForLabel(subject, text) {
    var label = $("label");
    label.each(function () {
        // get the corresponding input element of the label:
        var nameFor =$(this).attr("for");
        if(nameFor === subject){
            $(this).html('<span style="color: red;">' + text + '</span>');
        }
    });
}