$(document).ready(function () {
    $("#Bonificar").click(function () {

        Swal.fire({
            title: "Acumulación a monedero",
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: "Agregar",
            html: "<table style='width:100%;'>\n\
                    <tr><td style='width:40%;text-align:right;'>Ticket:</td><td><input type='number' name='Ticket' id='Ticket'></td></tr>\n\
                    <tr><td style='text-align:right;'>Unidad: </td><td><input type='text' name='Unidad' id='Unidad'></td></tr></table>"
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    type: "POST",
                    url: "getPuntos.php",
                    dataType: "json",
                    cache: false,
                    beforeSend: function () {
                        alertTextValidation("Cargando, favor de esperar ... ", "", "", "", false, "warning", 10000, false);
                    },
                    data: {"Op": "AcumulaPuntosV2", "Ticket": $("#Ticket").val(), "ClaveUnidad": $("#Unidad").val()},
                    success: function (data) {
                        alertTextValidation(data.Html.Msj, "", "Guardar", "", false, "success", 10000, false);
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                    }
                });
            }
        });


    });
});