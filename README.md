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

通过接入GLOCASHPAYMENT支付网关系统（下称GC网关），使商户系统获得覆盖全球的多样化收款渠道。在直连模式中，不需要跳转到信用卡支付页，直接完成支付。可快速完成接入，并实现最佳的付款体验。

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

通过接入GLOCASHPAYMENT支付网关系统（下称GC网关），使商户系统获得覆盖全球的多样化收款渠道。在内嵌模式中，不需要跳转到信用卡支付页，可以直接嵌入网站页面中，让用户可以快速付款，从而提高付款率。

#### 前端代码 embed.html

```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>glocash-iframe-test</title>
  <meta name="viewport" content="user-scalable=no, width=device-width initial-scale=1.0， maximum-scale=1.0"/>
</head>
<body>
<form id='place_order' method="post">
  <!--这些参数都是商户自己的提交参数-->
  <div>
    <input type="hidden" name="CUS_EMAIL" value="32892123@qq.com"/>
    <input type="hidden" name="BIL_PRICE" value="16"/>
    <input type="hidden" name="BIL_CC3DS" value="0"/>
    <input type="hidden" name="BIL_CURRENCY" value="EUR"/>
    <input type="hidden" name="BIL_GOODSNAME" value="#gold#Runescape/OSRS Old School/ 10M Gol"/>
    <input type="hidden" name="CUS_COUNTRY" value="CN"/>
  </div>

  <!-- 指定表单要插入的位置 -->
  <div style="max-width:400px;margin :0 auto" id="testFrom"></div>

  <div style="max-width:400px;margin :0 auto;text-align:center">
    <input style="padding: 5px 10px;cursor: pointer;width:100px" id="sub_order" type="button" value="付款" />
  </div>
</form>
</body>
</html>
<!-- 引入 jquery.js和iframe.js初始化 即可生成对应的form -->
<script type="text/javascript" src="https://pay.glocashpayment.com/public/comm/js/jquery112.min.js"></script>
<script type="text/javascript" src="https://pay.glocashpayment.com/public/gateway/js/iframe.v0.1.js"></script>
<script type="text/javascript">
  $(function() {
    //初始化 设置应用id和对应要嵌入的位置
    glocashPay.init(
            {
              appId: 2, //商户ID 必填
              payElement: "testFrom",//需要放入的支付表单的位置
              isToken: true, // token支付 必须是true
              buyerId: "456733211", // 买家ID
              config:{
                "card_iframe":{"style":"border: none; width: 100%;height:300px;display:none"},
              } // 设置iframe样式
            }
    );

    // 付款
    $("#sub_order").click(function () {
      glocashPay.checkout(function ({data}) {
        if (data.error) {
          console.error("创建卡信息失败:" + data.error);
          return false;
        }
        var postData = $("#place_order").serializeArray();
        if (data.token) {
          postData.push({name: 'BIL_TEMP_TOKEN', value: data.token});
        }
        if (data.bilToken) {
          postData.push({name: 'BIL_TOKEN', value: data.bilToken});
        }
        submitData(postData)
      });
    });

    // 提交数据
    function submitData(postData) {
      // 如果当前页面有其他的支付信息也可以一并提交到后台
      $.ajax({
        url: "./embed.php", //对应着你们后台的url
        type: "POST",
        data: postData,
        dataType: 'json',
        success: function (result) {
          console.log("返回参数", result);
          if (result.data.URL_CC3DS && result.data.URL_CC3DS != "") {
            //跳转到3ds页面
            window.location.href = result.data.URL_CC3DS;
          } else {
            if (result.data.BIL_STATUS && result.data.BIL_STATUS == 'paid') {
              //支付成功
              alert('paid success');
            } else {
              //支付失败
              if (result.data.REQ_ERROR) {
                alert('paid error ' + result.data.REQ_ERROR);
              }
              if (result.data.PGW_MESSAGE) {
                alert('paid error ' + result.data.PGW_MESSAGE);
              }
            }
          }
          return true;
        }
      });
    }
  });
</script>

```

#### 后端代码 embed.php
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

通过接入GLOCASHPAYMENT支付网关系统（下称GC网关），使商户系统获得覆盖全球的多样化收款渠道。在收银台模式中，可以根据国家，展示对应的支付方式，从而提高付款率。

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



