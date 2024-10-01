/* global METHOD_GET, METHOD_PUT */

let fileAjax = "bootstrap/ajax/getAjuste.php";
let paramValidator = "Ajuste";
let modalAdd = "";
$(".close").click(function (e) {
    $("#modal-parametros-listas").fadeOut('slow');
});
$("#CambiodeImporte").click(function (e) {
    let event = $(e.relatedTarget);
    $("#modal-ajusta-ticket").modal("toggle");
    $(".modal-title").html("Ajuste de ticket");
});

$("#BuscaSum").click(function () {
    let Ticket = Monto = false;
    Ticket = pintaInputSinDatos("#IdTicket");
    Monto = pintaInputSinDatos("#MontoSum");
    var TipoMov = $("#TipoMovi").val();
    switch (TipoMov) {
        case "Importe":
            ObtenerTickets("importe", Monto, Ticket, fileAjax);
            break;
        case "Volumen":
            ObtenerTickets("volumen", Monto, Ticket, fileAjax);
            break;
    }
});
function ObtenerTickets(TipoValor, Monto, Ticket, fileAjax) {
    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {
            "Ticket": $("#IdTicket").val(),
            "Op": 4,
            "TipoValor": TipoValor
        },
        success: function (data) {
            if (Ticket && Monto) {
                muestraTickets(TipoValor, fileAjax, data);
            }
        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
}
function muestraTickets(TipoValor, fileAjax, data) {
    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {
            "Ticket": $("#IdTicket").val(),
            "Monto": $("#MontoSum").val() - data,
            "TipoValor": TipoValor,
            "Op": 1
        },
        beforeSend: function (xhr, opts) {
        },
        success: function (data) {
            $("#ContenidoTabla1").html(data);
        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
}
function pintaInputSinDatos(IdDato) {
    if ($(IdDato).val() == "") {
        $(IdDato).css({"background": "#FADBD8", "borderColor": "#E6B0AA"});
        pass = false;
    } else {
        $(IdDato).css({"background": "#FFF", "borderColor": "#D5D8DC"});
        pass = true;
    }
    return pass;
}
$("#TicketL").click(function () {
    $("input").hide();
    obtenTicketsLibres($("#TipoMovi").val().toLowerCase(), $("#MontoSum").val(), $("#TotalDisponible").val());
});

function obtenTicketsLibres(TipoValor, MontoSum, TotalDisponible) {
    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {
            "Ticket": $("#IdTicket").val(),
            "Op": 4,
            "TipoValor": TipoValor
        },
        success: function (data) {
            if (parseInt(TotalDisponible) >= parseInt(MontoSum - data)) {
                quantityExtractor(MontoSum, TipoValor, data);
            } else {
                $("#ContenidoTabla1").html("El monto requerido es mayor al disponible");
            }
        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
}

function quantityExtractor(MontoSum, TipoValor, dataAnt) {
    $.ajax({
        type: "POST",
        url: fileAjax,
        data: {
            "Ticket": $("#IdTicket").val(),
            "MontoSum": parseFloat(MontoSum - dataAnt),
            "Op": 3,
            "UsuarioM": $("#NameUser").val(),
            "TipoValor": TipoValor
        },
        success: function (data) {
            $("#ContenidoTabla1").html(data);
            $("#IdTicket").val("");
            $("#MontoSum").val("");
            setTimeout(function () {
                $("#ContenidoTabla1").html("");
                $("#modal-ajusta-ticket").modal("hide")
            }, 12000);
            setTimeout(window.location.href = 'remisiones.php?criteria=ini', 10000);

        },
        error: function (jqXHR, ex) {
            console.log("Status: " + jqXHR.status);
            console.log("Uncaught Error.\n" + jqXHR.responseText);
            console.log(ex);
        }
    });
}

$("#Recalculo").click(function () {
    $("input").hide();
    let IdTicket = $('input[name=TicketRevolvente]:checked', '#formModal2').val();
    if (IdTicket > 0) {
        console.log("Ticket " + IdTicket + " Monto: " + $("#MontoSum").val());
        $.ajax({
            type: "POST",
            url: fileAjax,
            data: {
                "Ticket": $("#IdTicket").val(),
                "TicketExt": IdTicket,
                "Monto": $("#MontoSum").val(),
                "Op": 2,
                "TipoValor": $("#TipoMovi").val()
            },
            beforeSend: function (xhr, opts) {
            },
            success: function (data) {
                $("#ContenidoTabla1").html("");
                $("#ContenidoTabla1").html(data);
                $("#IdTicket").val("");
                $("#MontoSum").val("");
                setTimeout(function () {
                    $("#ContenidoTabla1").html("");
                    $("#modal-ajusta-ticket").modal("hide")
                }, 12000);
                setTimeout(window.location.href = 'remisiones.php?criteria=ini', 10000);
            },
            error: function (jqXHR, ex) {
                console.log("Status: " + jqXHR.status);
                console.log("Uncaught Error.\n" + jqXHR.responseText);
                console.log(ex);
            }
        });
    } else {
        console.log("ERROR en ticket");
    }
});
