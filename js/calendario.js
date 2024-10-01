$(document).ready(function () {
    /*Autocompletado de los calendarios */
    $('#basicAuto').autocomplete({
        serviceUrl: 'ListBootstrap.php?Op=BuscaCli',
        responseTime: 2000,
        cache: false,
        transformResult: function (response) {
            return {
                suggestions: $.map($.parseJSON(response), function (item, value) {
                    return {value: value + ".-" + item, data: value, id: value};
                })

            };
        },
        onSelect: function (e, term, item) {
            console.log("E " + e + "Term " + term + " item" + item);
        },
        success: function (data) {
            console.log("SUCCES" + JSON.parse(data));
            response(JSON.parse(data));
        }
    });

    $('#Terminal').autocomplete({
        serviceUrl: 'ListBootstrap.php?Op=BuscaTerminal',
        responseTime: 2000,
        cache: false,
        transformResult: function (response) {
            return {
                suggestions: $.map($.parseJSON(response), function (item, value) {
                    console.log("ITEM " + item + " VALUE " + value);
                    return {value: value + ".-" + item, data: value, id: value};
                })

            };
        },
        onSelect: function (e, term, item) {
            console.log("E " + e + "Term " + term + " item" + item);
        },
        success: function (data) {
            console.log("SUCCES" + JSON.parse(data));
            response(JSON.parse(data));
        }
    });
    /*--------*/
    /*Aceptamos la venta del lado del proveedor*/
    $("#AceptarVenta").click(function () {
        Swal.fire({
            title: "Se agregara la carga y la salida del producto  <br>¿Seguro de aceptar el pedido " + $("#bt").val() + "?",
            position: "center",
            icon: "info",
            showConfirmButton: true,
            confirmButtonText: "Aceptar",
            showCancelButton: true,
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: "ListBootstrap.php?Op=AceptarPedido&IdPago=" + $("#bt").val() + "&Id_Cia=" + $("#Id_Cia").val(),
                    success: function (data) {
                        console.log(data);
                        var href = "calendarRm.php";
                        $(location).attr('href', href);
                        return true;
                    },
                    error: function (jqXHR, ex) {
                        console.log("Status: " + jqXHR.status);
                        console.log("Uncaught Error.\n" + jqXHR.responseText);
                        console.log(ex);
                    }
                });
            }
        });
        return false;
    });
    /*Cancelamos el pedido*/
    $("#CancelarVenta").click(function () {
        Swal.fire({
            title: "¿Seguro de cancelar el pedido " + $("#bt").val() + "?",
            position: "center",
            icon: "error",
            showConfirmButton: true,
            confirmButtonText: "Aceptar",
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: "ListBootstrap.php?Op=CancelarPedido&IdPago=" + $("#bt").val(),
                    success: function (data) {
                        console.log("Delete " + data);
                        var href = "calendarRm.php";
                        $(location).attr('href', href);
                        return true;
                    },
                    error: function (jqXHR, ex) {
                        console.log("Status: " + jqXHR.status);
                        console.log("Uncaught Error.\n" + jqXHR.responseText);
                        console.log(ex);
                    }
                });
            }
        });
        return false;
    });
});
