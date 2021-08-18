<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \glocash\Payment();
try{
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    //付款结束的结果页面 或者是3ds 返回的页面
    $params =  $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey'])->notify();
    //业务逻辑查找当前订单交易情况
    if($params['PGW_NOTIFYTYPE'] == 'transaction'){
        //判断是否是unpaid 状态
        //更新状态操做
    }else if($params['PGW_NOTIFYTYPE'] == 'refunded'){
        //判断是否是paid refunding
        //处理退款信息
    }
    echo "<pre>";
    print_r($params);
    echo "</pre>";
    echo 'success';
    //接下来操做退款业务逻辑
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
