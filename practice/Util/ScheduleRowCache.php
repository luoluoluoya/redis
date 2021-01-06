<?php


namespace Practice\Util;


use Practice\Control\CronAble;

/**
 * 对某个表的数据行进行缓存：
 * 调度器：
 *  schedule(zset): [ [time, rowId], ]
 * 缓存更新时间：
 *  delay(zset): [ [delay, rowId] ]
 * 缓存行：
 *  ive{row_id}(string): { 'xx':'xx', 'xx':'xx', }
 * @package Practice\Util
 */
class ScheduleRowCache implements CronAble
{
    public function cache($row, $delay)
    {
        Redis::client()->zadd(self::schedule_ident, [$row => time()]);
        Redis::client()->zadd(self::delay_ident, [$row => $delay]);
    }

    public function cancel($row)
    {
        Redis::client()->zrem(self::schedule_ident, $row);
    }

    public function handler()
    {
        $client = Redis::client();
        while (true) {
            $next = $client->zrange(self::schedule_ident, 0, 0, ['withscore' => true]);
            if (!$next || $next[0][1] > time()) {
                sleep(1);
            }
            $row = $next[0][0];
            $delay = $client->zscore(self::delay_ident, $row);
            if ($delay <= 0) {
                $client->zrem(self::delay_ident, $row);
                $client->zrem(self::schedule_ident, $row);
                $client->del($this->cacheKey($row));
                continue;
            }
            $data = [];    // todo 此处获取数据
            $client->setex($this->cacheKey($row), time() + $delay, json_encode($data));
            $client->zadd(self::schedule_ident, $row, time() + $delay);
        }
    }

    private function cacheKey($row)
    {
        return 'ive:' . $row;
    }

    const schedule_ident = 'schedule';
    const delay_ident = 'delay';
}