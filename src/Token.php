<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;


class Token
{
    protected static $token = null;

    public static function get()
    {
        return self::$token;
    }

    public static function set($token)
    {
        self::$token = $token;
    }
}