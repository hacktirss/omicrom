$(document).ready(function () {
    $(".Transferir").hide();
    $(".ImporteTransferir").hide();
    $("#Seleccionar").click(function () {
        $("#IdUnidSelec").val($("#IdUnidadSeleccionada").val());
        $("#IdUnidadSeleccionada").prop("disabled", true);
        $(".Transferir").show();
        $(".ImporteTransferir").show();
        $('input[data-idOrigenNum="' + $("#IdUnidadSeleccionada").val() + '"]').hide();
        $('input[data-idOrigenInput="' + $("#IdUnidadSeleccionada").val() + '"]').hide();
    });
    $(".Transferir").click(function () {
        var IdSuma = $("#IdUnidSelec").val();
        var IdResta = this.dataset.idunidad;
        var importeTarjeta = parseFloat(this.dataset.importe);
        var ImporteTransferencia = parseFloat($(this).parent().parent().find('td:eq(4)').find('input').val());
        if (IdResta !== IdSuma) {
            if (importeTarjeta >= ImporteTransferencia) {
                jQuery.ajax({
                    type: "POST",
                    url: "getByAjax.php",
                    dataType: "json",
                    cache: false,
                    data: {"Op": "TransfiereSaldo", "ImpTransf": ImporteTransferencia, "IdQuita": IdResta, "IdPone": IdSuma, "Usr": $("#NombreUsr").val()},
                    success: function (data) {
                        location.reload();
                    }
                });
            } else {
                alert("El importe ingresado es mayor al importe de la tarjeta");
            }
        } else {
            alert("Favor de transferir a otra tarjeta que no sea la misma");
        }
    });
});


