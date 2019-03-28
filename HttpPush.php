<?php

namespace chengang\joyInteraction;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\UnprocessableEntityHttpException;

/**
 * This is just an example.
 */
class HttpPush extends Model
{
    public $source; //源
    public $timestamp; //调用时间
    public $sign; //签名字符串
    public $method; //请求方式


    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';


    public function rules()
    {
        return [
            [['source', 'method'], 'required'],
            [['source', 'sign', 'method'], 'string'],
            [['timestamp'], 'integer'],
            ['method', 'in', 'range' => [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE]]
        ];
    }


    //请求对象生成
    private function client($method)
    {
        $this->method = $method;
        $client = new Client();
        $this->signCreate(); //签名生成
        return $client->createRequest()
            ->setMethod($this->method)
            ->setHeaders($this->attributes);
    }

    //get 请求
    public function push($method, $url, $data = [])
    {
        $client = $this->client($method);
        $response = $client
            ->setUrl($url)
            ->setData($data)
            ->send();

        if ($response->isOk) {
            return $response->data;
        } else {
            throw new UnprocessableEntityHttpException(ArrayHelper::getValue($response->data, 'message'));
        }
    }


    // sign 签名生成
    private function signCreate()
    {
        $this->timestamp = time();
        if (!$this->validate()) {
            throw new UnprocessableEntityHttpException(ArrayHelper::getValue(array_values($this->firstErrors), 0));
        }
        $this->sign = md5($this->source . $this->timestamp);
    }
}
