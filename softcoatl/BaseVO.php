<?php

namespace com\softcoatl\utils;

/**
 * Description of BaseVO
 *
 * @author rolando
 */
abstract class BaseVO {
    public function uempty($value, $default) {
        return Utils::uempty($value, $default);
    }
    public abstract static function parse($array);
}
