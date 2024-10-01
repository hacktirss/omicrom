/* global Swal */
$(document).ready(function () {
    $("#CerrarCt").click(function () {
        Op = $("#OpGrupo").val();
        console.log(Op);
        switch (Op) {
            case "0":
                console.log("ENTA?");
                $(location).attr('href', "cambiotur.php?op=cr");
                break;
            case "1":
//                Swal.fire({
//                    icon: "question",
//                    iconColor: "#5499C7",
//                    title: "Envios de archivos",
//                    input: 'radio',
//                    inputOptions: inputOptions,
//                    inputValidator: (value) => {
//                        if (!value) {
//                            return 'Seleccione una opcion';
//                        }
//                    }
//                }).then((result) => {
//                    if (result.isConfirmed) {
//                        jQuery.ajax({
//                            type: "POST",
//                            url: "getByAjax.php",
//                            dataType: "json",
//                            cache: false,
//                            data: {"Origen": "GeneraProcesoGrupoG", "Value": result.value, "Corte": $("#CorteHidden").val()},
//                            success: function () {
//                                Swal.fire({
//                                    title: "Archivos enviados con exito!"
//                                }).then(() => {
//                                    $(location).attr('href', "cambiotur.php?op=cr");
//                                });
//                            },
//                            error: function () {
//                                Swal.fire({
//                                    title: "Revisar el envio!"
//                                }).then(() => {
//                                    $(location).attr('href', "cambiotur.php?op=cr");
//                                });
//                            }
//                        });
//                    }
//                });
                $(location).attr('href', "cambiotur.php?op=cr");
                break;
        }
    });
});

const inputOptions = new Promise((resolve) => {
    resolve({
        'ALL': 'Todo',
        'SALES': 'Ventas',
        'BALANCE': 'Balance',
        'TURNO': 'Turno'
    });
});