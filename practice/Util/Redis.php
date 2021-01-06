<?php

namespace Practice\Util;

use Predis\Client;

abstract class Redis
{
    public static function client()
    {
        if (!self::$client instanceof Client) {
            self::$client = new Client(['scheme' => Conf::scheme, 'host' => Conf::host, 'port' => Conf::port]);
        }
        return self::$client;
    }

    private static $client;
}