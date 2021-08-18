<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \glocash\Payment();
try{
    $gcid = $_GET['gcid'];
    $list =  $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey'])->query($gcid);
    print_r($list);
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
