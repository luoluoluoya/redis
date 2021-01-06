<?php

namespace Practice\Control;

use Practice\Util\Conf;
use Practice\Util\Redis;
use Predis\Client;
use \Practice\Struct\User;


class Recently implements CronAble
{
    /**最近的信息**/
    public function handler()
    {
        $client = Redis::client();
        $commodity = new Commodity();
        while (true) {
            // token过期
            $tokens = $this->overdue($client);
            // 键上溢
            array_merge($tokens, $this->overflow($client));
            // 统计即将被移除记录的用户
            $users = array_reduce($tokens, function (array $carry, $token) use (&$client) {
                if ($userId = $client->hget(Auth::certified_ident, $token))
                    $carry[] = $userId;
                return $carry;
            }, []);
            // 清除token记录
            if (!empty($tokens)) {
                $client->hdel(Auth::certified_ident, $tokens);
            }
            // 清除商品浏览记录
            $this->clearViewed($commodity, $users);
            // 清除购物车
            $this->clearCart($commodity, $users);

            sleep(1);
        }
    }

    /**
     * @param Client $client
     * @return array
     */
    private function overflow(Client $client)
    {
        $overflow = $client->zcard(self::recent_ident) - Conf::limit;
        if ($overflow <= 0) {
            return [];
        }
        $tokens = $client->zrange(self::recent_ident, 0, $overflow);
        $client->zremrangebyrank(self::recent_ident, 0, $overflow);
        return $tokens;
    }

    /**
     * @param Client $client
     * @return array
     */
    private function overdue(Client $client)
    {
        $tokens = $client->zrangebyscore(self::recent_ident, 0, time() - Conf::expiration_of_cert);
        $client->zremrangebyscore(self::recent_ident, 0, time() - Conf::expiration_of_cert);
        return $tokens;
    }

    /**
     * @param Commodity $commodity
     * @param array $users
     */
    private function clearViewed(Commodity $commodity, array $users)
    {
        array_walk($users, function ($user) use ($commodity) {
            $commodity->clearViewed($user);
        });
    }

    /**
     * @param Commodity $commodity
     * @param array $users
     */
    private function clearCart(Commodity $commodity, array $users)
    {
        array_walk($users, function ($user) use ($commodity) {
            $commodity->clearCart($user);
        });
    }

    const recent_ident = 'recent';
}
