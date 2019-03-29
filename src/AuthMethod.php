<?php
/**
 * Created by PhpStorm.
 * User: chengang
 * Date: 2019-03-29
 * Time: 11:06
 */

namespace chengang\joyInteraction;


interface AuthMethod
{
    //需要传入签名参数 具体实现签名算法
    public static function createSign($params);

}