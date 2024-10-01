/* global METHOD_GET, METHOD_PUT */

var fileAjax = "bootstrap/ajax/getDispensarios.php";
var paramValidator = "Dispensarios";
var modalAdd = "";
var modalEdit = "#modal-dispensarios";;

$("body").on("shown.bs.modal", modalEdit, function (e) {
    var event = $(e.relatedTarget);
    var Identificador = event.data("identificador");
    var modalTitle = "Ajustar fecha de calibración";

    $(".Identificador").val(Identificador);
    var modal = $(this);
    if (Identificador === "Dispensarios") {
        $(".ParamValidator").val(METHOD_PUT);
        modal.find(".modal-title").html(modalTitle + ' <i class="fa fa-edit"></i>');
    }
});
