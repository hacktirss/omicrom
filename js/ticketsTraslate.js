$(document).ready(function () {
    $(".botonAnimatedMin").click(function () {
        var tt = parseFloat(0.00);
        $('input[type="checkbox"]:checked').each(function () {
            tt = tt + parseFloat(this.dataset.import);
        });
        $("#SumaTT").html(tt.toFixed(2));
    });

    $("#Transferir").click(function () {
        Swal.fire({
            icon: 'question',
            iconColor: '#D35400',
            title: "Este movimiento afectara tanto tickets como estados de cuenta. ¿Esta seguro de seguir con el proceso? ",
            background: "#EDBB99",
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonColor: '#58D68D',
            cancelButtonColor: '#EC7063',
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $('input[type="checkbox"]:checked').each(function () {
                    $.ajax({
                        type: "POST",
                        url: "getTicketsDisponibles.php",
                        data: {
                            "Op": "LanzarProcesoPorTicket",
                            "Ticket": $(this).val(),
                            "AuthName": $("#NameAuth").val(),
                            "IdAuth": $("#IdAuth").val()
                        },
                        success: function (data) {
                            var txtds = "Msj=Registros modificados con exito!";
                            window.location.href = "cxc.php?" + txtds;
                        },
                        error: function (jqXHR, ex) {
                            console.log("Status: " + jqXHR.status);
                            console.log("Uncaught Error.\n" + jqXHR.responseText);
                            console.log(ex);
                        }
                    });
                });
            }
        });
    });
});