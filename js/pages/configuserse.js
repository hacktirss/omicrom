$(document).ready(function () {
    var busca = $("#JsBusca").val();

    $("#Name").val($("#JsNombre").val());
    $("#Uname").val($("#JsUsername").val());
    $("#Mail").val($("#JsMail").val());
    $("#Status").val($("#JsStatus").val());
    $("#Rol").val($("#JsRol").val());

    if (busca === "NUEVO") {
        $("#Boton").val("Agregar");
    } else {
        $("#Boton").val("Actualizar");
    }
    $("#busca1").val(busca);
    $("#busca2").val(busca);
    $("#Name").focus();

    $("#Generar").click(function (event) {
        event.preventDefault();
        $("#Passwd").val(generatePassword());
    });

    $("#form1").submit(function (event) {
        var busca = $("#JsBusca").val();
        if (!validaUsername($("#Uname"))) {
            event.preventDefault();
            clicksForm = 0;
            return false;
        }
        if (busca === "NUEVO") {
            var response = validaPassword2($("#Passwd"));
            console.log(response);
            if (response !== "OK") {
                $("#Response").html(response);
                clicksForm = 0;
                event.preventDefault();
                $("#Passwd").focus();
            }
        }
    });

    $("#form2").submit(function (event) {
        var response = validaPassword2($("#Passwd"));
        console.log(response);
        if (response !== "OK") {
            $("#Response").html(response);
            clicksForm = 0;
            event.preventDefault();
            $("#Passwd").focus();
        }
    });

    $("#Uname").change(function () {
        if ($("#Uname").val().length < 2) {
            $("#MensajeError").show();
            $("#MensajeError").html("El usuario necesita ser mayor a 2 caracteres");
            $("#Boton").hide();
        } else {
            $("#MensajeError").hide();
            $("#Boton").show();
        }
    });
});