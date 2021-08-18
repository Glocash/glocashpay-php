<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \glocash\Payment();
try{
    $gcid = $_GET['gcid'];
    $amount = $_GET['amount'];
    $result =  $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey'])->refunded($gcid,$amount);
    //接下来操做退款业务逻辑
    //更新为refunding
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
