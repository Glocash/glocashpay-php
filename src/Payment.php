<?php

namespace glocash;

class Payment
{
    protected $debug = true;
    protected $sandbox = false;
    private $mchEmail = "";
    private $apiKey = "";
    private $channel = 'C01';
    const TYPE_CREATE = 1;
    const TYPE_QUERY = 2;
    const TYPE_REFUND = 3;
    const TYPE_NOTIFY = 4;
    const LIVE_URL = 'https://pay.glocashpayment.com';
    const SANDBOX_URL = 'https://sandbox.glocashpayment.com';
    const PAYMENT_URL = '/gateway/payment/index';
    const QUERY_URL = '/gateway/transaction/index';
    const REFUND_URL = '/gateway/transaction/refund';
    protected $base_url = "";


    /**
     * @param $base_url
     * @return $this
     */
    public function setBaseUrl($base_url): Payment
    {
        //预留设置url入口 防止地址变化
        $this->base_url = $base_url;
        return $this;
    }


    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return Payment
     */
    public function setDebug(bool $debug): Payment
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param string $mchEmail
     * @return Payment
     */
    public function setMchEmail(string $mchEmail): Payment
    {
        $this->mchEmail = $mchEmail;
        return $this;
    }

    /**
     * @param string $apiKey
     * @return Payment
     */
    public function setApiKey(string $apiKey): Payment
    {
        $this->apiKey = $apiKey;
        return $this;
    }


    /**
     * @param bool $sandbox
     * @return Payment
     */
    public function setSandbox(bool $sandbox): Payment
    {
        $this->sandbox = $sandbox;
        return $this;
    }

    /**
     * @param string $channel
     * @return Payment
     */
    public function setChannel(string $channel): Payment
    {
        $this->channel = $channel;
        return $this;
    }


    private function getURl($url): string
    {
        $baseUrl = $this->sandbox ? self::SANDBOX_URL : self::LIVE_URL;
        $baseUrl = empty($this->base_url) ? $baseUrl : $this->base_url;
        return $baseUrl . $url;
    }

    private function checkMerchantConfig()
    {
        if (empty($this->mchEmail) || empty($this->apiKey)) {
            throw new PaymentException(PaymentException::SIGN_NULL, PaymentException::CODE_UNAUTHORIZED);
        }
    }

    public function create($data)
    {
        $this->log('----------------create transaction--------------------------');
        $this->checkMerchantConfig();
        $url = $this->getURl(self::PAYMENT_URL);
        $data['REQ_SANDBOX'] = (int)$this->sandbox;
        $data['REQ_EMAIL'] = $this->mchEmail;
        $data['BIL_METHOD'] = $this->channel;
        $data['REQ_TIMES'] = time();
        if (!isset($data['CUS_EMAIL']) || empty($data['CUS_EMAIL'])) {
            throw new PaymentException(PaymentException::EMAIL_MUST, PaymentException::CODE_BAD_REQUEST);
        }
        if (!isset($data['BIL_GOODSNAME']) || empty($data['BIL_GOODSNAME'])) {
            throw new PaymentException(PaymentException::BIL_GOODSNAME_MUST, PaymentException::CODE_BAD_REQUEST);
        }
        if (!isset($data['BIL_PRICE']) || empty($data['BIL_PRICE'])) {
            throw new PaymentException(PaymentException::BIL_PRICE_MUST, PaymentException::CODE_BAD_REQUEST);
        }
        if (!isset($data['BIL_CURRENCY']) || empty($data['BIL_CURRENCY'])) {
            throw new PaymentException(PaymentException::BIL_CURRENCY_MUST, PaymentException::CODE_BAD_REQUEST);
        }
        $data['REQ_SIGN'] = $this->makeSign($data, self::TYPE_CREATE);
        $this->log('request url: ' . $url);
        $this->log('request data: ' . json_encode($data));
        $result = $this->curl_request($url, $data);
        $this->log('response data: ' . $result);
        $result = json_decode($result, true);
        if (isset($result['REQ_ERROR']) && empty(!$result['REQ_ERROR'])) {
            throw new PaymentException($result['REQ_ERROR'], PaymentException::CODE_REQUEST_FAILED);
        }
        return $result;
    }

