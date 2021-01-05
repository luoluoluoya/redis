<?php

use Predis\Client;

class Recently
{
    public function handler(Client $client)
    {
        while (true) {
            // token过期
            $tokens = $client->zrangebyscore(self::recent_ident, 0, time() - self::expiration_of_cert);
            if (!empty($tokens)) {
                $client->hdel(Auth::certified_ident, $tokens);
            }
            $client->zremrangebyscore(self::recent_ident, 0, time() - self::expiration_of_cert);
            // 键溢出
            $overflow = $client->zcard(self::recent_ident) - self::limit;
            if ($overflow > 0) {
                $tokens = $client->zrange(self::recent_ident, 0, $overflow);
                if (!empty($tokens)) {
                    $client->hdel(Auth::certified_ident, $client->zrange(self::recent_ident, 0, $overflow));
                }
                $client->zremrangebyrank(self::recent_ident, 0, $overflow);
            }
            sleep(1);
        }
    }

    const limit = 0;
    const expiration_of_cert = 24 * 60 * 60;
    const recent_ident = 'recent';
}
