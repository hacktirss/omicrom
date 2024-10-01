$(document).ready(function () {
    /*Si se encuentra cuadrado quitamos las columnas */
    if ($("#AbonoTotal").val() == "Cuadrado") {
        $(".cuadrado").hide();
    }
    /*
     * Damos click en Transferir importe del pago a la Unidad tipo balance
     */
    $(".AddImporte").click(function () {
        var IdUnidad = this.dataset.idunidad;
        var ImporteAbono = $(this).parent().parent().find('td:eq(5)').find('input').val();
        jQuery.ajax({
            type: "POST",
            url: "getByAjax.php",
            dataType: "json",
            cache: false,
            data: {"Op": "IngresaAbono", "IdUnidad": IdUnidad, "ImporteAbono": ImporteAbono, "IdPago": $("#IdPagoT").val(), "UsrName": $("#NombreUsr").val()},
            success: function (data) {
                console.log(data);
                if (data.Sucess) {
                    location.reload();
                } else {
                    alert(data.Msj);
                }
            }
        });
    });

    /*Elimina relacion*/
    $(".DeleteDif").click(function () {
        var IdLog = this.dataset.idnvo;
        var IdUnidad = this.dataset.idunidad;
        var IAbono = this.dataset.importeabono;
        jQuery.ajax({
            type: "POST",
            url: "getByAjax.php",
            dataType: "json",
            cache: false,
            data: {"Op": "DeleteUL", "IdUnidad": IdUnidad, "IdLogUnidades": IdLog, "Importe": IAbono},
            success: function (data) {
                console.log(data.Msj);
                if (data.Sucess) {
                    location.reload();
                } else {
                    alert(data.Msj);
                }
            }
        });

    });
});


