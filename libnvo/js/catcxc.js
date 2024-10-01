$(document).ready(function () {
    var TotalSeleccionado = 0;
    var count = 0;
    var count0 = 0;
    var count1 = 0;
    var count2 = 0;
    var count3 = 0;
    var count4 = 0;
    var count5 = 0;
    var count6 = 0;
    var count7 = 0;
    var count8 = 0;
    var count9 = 0;
    var count10 = 0;
    var count11 = 0;
    var count12 = 0;
    var count13 = 0;
    var count14 = 0;
    $("#checkall").click(function () {
        var fila;
        if (count == 0) {
            fila = parseFloat($(".Count00").find("td").eq(9).html());
            console.log("Fila" + fila);
            fila = fila + parseFloat($(".Count01").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count02").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count03").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count04").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count05").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count06").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count07").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count08").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count09").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count010").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count011").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count012").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count013").find("td").eq(9).html());
            fila = fila + parseFloat($(".Count014").find("td").eq(9).html());
            count = 1;
        } else {
            fila = 0;
            count = 0;
        }
        TotalSeleccionado = fila;
        $(".sumChecks").html(fila.toFixed(2));

    });
    $(".Count0 input").click(function () {
        $(".Count00").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count0 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count0 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count0 = 0;
                }
            } else {
                if (count0 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count0 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count0 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count1 input").click(function () {
        $(".Count01").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count1 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count1 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count1 = 0;
                }
            } else {
                if (count1 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count1 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count1 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count2 input").click(function () {
        $(".Count02").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count2 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count2 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count2 = 0;
                }
            } else {
                if (count2 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count2 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count2 = 0;
                }
            }

            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count3 input").click(function () {
        $(".Count03").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count3 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count3 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count3 = 0;
                }
            } else {
                if (count3 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count3 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count3 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count4 input").click(function () {
        $(".Count04").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count4 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count4 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count4 = 0;
                }
            } else {
                if (count4 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count4 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count4 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count5 input").click(function () {
        $(".Count05").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count5 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count5 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count5 = 0;
                }
            } else {
                if (count5 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count5 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count5 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count6 input").click(function () {
        $(".Count06").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count6 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count6 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count6 = 0;
                }
            } else {
                if (count6 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count6 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count6 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count7 input").click(function () {
        $(".Count07").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count7 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count7 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count7 = 0;
                }
            } else {
                if (count7 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count7 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count7 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count8 input").click(function () {
        $(".Count08").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count8 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count8 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count8 = 0;
                }
            } else {
                if (count8 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count8 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count8 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count9 input").click(function () {
        $(".Count09").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count9 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count9 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count9 = 0;
                }
            } else {
                if (count9 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count9 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count9 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count10 input").click(function () {
        $(".Count010").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count10 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count10 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count10 = 0;
                }
            } else {
                if (count10 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count10 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count10 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count11 input").click(function () {
        $(".Count011").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count11 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count11 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count11 = 0;
                }
            } else {
                if (count11 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count11 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count11 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count12 input").click(function () {
        $(".Count012").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count12 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count12 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count12 = 0;
                }
            } else {
                if (count12 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count12 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count12 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count13 input").click(function () {
        $(".Count013").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count13 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count13 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count13 = 0;
                }
            } else {
                if (count13 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count13 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count13 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".Count14 input").click(function () {
        $(".Count014").each(function () {
            var Pagado = $(this).find("td").eq(9).html();
            if (count == 1) {
                if (count14 == 0) {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count14 = 1;
                } else {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count14 = 0;
                }
            } else {
                if (count14 == 0) {
                    TotalSeleccionado = parseFloat(Pagado) + TotalSeleccionado;
                    count14 = 1;
                } else {
                    TotalSeleccionado = TotalSeleccionado - Number(Pagado);
                    count14 = 0;
                }
            }
            $(".sumChecks").html(TotalSeleccionado.toFixed(2));
        });
    });
    $(".sumChecks").html(TotalSeleccionado);
});