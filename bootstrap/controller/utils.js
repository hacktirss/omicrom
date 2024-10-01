/* global Swal */

const METHOD_POST = "0";
const METHOD_GET = "1";
const METHOD_PUT = "2";
const METHOD_DELETE = "3";
const OPTION_FREE = "Liberar";
const OPTION_ADJUST = "Ajustar";
const OPTION_CHANGE = "Cambiar";
const OPTION_CLOSE = "Cerrar";
const OPTION_CANCEL = "Cancelar";
const OPTION_SAVE = "Guardar";

var count = 0;
var bntFormSubmit = 0;
var defaultOptionsDatatables = {
    "paging": false,
    "lengthChange": false,
    "searching": false,
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
    "columnDefs": [],
    "order": [[1, "asc"]]
};


function loadImg(tag) {
    var stringImg = "<div style='text-align: left; color: #009990; font-size: 11px;'><i class='icon fa fa-lg fa-chevron-right'></i>";
    if (count === 1) {
        stringImg = stringImg + "<i class='icon fa fa-lg fa-window-minimize'></i>";
        count = 0;
    } else {
        count++;
    }
    stringImg = stringImg + "</div>";
    tag.html(stringImg);
}

function callLoad(tag) {
    window.setInterval(function () {
        loadImg(tag);
    }, 1000);
}

function callVisor(tag, page) {
    window.setInterval(function () {
        tag.load(page);
    }, 1000);
}

function resizeDatatable(tag, options) {
    $(window).resize(function () {
        //Destroy the old Datatable
        tag.DataTable().destroy();
        tag.DataTable(options);
    });
}


function winmin(nombre, url) {
    window.open(url, nombre, "width=400,height=500,left=200,top=120,location=no");
}
/*
function confirmar(mensaje, url) {
    if (confirm(mensaje)) {
        document.location.href = url;
    }
}

function confirmarOperacion(url) {
    var mensaje = "Estas seguro de realizar esta operación";
    if (confirm(mensaje)) {
        document.location.href = url;
    }
}
*/

function winuni(nombre, url) {
    window.open(url, nombre, "width=860,height=500,left=200,top=120,location=no");
}

function wingral(nombre, url) {
    window.open(url, nombre, "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1230,height=600,left=100,top=50");
}
/*
function borrarRegistro(direccion, identificador, variable) {
    var mensaje = "Esta seguro que quiere borrar el registro " + identificador + "?";
    if (confirm(mensaje)) {
        var url = direccion + "?op=Si&" + variable + "=" + identificador;
        document.location.href = url;
    }
}
*/
function cancelar(url) {
    window.location = url;
}

function initDataTable(selector, columnDefs, order) {


    defaultOptionsDatatables.columnDefs = columnDefs;
    defaultOptionsDatatables.order = order;

    //init datatable
    var table = $(selector).DataTable($.extend(true, {
        sDom: "t<'row'<'col-sm-3'i><'col-sm-6 text-center'p><'col-sm-3 text-right'l>>",
        "pagingType": 'full_numbers'
    }, defaultOptionsDatatables || {}));

    //init tooltips (needed here due to the way datatables handles paging)
    table.$('span[data-toggle="tooltip"]').each(function () {
        $(this).tooltip();
    });

    /*table.on("responsive-resize", function (e, datatable, columns) {
     var count = columns.reduce(function (a, b) {
     return b === false ? a + 1 : a;
     }, 0);
     console.log(count + " column(s) are hidden");
     });*/

    $(window).resize(function () {
        table.columns.adjust().draw();
    });

    return table;

}

function transformarMayusculas(input) {
    input.value = input.value.toUpperCase();
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

var cleanUrl = function () {
    var clean_uri = location.protocol + "//" + location.host + location.pathname;
    window.history.replaceState({}, document.title, clean_uri);
};

function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function getActualFullDate() {
    var d = new Date();
    var day = addZero(d.getDate());
    var month = addZero(d.getMonth() + 1);
    var year = addZero(d.getFullYear());
    var h = addZero(d.getHours());
    var m = addZero(d.getMinutes());
    var s = addZero(d.getSeconds());
    return year + "-" + month + "-" + day + "T" + h + ":" + m;
}

function getActualHour() {
    var d = new Date();
    var h = addZero(d.getHours());
    var m = addZero(d.getMinutes());
    var s = addZero(d.getSeconds());
    return h + ":" + m + ":" + s;
}

function getActualDate() {
    var d = new Date();
    var day = addZero(d.getDate());
    var month = addZero(d.getMonth() + 1);
    var year = addZero(d.getFullYear());
    return year + "-" + month + "-" + day;
}

function toggleSwitch(switch_elem, on) {
    if (on) {
        if ($(switch_elem)[0].checked) {
            // nothing
        } else {
            $(switch_elem).trigger("click").attr("checked", "checked");
        }
    } else {
        if ($(switch_elem)[0].checked) {
            $(switch_elem).trigger('click').removeAttr("checked");
        } else {
            // nothing, already off
        }
    }
}


function getDispensario(claveDispensario) {
    var dispensario = "";
    switch (claveDispensario) {
        case "H" :
            dispensario = "HongYang";
            break;
        case "G" :
            dispensario = "Gilbarco";
            break;
        case "W" :
            dispensario = "Wayne";
            break;
        case "B" :
            dispensario = "Bennett";
            break;
        case "L" :
            dispensario = "LC";
            break;
        default:
            dispensario = "Others";
            break;
    }
    return dispensario;
}


/* -- Begin. Operations with datatables -- */

(function ($) {
    $.fn.cleanDataTable = function (datatable, defaultOrder) {
        //console.log($(this));
        if (datatable !== null) {
            datatable.fnSort([[defaultOrder, "asc"]]);
            datatable.fnFilter("");
            datatable.fnDraw();
            //datatable.fnPageChange("last");
        }
    };
})(jQuery);

(function ($) {
    $.fn.refreshDatatable = function (datatable) {
        if (datatable !== null) {
            datatable.fnDraw();
        }
    };
})(jQuery);


(function ($) {
    $.fn.getColumns = function (id) {
        let thisColumns = [];
        $.ajax({
            data: {"GetColumns": true, "idQuery": id},
            url: "ajax/CatalogosAjax.php",
            success: function (data) {
                data = JSON.parse(data);
                jQuery.each(data, function (key, value) {
                    thisColumns.push({"mData": value.trim()});
                });

                console.log(thisColumns);
            }
        });
        return thisColumns;
    };
})(jQuery);
/* -- End. Operations with datatables -- */