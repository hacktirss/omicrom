function mostrarToast(Msj, sts) {
    var toast = document.getElementById("mitoast");
    if (sts) {
        $("#colorC").css({"background-color": "#2ECC71","color":"#212F3D"});
    } else {
        $("#colorC").css({"background-color": "#EC7063","color":"#212F3D"});
    }
    toast.className = "mostrar";
    $(".toast-body").html(Msj);
    if (sts) {
        setTimeout(function () {
            toast.className = toast.className.replace("mostrar", "");
        }, 5000);
    }
}

function cerrarToast() {
    var toast = document.getElementById("mitoast");
    toast.className = "cerrar";
    toast.className = toast.className.replace("cerrar", "");
}

/*
 function mostrarToast() {
 $("mitoast").fadeIn("slow",0.2);
 }
 
 function cerrarToast() {
 $("mitoast").fadeOut(5000);
 }*/