<?php

namespace com\softcoatl\utils;

/**
 * Description of Messages
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class Messages {
    /* Operacion en general */

    const RESPONSE_TURN_CLOSE = "Corte cerrado y cuadrado exitosamente";
    const RESPONSE_ERROR = "Ocurrio un error en el proceso, favor de notificar a soporte!";
    const REGISTER_DUPLICATE = "Ocurrio un error en el proceso, el ? ya existe!";
    const MESSAGE_RINGING = "Iniciando proceso de timbrado!";
    const MESSAGE_RINGING_SUCCESS = "Se ha realizado el timbrado correctamente!";
    const MESSAGE_CLOSE = "El documento se ha cerrado correctamente!";
    const MESSAGE_DATE_INCORRECT = "La fecha de programacion (?) es invalida!";

    /* Operaciones CRUD */
    const RESPONSE_VALID_CREATE = "Registro creado con EXITO!";
    const RESPONSE_VALID_UPDATE = "Registro actualizado con EXITO!";
    const RESPONSE_VALID_DELETE = "Registro borrado con EXITO!";
    const RESPONSE_VALID_CANCEL = "Registro cancelado con EXITO!";
    const OP_ADD = "Agregar";
    const OP_UPDATE = "Actualizar";
    const OP_DELETE = "Si";
    const OP_FREE = "Liberar";
    const OP_SELECT = "Seleccionar";
    const OP_CANCEL = "Cancelar";
    const OP_SEND_EMAIL = "Enviar correo";
    const OP_CLOSE = "Cerrar";
    const OP_SEEK = "Buscar";
    const OP_SAVE = "Guardar";
    const OP_NO_OPERATION_VALID = "Enviar";
    const METHOD_POST = "0";
    const METHOD_GET = "1";
    const METHOD_GET_ALL = "-1";
    const METHOD_PUT = "2";
    const METHOD_DELETE = "3";

    /* Operaciones con usuarios */
    const RESPONSE_VALID_CHANGE_PWD = "Se cambio la contraseña con EXITO!";
    const RESPONSE_CREATE_USER_CUSTOMER = "Se ha creado el acceso al cliente, Usuario: ?, Contraseña: ?";
    const RESPONSE_PASSWORD_INCORRECT = "La clave ingresada es invalida, intente nuevamente!";
    const RESPONSE_USER_DATA_INVALID = "Los datos proporcionados son <font size='+1'>incorrectos</font>";
    const RESPONSE_USER_MAX_INTENTS = "Ha superado el máximo de intentos permitidos<br>";
    const RESPONSE_USER_ALIVE = "El usuario \"?\" tiene una sesión activa.<br/>Si cerró el navegador, deberá esperar un lapso de 10 minutos para ingresar nuevamente.";
    const RESPONSE_USER_LIVE = "Error: Otro usuario esta trabajando en este apartado.";

    /* Valor por default */
    const MESSAGE_DEFAULT = "Proceso realizado con EXITO!";
    const MESSAGE_NO_OPERATION = "No se realizo ningun movimiento";
    const MESSAGE_NO_PASSWORD_VALID = "La contraseña ingresada es incorrecta";

    /* Operaciones pagos */
    const MESSAGES_PAGOS_CLOSE = "El recibo se ha cerrado correctamente!";
    const MESSAGES_PAGOS_PAY_FREE = "El saldo se ha liberado correctamente!";

    /* Operaciones con facturas */
    const MESSAGES_FACTURAS_EXISTS = "Ticket asociado a otras facturas!";
    const MESSAGES_FACTURAS_LOAD_OK = "Se han cargado los tickets correctamente.";

    /* Icons from sweetAlert2*/
    const SUCCESSICON = "success";
    const ERRORICON = "error";
    const WARNINGICON = "warning";
    const INFOICON = "info";
    const QUESTIONICON = "question";

}
