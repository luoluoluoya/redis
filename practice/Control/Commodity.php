<?php

namespace Practice\Control;

use Practice\Struct\User;
use Practice\Util\Redis;

/*
 * 商品信息：
 *  commodity:{commodityId}(hash): [ {id:1}, {name:xx}, {price: xx},  ]
 * 用户商品浏览记录：
 *  user_viewed:{userId}(zset): [ [viewTime, commodityId], ]
 * 购物车：
 *  cart:{userId}(hash): [ [nums, commodityId], ]
 * 商品浏览量
 *  commodity_views(zset): [ [view_nums, commodityId],  ]
 */

class Commodity
{
    /**用户浏览的商品**/
    public function view(User $user, $item)
    {
        Redis::client()->zincrby($this->goodsViewIdent(), 1, $item);
        return Redis::client()->zadd($this->userViewIdent($user), [$item => time()]);
    }

    public function clearViewed(User $user)
    {
        return Redis::client()->del($this->userViewIdent($user));
    }

    public function userViewed(User $user)
    {
        return Redis::client()->zrange($this->userViewIdent($user), 0, -1);
    }

    public function views()
    {
        $views = [];
        foreach (Redis::client()->zrange($this->goodsViewIdent(), 0, -1) as $goods) {
            $views[$goods] = $this->goodsViews($goods);
        }
        return $views;
    }

    public function goodsViews($item)
    {
        return Redis::client()->zscore($this->goodsViewIdent(), $item);
    }

    /**购物车**/
    public function addToCart(User $user, $item, $count)
    {
        if ($count > 0) {
            return Redis::client()->hset($this->cartIdent($user), $item, $count);
        } else {
            return Redis::client()->hdel($this->cartIdent($user), $item);
        }
    }

    public function clearCart(User $user)
    {
        return Redis::client()->del($this->cartIdent($user));
    }

    public function cartList(User $user)
    {
        return Redis::client()->hgetall($this->cartIdent($user));
    }

    private function userViewIdent(User $user)
    {
        return 'user_viewed:' . $user->getId();
    }

    private function cartIdent(User $user)
    {
        return 'cart:' . $user->getId();
    }

    private function goodsViewIdent()
    {
        return 'commodity_views';
    }
}
