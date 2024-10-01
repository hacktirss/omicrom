$(document).ready(function () {
    if ($("#UuidHd").val() === "-----") {
        $(".Facturado").show();
    } else {
        $(".Facturado").hide();
    }
    $(".enlace_timbre").show();
    console.log("TT " + $("#TtSuma").val());
    if ($("#TtSuma").val() > 1) {
        console.log($("#ImporteHd").val() + " " + $("#TtSuma").val());
        if (parseInt($("#TtSuma").val()) === parseInt($("#ImporteHd").val())) {
            $(".enlace_timbre").show();
        } else {
            $(".enlace_timbre").hide();
        }
    }

    $(".GuardaFactura").click(function () {
        var cont = $(this);
        console.log($(this).prop('checked'));
        Swal.fire({
            icon: 'question',
            iconColor: '#85929E',
            title: "Seguro de agregar el pago " + this.dataset.id,
            background: "#D5D8DC",
            showConfirmButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    type: "POST",
                    url: "getPagosDif.php",
                    dataType: "json",
                    cache: false,
                    data: {"Op": "AddFactura",
                        "IdPagos": this.dataset.id,
                        "UuidPago": this.dataset.uuid,
                        "IdFactura": $("#IdHd").val(),
                        "UuidFactura": this.dataset.uuid,
                        "Total": this.dataset.total},
                    success: function (data) {
                        Swal.fire({
                            icon: 'success',
                            position: 'top-end',
                            iconColor: 'green',
                            title: "Registro transferido con exito",
                            showConfirmButton: false,
                            toast: true,
                            timer: 2500
                        });
                        Visor();
                    }
                });
            }
        });
    });

    /*Edita abonos agreados*/
    $(".EditaAbonos").click(function () {
        var idRelacionCfdi = this.dataset.id;
        $(this).parent().parent().find('td:eq(2)').html("<input type='text' name='NvoImporte' class='NvoImporte' style='width:80px;'> <input type='button' name='Boton' value='Guardar' class='BotonActualiza'>")
        $(".BotonActualiza").click(function () {
            jQuery.ajax({
                type: "POST",
                url: "getPagosDif.php",
                dataType: "json",
                cache: false,
                data: {"Op": "CambioImporte", "IdRelacion": idRelacionCfdi, "Importe": $(".NvoImporte").val()},
                success: function (data) {
                    var sts;
                    if (data.sts) {
                        sts = "success";
                    } else {
                        sts = "error";
                    }
                    Swal.fire({
                        icon: sts,
                        position: 'top-end',
                        title: data.Msj,
                        toast: true,
                        timer: 2500,
                        showConfirmButton: false,
                    });
                    Visor();
                }
            });

        });
    });
    /*Elimina registros de relaciones*/
    $(".EliminaAbono").click(function () {
        var idRelacionCfdi = this.dataset.id;
        Swal.fire({
            icon: 'question',
            iconColor: '#85929E',
            title: "Seguro de eliminar el registro relacionado",
            background: "#D5D8DC",
            showConfirmButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    type: "POST",
                    url: "getPagosDif.php",
                    dataType: "json",
                    cache: false,
                    data: {"Op": "EliminaRelacion", "IdRelacion": idRelacionCfdi},
                    success: function (data) {
                        Swal.fire({
                            icon: 'success',
                            position: 'top-end',
                            iconColor: 'green',
                            title: "Registro eliminado con exito",
                            toast: true,
                            timer: 2500,
                            showConfirmButton: false,
                        });
                        Visor();
                    }
                });
            }
        });
    });
});