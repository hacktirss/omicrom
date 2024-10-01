$(document).ready(function () {
    $("#autocomplete").focus();
    /*
     * Seleccionamos la unidad a la que le queremos agrega saldo de otras unidades tipo balance
     */
    $("#SeleccionarTarjeta").click(function () {
        $("#IdUnidadSeleccionada").val($("#Unidad").val());
        $("#SeleccionarTarjeta").hide();
        $("#Unidad").prop("disabled", true);
        jQuery.ajax({
            type: "POST",
            url: "getByAjax.php",
            dataType: "json",
            cache: false,
            data: {"Op": "Saldo", "IdUnidad": $("#Unidad").val()},
            success: function (data) {
                $("#ResultValues").html(data.ImporteUnidad);
            }
        });
    });
    /*
     * Revisamos que el importe cuadre con lo que se tiene en cada unidad
     */
    $(".ImporteUnidad").change(function () {
        sessionStorage.setItem("ImporteTransferencia", $(this).val());
        if (parseInt($("#IdUnidadSeleccionada").val()) !== parseInt(this.dataset.idunidad)) {
            if (parseInt(this.dataset.importeunidad) >= parseInt($(this).val())) {
                jQuery.ajax({
                    type: "POST",
                    url: "getByAjax.php",
                    dataType: "json",
                    cache: false,
                    data: {"Op": "TrabajaSaldos", "IdUnidadExp": this.dataset.idunidad, "IdUnidadImp": $("#IdUnidadSeleccionada").val()},
                    success: function (data) {
                        $("#ResultValues").html(data.ImporteUnidad);
                    }
                });
            } else {
                alert("El importe es mayor al que tienes disponible. ¡Favor de verificar sus datos!");
                $(this).val(0)
            }
        } else {
            alert("No se puede transferir a la misma tarjeta");
        }
    });
    /*
     * Click para transferir el importe de una unidad a otra, las dos pertenecen al tipo Balance
     */
    $(".AddImporte").click(function () {
        if ($("#IdUnidadSeleccionada").val() > 0) {
            jQuery.ajax({
                type: "POST",
                url: "getByAjax.php",
                dataType: "json",
                cache: false,
                data: {"Op": "TransfiereSaldo", "IdQuita": this.dataset.idunidad, "IdPone": $("#IdUnidadSeleccionada").val(), "ImpTransf": sessionStorage.getItem("ImporteTransferencia"), "Usr": $("#UsrA").val()},
                success: function (data) {
                    location.reload();
                }
            });
        } else {
            alert("Selecciona una unidad");
        }
    });
});
