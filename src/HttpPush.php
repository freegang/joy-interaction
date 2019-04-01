<?php

namespace chengang\joyInteraction;

use \Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\UnprocessableEntityHttpException;

/**
 * This is just an example.
 */
class HttpPush extends Model
{
    public $signMethod = '\chengang\joyInteraction\SignMethod'; //签名实现方法 可重新
    public $httpHeader = '\chengang\joyInteraction\HttpHeaderModel'; //请求头部对象 可重新
    public $method; //请求方式
    public $source; //请求源
    private $_header; //请求头部


    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';


    public function rules()
    {
        return [
            [['method', 'source'], 'required'],
            [['method', 'source'], 'string'],
            ['method', 'in', 'range' => [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE]]
        ];
    }


    //请求对象生成
    private function client($method)
    {
        $this->method = $method;
        if (!$this->validate()) {
            throw new UnprocessableEntityHttpException(ArrayHelper::getValue(array_values($this->firstErrors), 0));
        }
        $client = new Client();
        return $client->createRequest()
            ->setMethod($this->method);
    }

    /**
     * @param $method 请求方式
     * @param $url 请求地址
     * @param array $data 请求参数
     * @param array $header 头部参数
     * @return mixed
     * @throws UnprocessableEntityHttpException
     */
    public function push($method, $url, $data = [], $header = [])
    {
        try {
            $client = $this->client($method);
            if (isset($header['source']) && !$header['source']) {
                $header['source'] = $this->source;
            }
            $this->headerCreate($header, $data); //头部参数处理
            $response = $client
                ->setUrl($url)
                ->setHeaders($this->_header->attributes)
                ->setData($data)
                ->send();
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        if ($response->isOk) {
            return $response->data;
        } else {
            throw new UnprocessableEntityHttpException(ArrayHelper::getValue($response->data, 'message'));
        }
    }


    //头部参数处理
    private function headerCreate($headerParams, $data)
    {
        if (!is_subclass_of($this->httpHeader, HttpHeader::className())) {
            $this->httpHeader = Yii::createObject($this->httpHeader);
            if (!is_subclass_of($this->httpHeader, HttpHeader::className())) {
                throw new InvalidConfigException(get_class($this->httpHeader) . ' must extends chengang\joyInteraction\HttpHeader');
            }
        }
        $header = new $this->httpHeader();
        foreach ($header->attributes as $key => $value) {
            if (array_key_exists($key, $headerParams)) { //当请求存在头部参数时，替换值
                $header->$key = ArrayHelper::getValue($headerParams, $key);
            }
        }
        $header->source = $this->source;
        $header->sign = $this->signCreate($headerParams, $data);
        if (!$header->validate()) { //头部参数验证
            throw new UnprocessableEntityHttpException(ArrayHelper::getValue(array_values($header->firstErrors), 0));
        }
        $this->_header = $header;
    }

    // sign 签名生成
    private function signCreate($headerParams, $data)
    {

        if (!$this->signMethod instanceof AuthMethod) {
            $this->signMethod = Yii::createObject($this->signMethod);
            if (!$this->signMethod instanceof AuthMethod) {
                throw new InvalidConfigException(get_class($this->signMethod) . ' must implement chengang\joyInteraction\AuthMethod');
            }
        }
        return $this->signMethod::createSign($headerParams, $data);

    }
}
