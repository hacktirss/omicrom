$(document).ready(function () {
    $(".tiposCliente").click(function () {
        var idCli = this.dataset.idcli;
        var Mes = this.dataset.mes;
        var MesNo = this.dataset.mesno;
        var Name = this.dataset.name;
        var Anio = this.dataset.anio;
        var idUsr = $("#idUsuario").val();
        jQuery.ajax({
            type: "POST",
            url: "getByAjax.php",
            dataType: "json",
            cache: false,
            data: {"Op": "ValidaExistencia", "IdCliente": idCli, "Mes": Mes, "Anio": Anio, "MesNo": MesNo},
            success: function (data) {
                console.log("Validando ");
                console.log(data);
                if (data.idRegistro == null) {
                    GeneraIngresaOActualiza(idCli, Mes, Anio, MesNo, Name, idUsr, "Agregar")
                } else {
                    Swal.fire({
                        title: "Ya contienes un saldo.<br> ¿Seguro de continuar con este proceso?",
                        showConfirmButton: true,
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            GeneraIngresaOActualiza(idCli, Mes, Anio, MesNo, Name, idUsr, "Actualizar")
                        }
                    });
                }
            }
        });
    });
    $("#Enviar").click(function () {
        var mesI = parseInt($("#FechaI").val().split("-")[1]);
        var mesF = parseInt($("#FechaF").val().split("-")[1]);
//        if (mesI == mesF) {
//            return true;
//        } else {
//            Swal.fire({
//                title: "Las fechas deben de estar dentro del mismo periodo mensual"
//            });
//            return false;
//        }
    });
});

function GeneraIngresaOActualiza(idCli, Mes, Anio, MesNo, Name, idUsr, TipoMov) {
    var tipoMovmiento = TipoMov === "Agregar" ? "Ingresa_Cxc" : "Actualizar_Cxc";
    Swal.fire({
        title: "Saldo inicial para el mes de " + Mes + " del " + Anio + "<br>" + Name,
        showConfirmButton: true,
        showCancelButton: true,
        html: "Importe",
        input: "text",
        confirmButtonText: "Agregar"
    }).then((result) => {
        if (result.isConfirmed) {
            console.log(tipoMovmiento);
            jQuery.ajax({
                type: "POST",
                url: "getByAjax.php",
                dataType: "json",
                cache: false,
                data: {"Op": tipoMovmiento, "Importe": result.value, "IdCliente": idCli, "Mes": Mes, "Anio": Anio, "MesNo": MesNo, "IdUsr": idUsr},
                success: function (data) {
                    Swal.fire({
                        title: "¿Desea recargar la pagina?",
                        icon: "question",
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: "Recargar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                }
            });
        }
    });
}