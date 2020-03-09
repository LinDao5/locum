<?php
/**
 * Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
 */

return function ($class) {
    static $map;
    if (!$map) {
        $map = include __DIR__ . '/autoload_classmap.php';
    }

    if (!isset($map[$class])) {
        return false;
    }

    return include $map[$class];
};
