<?php
ob_start('fatal_error_handler');

include_once ("auth.php");
include_once ("authconfig.php");

use com\softcoatl\utils as utils;

$stillLogged = false;

if (utils\HTTPUtils::getSessionValue(Usuarios::SESSION_USERNAME) && utils\HTTPUtils::getSessionValue(Usuarios::SESSION_PASSWORD)) {
    //error_log(print_r($_SESSION, TRUE));
    $username = utils\HTTPUtils::getSessionValue(Usuarios::SESSION_USERNAME);
    $password = utils\HTTPUtils::getSessionValue(Usuarios::SESSION_PASSWORD);

    $check = new Auth();
    $check->setUsername($username);
    $check->setPassword($password);
    $stillLogged = $check->page_check();
}

if ($stillLogged !== Auth::RESPONSE_VALID) {
    ?>
    <script>
        window.close();
    </script>

    <?php
    exit; // Termina la ejecucion del programa.
}


function fatal_error_handler($buffer) {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING,E_RECOVERABLE_ERROR))) {
        $newBuffer = '<script type="text/javascript">
            window.location = "500.html";
        </script>';

        return $newBuffer;
    }
    return $buffer;
}