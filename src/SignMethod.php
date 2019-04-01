<?php
/**
 * Created by PhpStorm.
 * User: chengang
 * Date: 2019-03-29
 * Time: 11:07
 */

namespace chengang\joyInteraction;

//签名方法类
class SignMethod implements AuthMethod
{
    //签名算法实现
    public static function createSign($headerParams, $dataParams)
    {
        return 'sign';
    }
}