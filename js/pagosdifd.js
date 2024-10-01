$(document).ready(function () {
    Visor();
    $(".Disp").click(function () {
        console.log("CLICK");
    });
    $("#Desap").hide();
    $("#PD").click(function () {
        if ($(this).prop('checked')) {
            $("#ContenidoFacturas").hide();
            $("#Desap").show();
        } else {
            $("#ContenidoFacturas").show();
            $("#Desap").hide();
        }
    });
});
function Visor() {
    jQuery.ajax({
        type: "POST",
        url: "getPagosDif.php",
        dataType: "json",
        cache: false,
        data: {"Op": "Visor", "UuidHd": $("#UuidHd").val(), "CliHd": $("#CliHd").val(), "IdFactura": $("#IdHd").val()},
        success: function (data) {
            console.log(data);
            $("#Contenido").html(data.Html);
        },
        error: function (data) {
            console.log("Error");
            console.log(data);
        }
    });
    console.log("KKEGA");
}