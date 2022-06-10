<?php
include_once "../vendor/autoload.php"; //按实际路径引入
include_once "./config.php";
$payment = new \glocash\Payment();
$payment = $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey']);
try{
    $data['REQ_INVOICE'] = 'test123'.rand(1000,9999); //订单号
    $data['REQ_APPID'] = 380; //应用ID
    $data['REQ_TYPE'] = 'website'; //请求类型
    $data['BIL_GOODSNAME'] = 'goods1Name×1;goods2Name×3'; //TODO 商户名称 请如实填写 否则银行结算会盘查
    $data['CUS_EMAIL'] = 'rongjiang.chen@witsion.com'; //客户邮箱
    $data['BIL_PRICE'] = '87'; //价格
    $data['BIL_CURRENCY'] = 'USD'; //币种
    $data['BIL_CC3DS'] = 1; //是否开启3ds 1 开启 0 不开启
    $data['BIL_IPADDR'] = '58.247.45.36'; //客户ip地址
    $data['URL_NOTIFY'] = 'http://www.crjblog.cn/notify.php';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_SUCCESS'] = 'http://www.crjblog.cn/return.php?status=success';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_FAILED'] = 'http://www.crjblog.cn/return.php?status=error';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['BIL_TEMP_TOKEN'] = '9fa3001597706e2c2a63cb55442b3e257b8e8d9fec629f43822f67d87d5af872'; //信用卡对应的临时TOKEN
    if (!empty($_REQUEST['BIL_TEMP_TOKEN'])) {
        $data['BIL_TEMP_TOKEN'] = $_REQUEST['BIL_TEMP_TOKEN']; //信用卡对应的token
    }
    if (!empty($_REQUEST['BIL_TOKEN'])) {
        $data['BIL_TOKEN'] = $_REQUEST['BIL_TOKEN']; //信用卡对应的token
    }
    $result =  $payment->setChannel('C01')->create($data,3);
    echo json_encode(['data' => $result]);
    die();
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
