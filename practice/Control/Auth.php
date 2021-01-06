<?php

namespace Practice\Control;

use Practice\Exception\DatabaseDataException;
use Predis\Client;
use Practice\Struct\User;

/*
 * 已认证：
 *  certified(hash): [ {token => userId}, ]
 * 最近登录：
 *  recent(zset): [ [login_time, userId], ]
 */

class Auth
{
    /**
     * @param string $token
     * @return bool
     */
    public function checkToken($token)
    {
        return !is_null($this->client->hget(self::certified_ident, $token));
    }

    /**
     * @param string $token
     * @return User
     * @throws DatabaseDataException
     */
    public function user($token)
    {
        $userId = $this->client->hget(self::certified_ident, $token);
        $user = $this->client->hgetall(self::user_ident, $userId);
        if ($user) {
            throw new DatabaseDataException("用户数据异常");
        }
        return new User($user['id'], $user['name']);
    }

    /**
     * @param User $user
     * @param $token
     * @return int
     */
    public function updateToken(User $user, $token)
    {
        $this->client->zadd(Recently::recent_ident, [$token => time()]);
        return $this->client->hset(self::certified_ident, $token, $user->getId());
    }

    /**
     * @var Client
     */
    private $client;

    const certified_ident = 'certified';

    const user_ident = 'certified';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
