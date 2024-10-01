<?php

/**
 * HTTPUtils
 * XXXXXXXXXX®
 * © 2019, Softcoatl
 * http://www.softcoatl.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since may 2019
 */

namespace com\softcoatl\utils;

class HTTPUtils {

    public static function sessionInvalidate() {
        session_start();
        $_SESSION = array();
        $sessionName = session_name();
        if (HTTPUtils::cookieSetted($sessionName)) {
            $params = session_get_cookie_params();
            setcookie($sessionName, '', 1, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        }
        session_destroy();
    }

    public static function sessionCreate() {
        session_start();
        if (!HTTPUtils::sessionSetted("CREATED")) {
            session_regenerate_id();
            $_SESSION["CREATED"] = time();
        }
    }

    public static function sessionSetted($key) {
        return isset($_SESSION[$key]);
    }

    public static function getSessionValue($key) {
        return $_SESSION[$key];
    }
    
    public static function getSessionBiValue($nameSession, $key) {
        return $_SESSION[$nameSession][$key];
    }

    public static function getSessionObject($key) {
        return unserialize($_SESSION[$key]);
    }

    public static function setSessionValue($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function setSessionBiValue($nameSession, $key, $value) {
        $_SESSION[$nameSession][$key] = $value;
    }

    public static function setSessionObject($key, $object) {
        $_SESSION[$key] = serialize($object);
    }

    public static function cookieSetted($key) {
        return HTTPUtils::getCookies()->hasAttribute($key);
    }

    public static function getCookieValue($key) {
        return HTTPUtils::getCookies()->getAttribute($key);
    }

    public static function getCookieObject($key) {
        $serialized = HTTPUtils::getCookies()->getAttribute($key);
        return unserialize($serialized);
    }

    public static function setCookieValue($key, $value) {
        setcookie($key, $value);
    }

    public static function setCookieObject($key, $object) {
        setcookie($key, serialize($object));
    }

    /**
     * 
     * @return QueryParameters
     */
    public static function getCookies() {
        return HTTPUtils::getMethod(INPUT_COOKIE);
    }

    /**
     * 
     * @return QueryParameters
     */
    public static function getEnvironment() {
        return HTTPUtils::getMethod(INPUT_SERVER);
    }

    /**
     * getRequest Returns request values
     * @return QueryParameters
     */
    public static function getRequest() {
        $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        return HTTPUtils::getMethod($method === "GET" ? INPUT_GET : INPUT_POST);
    }

    public static function getMethod($method = INPUT_GET) {
        $array = filter_input_array($method);
        return new QueryParameters($array);
    }

    public static function self() {
        return HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
    }

    public static function getContextPath() {
        $docRoot = HTTPUtils::getEnvironment()->getAttribute("DOCUMENT_ROOT");
        $softcoatl = dirname(__FILE__);
        $path = str_replace($docRoot, "", $softcoatl);
        return $path;
    }

}
