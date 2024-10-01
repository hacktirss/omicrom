<?php

$file = "/home/omicrom/xml/" . $_REQUEST["Direccion"];
$tam = filesize($file);
header("Content-type: application/pdf");
header("Content-Length: $tam");

header("Content-Disposition: inline; filename='" . $file . "'");
readfile($file);
