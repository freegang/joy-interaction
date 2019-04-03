<?php
/**
 * Created by PhpStorm.
 * User: chengang
 * Date: 2019-04-02
 * Time: 16:20
 */

namespace chengang\joyInteraction;


use yii\rest\Controller;
use yii\web\UnprocessableEntityHttpException;
use yii\filters\Cors;
use \Yii;
use yii\helpers\ArrayHelper;
use yii\web\UnauthorizedHttpException;

class BaseController extends Controller
{
    public $signMethod = '\chengang\joyInteraction\SignMethod'; //签名实现方法 可重写
    public $httpHeader = '\chengang\joyInteraction\HttpHeaderModel'; //请求头部对象 可重写

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['cors'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => ['x-pagination-total-count', 'x-pagination-page-count', 'x-pagination-current-page', 'x-pagination-per-page'],
            ]
        ];
        return $behaviors;
    }

    public function actionOptions()
    {
        return true;
    }


    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            //头部类验证
            if (!is_subclass_of($this->httpHeader, HttpHeader::className())) {
                $this->httpHeader = Yii::createObject($this->httpHeader);
                if (!is_subclass_of($this->httpHeader, HttpHeader::className())) {
                    throw new InvalidConfigException(get_class($this->httpHeader) . ' must extends chengang\joyInteraction\HttpHeader');
                }
            }
            $header = new $this->httpHeader();
            foreach (Yii::$app->request->headers->toArray() as $key => $value) {
                if (array_key_exists($key, $header->attributes)) {
                    $header->$key = $value[0];
                };
            }

            if (!$this->signMethod instanceof AuthMethod) {
                $this->signMethod = Yii::createObject($this->signMethod);
                if (!$this->signMethod instanceof AuthMethod) {
                    throw new InvalidConfigException(get_class($this->signMethod) . ' must implement chengang\joyInteraction\AuthMethod');
                }
            }
            $getParams = Yii::$app->request->getQueryParams(); //get参数
            $postParams = Yii::$app->request->getBodyParams(); //body参数
            $data = ArrayHelper::merge($getParams, $postParams);
            $sign = $this->signMethod::createSign($header->attributes, $data);
            $header->scenario = 'validate';
            if (!$header->validate()) {
                throw new UnprocessableEntityHttpException(ArrayHelper::getValue(array_values($header->firstErrors), 0));
            }

            if ($sign != $header->sign) {
                throw new UnauthorizedHttpException('签名错误');
            }
            return true;

        }

        return false;
    }


}