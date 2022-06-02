<h1 align="center"> GLOCASH PAY </h1>
<p align="center"> glocash payment SDK for PHP</p>
<h3 align="center"> <a target="_blank" href="https://docs.glocash.com">文档地址</a> </h3>

## 安装
```php
$ composer update
```

## 配置
配置信息 以及实例化
```php
$config = [
    'debug'=>true,//调试模式
    'sandbox'=>false,//sandbox模式
    'mchEmail'=>'商户邮箱或者商户编号',
    'apiKey'=>'商户秘钥',
];
$payment = new \glocash\Payment();
```

## 发起交易
```php
try{
    $data['REQ_INVOICE']  = 'test123'.rand(1000,9999); //订单号
    $data['REQ_APPID']  = 380; //应用ID
    $data['BIL_GOODSNAME']   = 'goods1Name×1;goods2Name×3'; //TODO 商户名称 请如实填写 否则银行结算会盘查
    $data['CUS_EMAIL']    = 'rongjiang.chen@witsion.com'; //客户邮箱
    $data['BIL_PRICE']    = '9.9'; //价格
    $data['BIL_CURRENCY'] = 'USD'; //币种
    $data['BIL_CC3DS'] = 1; //币种
    $data['URL_NOTIFY']   = 'http://www.crjblog.cn/notify.php';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_SUCCESS']   = 'http://www.crjblog.cn/return.php?status=success';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_FAILED']   = 'http://www.crjblog.cn/return.php?status=error';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $result =  $payment->setChannel('C01')->create($data);
    echo "<pre>"; print_r($result); echo "</pre>";
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```

## 发起退款
```php
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
```

## 交易查询
```php
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
```

## 异步通知
```php
try{
    //所有的状态更新都可以在这里操做
    $params =  $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey'])->notify();
    //业务逻辑查找当前订单交易情况
    if($params['PGW_NOTIFYTYPE'] == 'transaction'){
        //判断是否是unpaid 状态
        //更新状态操做

    }else if($params['PGW_NOTIFYTYPE'] == 'refunded'){
        //判断是否是paid refunding
        //处理退款信息
    }
    $payment->log('支付成功');
    echo 'success';
    //接下来操做退款业务逻辑
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```

## 直连模式
```php
$payment = new \glocash\Payment();
$payment = $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey']);
try{
    $data['REQ_INVOICE'] = 'test123'.rand(1000,9999); //订单号
    $data['REQ_APPID'] = 380; //应用ID
    $data['BIL_GOODSNAME'] = 'goods1Name×1;goods2Name×3'; //TODO 商户名称 请如实填写 否则银行结算会盘查
    $data['CUS_EMAIL'] = 'rongjiang.chen@witsion.com'; //客户邮箱
    $data['BIL_PRICE'] = '9.9'; //价格
    $data['BIL_CURRENCY'] = 'USD'; //币种
    $data['BIL_CC3DS'] = 1; //是否开启3ds 1 开启 0 不开启
    $data['BIL_IPADDR'] = '58.247.45.36'; //客户ip地址
    $data['BIL_GOODS_URL'] = 'https://www.merchant.com/goods/30'; //商品url
    $data['URL_NOTIFY'] = 'http://www.crjblog.cn/notify.php'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_SUCCESS'] = 'http://www.crjblog.cn/return.php?status=success'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_FAILED'] = 'http://www.crjblog.cn/return.php?status=error'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['BIL_CCNUMBER'] = '2223000000000007';
    $data['BIL_CCHOLDER'] = 'john smith';
    $data['BIL_CCEXPM'] = '09';
    $data['BIL_CCEXPY'] = '2024';
    $data['BIL_CCCVV2'] = '365';
    $result =  $payment->setChannel('C01')->create($data, 2);
    echo "<pre>"; print_r($result); echo "</pre>";
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```

## 内嵌模式
```php
$payment = new \glocash\Payment();
$payment = $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey']);
try{
    $data['REQ_INVOICE'] = 'test123'.rand(1000,9999); //订单号
    $data['REQ_APPID'] = 380; //应用ID
    $data['REQ_TYPE'] = 'website'; //请求类型
    $data['BIL_GOODSNAME'] = 'goods1Name×1;goods2Name×3'; //TODO 商户名称 请如实填写 否则银行结算会盘查
    $data['CUS_EMAIL'] = 'rongjiang.chen@witsion.com'; //客户邮箱
    $data['BIL_PRICE'] = '9.9'; //价格
    $data['BIL_CURRENCY'] = 'USD'; //币种
    $data['BIL_CC3DS'] = 1; //是否开启3ds 1 开启 0 不开启
    $data['BIL_IPADDR'] = '58.247.45.36'; //客户ip地址
    $data['URL_NOTIFY'] = 'http://www.crjblog.cn/notify.php';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_SUCCESS'] = 'http://www.crjblog.cn/return.php?status=success';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_FAILED'] = 'http://www.crjblog.cn/return.php?status=error';//异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['BIL_TEMP_TOKEN'] = '9fa3001597706e2c2a63cb55442b3e257b8e8d9fec629f43822f67d87d5af872'; //信用卡对应的临时TOKEN
    $result =  $payment->setChannel('C01')->create($data,3);
    echo "<pre>"; print_r($result); echo "</pre>";
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```

## 收银台模式
```php
$payment = new \glocash\Payment();
$payment = $payment->setMchEmail($config['mchEmail'])->setApiKey($config['apiKey']);
try{
    $data['REQ_INVOICE'] = 'test123'.rand(1000,9999); //订单号
    $data['REQ_APPID'] = 380; //应用ID
    $data['BIL_GOODSNAME'] = 'goods1Name×1;goods2Name×3'; //TODO 商户名称 请如实填写 否则银行结算会盘查
    $data['CUS_EMAIL'] = 'rongjiang.chen@witsion.com'; //客户邮箱
    $data['BIL_PRICE'] = '9.9'; //价格
    $data['BIL_CURRENCY'] = 'USD'; //币种
    $data['BIL_CC3DS'] = 1; //是否开启3ds 1 开启 0 不开启
    $data['BIL_IPADDR'] = '58.247.45.36'; //客户ip地址
    $data['URL_NOTIFY'] = 'http://www.crjblog.cn/notify.php'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_SUCCESS'] = 'http://www.crjblog.cn/return.php?status=success'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $data['URL_FAILED'] = 'http://www.crjblog.cn/return.php?status=error'; //异步通知地址 必须在白名单中 也可以在商户后台指定
    $result =  $payment->setChannel('C01')->create($data,4);
    echo "<pre>"; print_r($result); echo "</pre>";
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```



