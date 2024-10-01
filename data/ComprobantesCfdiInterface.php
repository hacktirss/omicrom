<?php

interface ComprobantesCfdiInterface {

    public function comprobante();

    public function emisor();

    public function receptor();

    public function cfdiRelacionados();

    public function conceptos();

    public function impuestos();

    public function observaciones();
}
