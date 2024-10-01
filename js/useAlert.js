const BackGroundColorBase = "#E9E9E9";
const ColorButtonAcept = "#066";
const ColorButtonCancel = "#E74C3C";
const StyleBackdrop = "swal2-backdrop-show";
/*
 * Obtener el font de swet en base a multiples variables
 * @module ApiShowAlert
 * @author Alejandro Ayala Gonzalez <alejandro.ayala.gonz@hotmail.com>
 * @param {type} text - Texto que mostraremos en el alert
 * @param {type} typeInput - tipo de boton
 * @param {type} nameBotton - nombre del boton aceptar
 * @param {type} placeholder - texto visible en los inputs
 * @param {type} ConfirmButton - si es visible el boton de aceptar
 * @param {type} Icons - que icono se va a mostrar
 * @param {type} Timer - tiempo de espera por swet
 * @param {type} CancelButton - si mostramos boton cancelar
 * @param {type} ButtonValue - texto de boton 
 * @param {type} IdMovmiento - movimiento que se le tenga que dar seguimiento
 * @param {type} maxlengtInput - maximo texto aceptado
 * @param {type} HtmlText 
 * @param {type} iconColor
 * @returns {json}
 */
function alertTextValidation(text, typeInput = "textarea", nameBotton = "Guardar", placeholder = "Descripcion", ConfirmButton = true, Icons = "", Timer, CancelButton = true, ButtonValue = "", IdMovmiento, maxlengtInput = 1000, HtmlText = "", iconColor = "") {
    var myJSON = {
        "Value": "",
        "Sucess": false,
        "IdOrigen": IdMovmiento
    };
    Swal.fire({
        title: text,
        icon: Icons,
        iconColor: iconColor,
        timer: Timer,
        background: BackGroundColorBase,
        showConfirmButton: ConfirmButton,
        showCancelButton: CancelButton,
        inputValue: ButtonValue,
        confirmButtonColor: ColorButtonAcept,
        cancelButtonColor: ColorButtonCancel,
        confirmButtonText: nameBotton,
        inputPlaceholder: placeholder,
        input: typeInput,
        backdrop: StyleBackdrop,
        html: HtmlText,
        inputAttributes: {
            maxlength: maxlengtInput
        }
    }).then((result) => {
        if (result.isConfirmed) {
            myJSON = {
                "Value": result.value,
                "Sucess": true,
                "IdOrigen": IdMovmiento
            }
        }
        getResultado(myJSON);
    });
}