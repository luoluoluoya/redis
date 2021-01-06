<?php

namespace Practice\Util;

/**
 * 在请求中间件中设置缓存代理以响应请求
 *
 * @package Practice\Util
 */
class CachingProxy
{
    public function handlerRequest($request, callable $callable)
    {
        if (!Conf::using_cache)
            return $callable($request);
        $cacheKey = $this->cachingIdent($request);
        if (!Redis::client()->exists($cacheKey)) {
            $response = $callable($request);
            Redis::client()->setex($cacheKey, Conf::cache_exp_time, json_encode($response));
        }
        return Redis::client()->get($cacheKey);
    }

    private function cachingIdent($request)
    {
        return 'caching:' . hash('md5', $request);
    }
}
