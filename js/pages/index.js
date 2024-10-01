$(document).ready(function () {
    var Fondo1 = "https://corporativos.dyndns.org/soporte/lib/imgs/Fondo1.png";
    var Fondo2 = "https://corporativos.dyndns.org/soporte/lib/imgs/Fondo2.png";
    var Fondo3 = "https://corporativos.dyndns.org/soporte/lib/imgs/Fondo3.png";
    $("#Usuario").focus();
    $("#Msj").html($("#htmlResponse").val());
    $("#spiner").hide();
    $("#Recordarme").click(function (rs) {
        if ($('#Recordarme').prop('checked')) {
            Swal.fire({
                title: "Advertencia <br>Solo recordar contraseña en caso de ser computadora personal",
                icon: "warning",
                background: "#D5DBDB",
                backdrop: true,
                toast: true,
                iconColor: "#E74C3C",
                showConfirmButton: true,
                confirmButtonText: "Aceptar",
                showCancelButton: true,
                cancelButtonText: "Cancelar",
                cancelButtonColor: '#d33',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                console.log(result);
                if (result.isConfirmed) {
                    $("#Recordarme").val("Ok");
                } else {
                    $("#Recordarme").val("");
                    $("#Recordarme").prop("checked", false);
                }
            });
        } else {
            $("#Recordarme").val("");
        }
    });
    $("#Usuario").change(function () {
        if ($("#Usuario").val().length < 2) {
            $("#MensajeError").show();
            $("#MensajeError").html("El usuario necesita ser mayor a 2 caracteres");
            $("#Entrar").hide();
            sinEnter();
        } else {
            $("#MensajeError").hide();
            $("#Entrar").show();
        }
    });
    $("#Login").submit(function (event) {
        event.preventDefault();
        jQuery.ajax({
            type: "POST",
            url: "auth_ajax.php",
            dataType: "json",
            cache: false,
            data: {"username": $("#Usuario").val(), "password": $("#Contrasenia").val(), "recordar": $("#Recordarme").val()},
            beforeSend: function (xhr) {
                $("#Msj").hide();
                $("#Fail").hide();
                $("#myLoader").modal("toggle");
            },
            success: function (data) {
                //console.log(data);
                console.log("Success");
                if (data.success) {
                    window.location = data.redirect;
                } else {
                    var count = 0;
                    if (data.count !== null && data.count !== "") {
                        count = parseInt(data.count) + 1;
                    }
                    if (data.count < 5) {
                        $("#Msj").html("<strong>" + data.message + "</strong>");
                        if (count > 0) {
                            $("#Fail").html("Intento fallido " + count);
                        }
                        $("#Contrasenia").val("");
                        $("#myLoader").modal("toggle");
                        $("#Msj").show();
                        $("#Fail").show();
                        $("#Usuario").focus();
                    } else {
                        window.location = "locked.php?Msj=" + data.message;
                    }
                }
            },
            error: function (jqXHR, textStatus) {
                console.log(jqXHR);
                console.log("error");
                window.location = "index.php?Msj=Error";
                $("#Msj").html(textStatus);
            }
        });

    });

    $("#Contrasenia").focus(function () {
        $("#Contrasenia").attr("type", "password");
    });
    $("#PasswordEye").mousedown(function () {
        $(".toggle-password").toggleClass("fa-eye fa-eye-slash");
        $("#Contrasenia").attr("type", "text");
    }).mouseup(function () {
        $(".toggle-password").toggleClass("fa-eye fa-eye-slash");
        $("#Contrasenia").attr("type", "password");
    });

    var ColorFocus = 'background: rgba(255,255,255,0.9) !important;';
    var SinFocus = 'background: rgba(255,255,255,0.4) !important;';
    $("#Img").css({
        'background': 'url("' + Fondo1 + '") no-repeat center right',
        'background-size': '100%'
    });
    $("#Img1").attr('style', function (i, s) {
        return s + ColorFocus
    });
    var i = 2;
    var interval = setInterval(function () {
        if (i > 3) {
            i = 1;
        }
        $("#Img1").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img2").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img3").attr('style', function (i, s) {
            return s + SinFocus
        });
        if (i == 1) {
            $("#Img").css({
                'background': 'url("' + Fondo1 + '") no-repeat center right',
                'background-size': '100%'
            });
            $("#Img1").attr('style', function (i, s) {
                return s + ColorFocus
            });
        } else if (i == 2) {
            $("#Img").css({
                'background': 'url("' + Fondo2 + '") no-repeat center right',
                'background-size': '100%'
            });
            $("#Img2").attr('style', function (i, s) {
                return s + ColorFocus
            });
        } else {
            $("#Img").css({
                'background': 'url("' + Fondo3 + '") no-repeat center right',
                'background-size': '100%'
            });
            $("#Img3").attr('style', function (i, s) {
                return s + ColorFocus
            });
        }

        i++;
    }, 10000);
    $("#Img").css({
        'background': 'url("' + Fondo1 + '") no-repeat center right',
        'background-size': '100%'
    });
    $("#Img1").click(function () {
        $("#Img3").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img2").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img").css({
            'background': 'url("' + Fondo1 + '") no-repeat center right',
            'background-size': '100%'
        });
        $("#Img1").attr('style', function (i, s) {
            return s + ColorFocus
        });
        clearInterval(interval);
    });
    $("#Img2").click(function () {
        $("#Img1").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img3").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img").css({
            'background': 'url("' + Fondo2 + '") no-repeat center right',
            'background-size': '100%'
        });
        $("#Img2").attr('style', function (i, s) {
            return s + ColorFocus
        });
        clearInterval(interval);
    });
    $("#Img3").click(function () {
        $("#Img1").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img2").attr('style', function (i, s) {
            return s + SinFocus
        });
        $("#Img").css({
            'background': 'url("' + Fondo3 + '") no-repeat center right',
            'background-size': '100%'
        });
        $("#Img3").attr('style', function (i, s) {
            return s + ColorFocus
        });
        clearInterval(interval);
    });
});
function sinEnter() {
    window.addEventListener("keypress", function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
        }
    }, false);
}