chengang/joy-interaction
========================
这个扩展提供了乐换机各个系统内部调用方法的接口封装。

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist chengang/joy-interaction "*"
```

or add

```
"chengang/joy-interaction": "*"
```

to the require section of your `composer.json` file.


Usage
-----
使用方式：

一.组件方式

1.在配置文件中 components 加入配置项。如下：
```
'components'=>{
 'joyInteraction' => [ //组件名称
            'class' => \chengang\joyInteraction\HttpPush::class, 
            'source' => '1213', //需要配置目标系统分配的来源标识
        ]
}
```
2.在需要使用时，直接引用
```
$res = Yii::$app->joyInteraction->push(HttpPush::METHOD_GET, 'http://wms.cc/v1/boxes/1');
$res: 为请求结果
HttpPush::METHOD_GET 请求方式，请引用HttpPush提供的请求方式枚举
'http://wms.cc/v1/boxes/1' 请求目标地址
```
二.直接实例化方式

直接实例化
```
   $httpPush = new \chengang\joyInteraction\HttpPush(); //实例化
   $httpPush->source = '123'; //设置目标系统分配的来源标识
   $data = $httpPush->push(HttpPush::METHOD_GET,'http://www.baidu.com');
   HttpPush::METHOD_GET 请求方式，请引用HttpPush提供的请求方式枚举
   'http://wms.cc/v1/boxes/1' 请求目标地址
```