    public function query(string $gcid)
    {
        $this->log('----------------query--------------------------');
        $data['REQ_EMAIL'] = $this->mchEmail;
        $data['TNS_GCID'] = $gcid;
        $data['REQ_TIMES'] = time();
        $data['REQ_SIGN'] = $this->makeSign($data, self::TYPE_QUERY);
        $url = $this->getURl(self::QUERY_URL);
        $result = $this->curl_request($url, $data);
        $this->log('response data: ' . $result);
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    public function refunded(string $tns_id, $amount)
    {
        $this->log('----------------refunded--------------------------');
        if (empty($tns_id)) {
            throw new PaymentException(PaymentException::GCID_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if ($amount <= 0) {
            throw new PaymentException(PaymentException::AMOUNT_GT0, PaymentException::CODE_BAD_REQUEST);
        }
        $data['REQ_EMAIL'] = $this->mchEmail;
        $data['PGW_PRICE'] = $amount;
        $data['TNS_GCID'] = $tns_id;
        $data['REQ_TIMES'] = time();
        $data['REQ_SIGN'] = $this->makeSign($data, self::TYPE_REFUND);
        $url = $this->getURl(self::REFUND_URL);
        $result = $this->curl_request($url, $data);
        $this->log($result);
        if (empty($result)) {
            return [];
        }
        $result = json_decode($result, true);
        if (isset($result['REQ_CODE']) && $result['REQ_CODE'] != 200) {
            throw new PaymentException($result['REQ_ERROR'], $result['REQ_CODE']);
        }
        return $result;
    }

    public function notify()
    {
        $this->log('----------------notify--------------------------');
        $params = $_POST;
        $this->verifySign($params);
        $this->log('notify: ' . json_encode($params));
        $status = $params['BIL_STATUS'] ?? '';
        $gcid = $params['TNS_GCID'] ?? '';
        if (empty($status) || empty($gcid)) {
            throw new PaymentException(PaymentException::PARAM_ERROR, PaymentException::CODE_SERVER_ERRORS);
        }
        return $params;
    }

    public function verifySign($params)
    {
        if (empty($params['REQ_SIGN'])) {
            throw new PaymentException(PaymentException::SIGN_ERROR, PaymentException::CODE_UNAUTHORIZED);
        }
        $sign = $this->makeSign($params, self::TYPE_NOTIFY);
        if ($sign != $params['REQ_SIGN']) {
            throw new PaymentException(PaymentException::SIGN_ERROR, PaymentException::CODE_UNAUTHORIZED);
        }
        return true;
    }

    protected function makeSign($data, $type)
    {
        $signString = "";
        switch ($type) {
            case self::TYPE_CREATE:
                $signString = $this->apiKey . $data['REQ_TIMES'] . $this->mchEmail . $data['REQ_INVOICE'] . $data['CUS_EMAIL'] . $this->channel . $data['BIL_PRICE'] . $data['BIL_CURRENCY'];
                break;
            case self::TYPE_QUERY:
                $signString = $this->apiKey . $data['REQ_TIMES'] . $this->mchEmail . $data['TNS_GCID'];
                break;
            case self::TYPE_REFUND:
                $signString = $this->apiKey . $data['REQ_TIMES'] . $this->mchEmail . $data['TNS_GCID'] . $data['PGW_PRICE'];
                break;
            case self::TYPE_NOTIFY:
                $signString = $this->apiKey . $data['REQ_TIMES'] . $data['REQ_EMAIL'] . $data['CUS_EMAIL'] . $data['TNS_GCID'] . $data['BIL_STATUS'] . $data['BIL_METHOD'] . $data['PGW_PRICE'] . $data['PGW_CURRENCY'];
                break;
        }
        $this->log('sign type：' . $type . '-sign string：' . $signString);
        return hash('sha256', $signString);
    }


    protected function curl_request($url, $data = null, $method = 'post', $https = true)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($https === true) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            if ($method === 'post') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $result = curl_exec($ch);
            if ($result === false) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $e) {
            throw new PaymentException($e->getMessage(), PaymentException::CODE_SERVER_ERRORS);
        }
    }

    public function log($data)
    {
        if (!self::isDebug()) {
            return true;
        }
        $data = is_array($data) ? var_export($data, true) : $data;
        file_put_contents('./glocash.log', $data . PHP_EOL, FILE_APPEND);
    }
}

class PaymentException extends \Exception
{

    /**
     * 正常返回
     */
    const CODE_SUCCESS = 200;

    /**
     * 参数缺失
     */
    const CODE_BAD_REQUEST = 400;

    /**
     * 验证失败
     */
    const CODE_UNAUTHORIZED = 401;

    /**
     * 请求业务类型错误
     */
    const CODE_REQUEST_FAILED = 402;

    /**
     * 接口限制
     */
    const CODE_FORBIDDEN = 403;

    /**
     * 资源不存在
     */
    const CODE_NOT_FOUND = 404;

    /**
     * 系统内部错误
     */
    const CODE_SERVER_ERRORS = 500;

    const SIGN_NULL = 'merchant email or apiKey is null';
    const EMAIL_MUST = 'email is  must';
    const BIL_GOODSNAME_MUST = 'bil_goodsname is must';
    const BIL_PRICE_MUST = 'bil_price is must';
    const BIL_CURRENCY_MUST = 'bil_currency is must';
    const GCID_MUST = 'gcid is  must';
    const AMOUNT_GT0 = 'amount must greater than 0';
    const SIGN_ERROR = 'sign is failed';
    const PARAM_ERROR = 'param is failed';
}
