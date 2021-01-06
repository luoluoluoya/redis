<?php

namespace Practice\Util;

/**
 * @package Practice\Util
 */
final class Conf
{
    // Redis
    const scheme = 'tcp';
    const host = '127.0.0.1';
    const port = 6379;

    // caching
    const using_cache = true;
    const cache_exp_time = 60*3;

    // userLimit
    const limit = 10;
    const expiration_of_cert = 24 * 60 * 60;
}