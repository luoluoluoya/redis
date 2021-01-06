<?php

ini_set('display_errors', 1);
include_once "vendor/autoload.php";


$client = \Practice\Util\Redis::client();
$user = new \Practice\Struct\User(1, "zhangsan");
$auth = new \Practice\Control\Auth($client);
$token = md5('zhangsan'.time());
$auth->updateToken($user, $token);

if($auth->checkToken($token)) {
    print_r("user {$user->getName()} login !");
} else {
    print_r("user {$user->getName()} not login !");
}
