/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function validaPassword(elementId) {

    var $regexLower = /[a-z]+/;
    var $regexUpper = /[A-Z]+/;
    var $regexNumber = /[0-9]+/;
    var $regexSpecial = /[$&*@#?<>_+-]+/;

    if (elementId.val().length < 8) {
        $("#mensaje").html("Contraseña invalida, la longitud minima es de 8 caracteres");
        return false;
    }

    if (!elementId.val().match($regexLower)) {
        $("#mensaje").html("Contraseña invalida, debe contener al menos una minuscula");
        return false;
    }
    if (!elementId.val().match($regexUpper)) {
        $("#mensaje").html("Contraseña invalida, debe contener al menos una mayuscula");
        return false;
    }
    if (!elementId.val().match($regexNumber)) {
        $("#mensaje").html("Contraseña invalida, debe contener al menos un numero");
        return false;
    }
    if (!elementId.val().match($regexSpecial)) {
        $("#mensaje").html("Contraseña invalida, debe contener al menos un caracter especial");
        return false;
    }
    console.log("Password valido");
    return true;

}
function validaPassword2(elementId) {

    var $regexLower = /[a-z]+/;
    var $regexUpper = /[A-Z]+/;
    var $regexNumber = /[0-9]+/;
    var $regexSpecial = /[$&*@#?<>_+-]+/;

    if (elementId.val().length < 8) {
        return "Contraseña invalida, la longitud minima es de 8 caracteres";
    }

    if (!elementId.val().match($regexLower)) {
        return "Contraseña invalida, debe contener al menos una minuscula";
    }
    if (!elementId.val().match($regexUpper)) {
        return "Contraseña invalida, debe contener al menos una mayuscula";
    }
    if (!elementId.val().match($regexNumber)) {
        return "Contraseña invalida, debe contener al menos un numero";
    }
    if (!elementId.val().match($regexSpecial)) {
        return "Contraseña invalida, debe contener al menos un caracter especial";
    }
    console.log("Password valido");
    return "OK";

}
function validaUsername(elementId) {
    var $regex = /[a-zA-Z0-9]+/;
    if (!elementId.val().match($regex)) {
        $("#mensaje").html("Usuario invalido, solo puede contener letras y numeros");
        return false;
    }
    console.log("Usuario valido");
    return true;
}
function generatePassword() {
    var minLen = 8;
    var minus = "abcdefghijklmnopqrstuwxyz";
    var mayus = "ABCDEFGHIJKLMNOPQRSTUWXYZ";
    var number = "0123456789";
    var special = "$&*@#?<>_+-";
    var chars = "" + minus + mayus + number + special;
    var randomstring = "";

    for (var i = 0; i < minLen; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
    }
    randomstring = validateString(randomstring, minus);
    randomstring = validateString(randomstring, mayus);
    randomstring = validateString(randomstring, number);
    randomstring = validateString(randomstring, special);

    return randomstring;
}

function validateString(string, pattern) {
    //console.log(string);
    var len = pattern.length;
    for (var i = 0; i < len; i++) {
        var rchar = pattern.substring(i, i + 1);
        if (string.indexOf(rchar) > -1) {
            return string;
        }
    }
    var rnum = Math.floor(Math.random() * pattern.length);
    string += pattern.substring(rnum, rnum + 1);
    return string;
}