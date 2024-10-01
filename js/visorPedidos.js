$("#PP").click(function () {
    GetAjaxPedidos("PP");
    PintaPedidosClass("PP");
    $("#ConecntDetalle").html("");
});
$("#PA").click(function () {
    GetAjaxPedidos("PA");
    PintaPedidosClass("PA");
    $("#ConecntDetalle").html("");
});
$("#PEP").click(function () {
    GetAjaxPedidos("PEP");
    PintaPedidosClass("PEP");
    $("#ConecntDetalle").html("");
});
$("#PC").click(function () {
    GetAjaxPedidos("PC");
    PintaPedidosClass("PC");
    $("#ConecntDetalle").html("");
});
function PintaPedidosClass(vari) {
    $(".PedidosClass").css({"background-color": "#099", "border": "1px solid #099"});
    $("#" + vari).css({"background-color": "#28B463", "border": "1px solid #566573"});
}
function PintaPedidosClassTwo(vari) {
    $(".PedidosClass2").css({"background-color": "#099", "border": "1px solid #099"});
    $("#" + vari).css({"background-color": "#28B463", "border": "1px solid #566573"});
}
function LevelTwo() {
    $(".PedidosClass2").click(function (data) {
        data.stopImmediatePropagation();
        var Type = data.delegateTarget.dataset.idRegistro;
        var id = data.delegateTarget.id;
        PintaPedidosClassTwo(id);
        $('#ConecntDetalle').load('getDetallePedidos.php?IdPedido=' + Type, function (response, status, xhr) {

            if (status === "error") {
                var msg = "Sorry but there was an error: ";
                console.log(msg + xhr.status + " " + xhr.statusText);
            }
        });
    });
}
function GetAjaxPedidos(pedido) {
    $('#ConecntPedidos').load('getPedidos.php?Pedido=' + pedido + '&Ini=' + $("#FechaInicialV").val() + '&Fin=' + $("#FechaFinalV").val(), function (response, status, xhr) {
        if (status === "error") {
            var msg = "Sorry but there was an error: ";
            console.log(msg + xhr.status + " " + xhr.statusText);
        }
    });
}