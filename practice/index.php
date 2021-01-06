<?php

ini_set('display_errors', 1);
include_once "vendor/autoload.php";


$client = \Practice\Util\Redis::client();
$user = new \Practice\Struct\User(md5("zhangsan"), "zhangsan");
$auth = new \Practice\Control\Auth($client);
$token = md5('zhangsan' . time());
$auth->updateToken($user, $token);

if ($auth->checkToken($token)) {
    print_r("user {$user->getName()} login !<br/>");
} else {
    print_r("user {$user->getName()} not login !<br/>");
}

$commodity = new \Practice\Control\Commodity();
$commodity->view($user, 1);
$commodity->view($user, 1);
$commodity->view($user, 2);
$commodity->view($user, 3);

echo "User {$user->getName()} vied: <br/>";
var_dump($commodity->userViewed($user));

echo "clear {$user->getName()} vied: <br/>";
$commodity->clearViewed($user);
var_dump($commodity->userViewed($user));

echo "view {$user->getName()} cart: <br/>";
var_dump($commodity->cartList($user));
//echo "do commodity join<br/>";
//$commodity->addToCart($user, 1, 10);
//$commodity->addToCart($user, 2, 20);
//$commodity->addToCart($user, 3, 30);
//var_dump($commodity->cartList($user));

echo "commodity views:<br/>";
var_dump($commodity->views());

echo "test cache request:<br/>";
$proxy = new \Practice\Util\CachingProxy();
$response = $proxy->handlerRequest('cache/test1', function ($request) { return ['url' => $request, 'data' => ['testing'],]; });
var_dump(json_decode($response));

