$(document).ready(function () {
    $(".BotonTipoVolumen").css({"background-color": "#099", "border": "2px solid #099","border-radius":"10px"});
    $(".ini").css({"background-color": "#066", "border": "2px solid #E74C3C"});
    $(".SelectCompensado").hide();
    $(".BotonTipoVolumen").click(function () {
        $(".BotonTipoVolumen").css({"background-color": "#099", "border": "2px solid #099"});
        var tipoVolumen = this.dataset.tipovolumen;
        if (tipoVolumen === "Bruto") {
            $(".SelectBruto").show();
            $(".SelectCompensado").hide();
        } else {
            $(".SelectCompensado").show();
            $(".SelectBruto").hide();
        }
        $(this).css({"background-color": "#066", "border": "2px solid #E74C3C"});
    });
});