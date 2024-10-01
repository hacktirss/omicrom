
$(document).ready(function () {
    $(".Delete").click(function () {
        var idDt = this.dataset.idpd;

        Swal.fire({
            title: "¿Seguro de eliminar el registro?",
            icon: 'warning',
            iconColor: '#C0392B',
            background: "#E9E9E9",
            cancelButtonColor: '#E74C3C',
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: "Aceptar"
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": idDt, "Op": "EliminaEnvioPromoPorCliente"},
                    success: function (data) {
                        location.reload();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        });
    });

    $("#botonPromocion").click(function () {
        var idPromo = $("#busca").val();
        Swal.fire({
            title: "¿Seguro de lanzar la promoción?",
            icon: 'warning',
            iconColor: '#C0392B',
            background: "#E9E9E9",
            cancelButtonColor: '#E74C3C',
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: "Aceptar"
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": idPromo, "Op": "LanzamientoDePromo"},
                    success: function (data) {
                        location.reload();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        });
    });
});