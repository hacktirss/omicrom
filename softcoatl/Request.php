<?php
/**
 * Request
 * XXXXXXXXXX®
 * © 2019, Softcoatl
 * http://www.softcoatl.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since ago 2019
 */

namespace com\softcoatl\utils;

class Request {

    /* @var $request  QueryParameters */
    private $request;

    public static function instance() {
        return new Request();
    }

    private function __construct() {
        $this->request = HTTPUtils::getRequest();
    }

    public function equals($key, $value) {
        return $this->request->getAttribute($key) === $value;
    }

    public function get($key, $default = "") {
        return $this->request->getAttribute($key, $default);
    }

    public function has($key) {
        return $this->request->hasAttribute($key);
    }
    
    public function present($key) {
        return $this->request->presentAttribute($key);
    }
    
    /**
     * @return array
     */
    public function getAttributes() {
        return $this->request->getAttributes();
    }
}
