<?php
ini_set('display_errors', 1);
include_once "vendor/autoload.php";

$client = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$user = new \struct\User(1, "zhangsan");
$auth = new Auth($client);
$token = md5('zhangsan'.time());
$auth->updateToken($user, $token);

if($auth->checkToken($token)) {
    print_r("user {$user->getName()} login !");
} else {
    print_r("user {$user->getName()} not login !");
}

$rec = new Recently();
$rec->handler($client);
