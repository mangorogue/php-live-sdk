<?php

/**
 * 客户端工具类
 * @filename  Client.php
 * @author    Orange
 * @date      2018-9-26 16:17:47
 */

namespace Sdk;

use Exception;
use stdClass;

class ClientUtil
{
    /**
     * 生成随机字符串
     *
     * @param int $len 需要的字符串长度
     *
     * @return string  $ret
     * @throws Exception
     */
    private static function genRandomStr(int $len)
    {
        $base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ret = '';
        $stringLen = strlen($base);
        for ($i = 0; $i < $len; $i++) {
            $ret .= $base[random_int(0, $stringLen - 1)];
        }
        return $ret;
    }

    /**
     * 生成签名
     *
     * @param string   $signKey        签名密钥
     * @param array    $param          参与签名的接口参数
     * @param array    $signHeader     参与签名的头部参数
     * @param array    $specialArray   跳过空过滤的数组，键为参数的键，值为此键的类型，例如“'isDeleted' => 'int'”
     *
     * @return string  $ret
     */
    private static function genSign(string $signKey,array $param, array $signHeader, array $specialArray = [])
    {
        $filterParam = array_filter($param, function($v, $k) {
            return (bool)$v && !in_array($k, ['file']);
        }, ARRAY_FILTER_USE_BOTH);
        $data = array_merge($filterParam, $signHeader);
        //  对有些参数要做特殊处理
        if ($specialArray) {
            foreach ($specialArray as $key => $special) {
                if ($data[$key]) { // 数据存在的键，不需要重置为空
                    continue;
                }
                switch ($special){
                    case 'string':
                        $data[$key] = '';
                        break;
                    case 'int':
                        $data[$key] = 0;
                        break;
                    case 'float':
                        $data[$key] = 0.0;
                        break;
                    case 'double':
                        $data[$key] = 0.00;
                        break;
                    case 'array':
                        $data[$key] = [];
                        break;
                    case 'object':
                        $data[$key] = new stdClass();
                        break;
                }
            }
        }
        ksort($data);
        $string = '';
        foreach ($data as $k =>$v) {
            if (is_array($v)) {
                $v = self::array2string($v);
            }
            if ($string) {
                $string .= '&' . $k . '=' . $v;
            } else {
                $string = $k . '=' . $v;
            }
        }
        $sign = md5($signKey . '_' . strtoupper($string));

        return $sign;
    }

    //  对签名参数里的数组格式化，方便和JAVA端的签名统一
    private static function array2string(array $param)
    {
        $string = '{';
        foreach ($param as $key => $value) {
            if (is_array($value)) {
                $value = self::array2string($value);
            }
            $string .= $key . '=' . $value;
        }
        $string .= '}';
        return $string;
    }


    /**
     * 调用接口
     *
     * @param Client    $client       客户端类
     * @param string    $url          接口链接
     * @param array     $param        接口参数
     * @param int       $perPage      单页数据数量（选填）
     * @param int       $pageNumber   页码（选填）
     * @param array     $specialArray   跳过签名空过滤的参数数组
     *
     * @return array|int  $data
     */
    public static function request(Client $client, string $url, array $param , int $perPage = 0, int $pageNumber = 0, array $specialArray = [])
    {
        $result = [
            'code' => 100009,
            'msg' => '网络繁忙',
            'data' => []
        ];
        $timeStampStr = time();
        try {
            $randomStr = self::genRandomStr(16);
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
            return $result;
        }
        $channel = $client->getAppId();
        $header = [
            'content-type: application/json',
            'deviceId: php',
            'appId: ' . ($channel ? $channel : 1),
            'timeStampStr: ' . $timeStampStr,
            'randomStr: ' . $randomStr,
        ];

        $signHeader = [
            'deviceId' => 'php',
            'timeStampStr' => $timeStampStr,
            'randomStr' => $randomStr
        ];

        $sign = self::genSign($client->getSignKey(), $param, $signHeader, $specialArray);

        array_push($header , 'sign: ' . $sign);

        if ($perPage && $pageNumber) {
            $param['pageSize'] = $perPage;
            $param['pageNumber'] = $pageNumber;
        }

        if (!$param) {
            $param = new stdClass();    // 如果参数为空，转为对象
        }

        $response = self::curlPost($client->getDomain() . $url, $param, $header, true);

        if (!empty($response['code'])) {
            $result['code'] = $response['code'];
            $result['msg'] = !empty($response['msg']) ? $response['msg'] : '请求超时';
            if ($response['code'] == Constant::SUCCESS_CODE) {
                if ($response['data']) {
                    if (empty($response['data']['key']) || empty($response['data']['data'])) {
                        $result = [
                            'code' => 100009,
                            'msg' => '接口加密未开启',
                            'data' => []
                        ];
                        return $result;
                    }
                    $result['data'] = self::decrypt($response['data']['key'], $response['data']['data'], $client->getPrivateKey());
                }
            }
        }

        return $result;
    }

    public static function curlPost(string $url, $param, array $header = [], $isJson = false)
    {
        $ch = curl_init();
        if ($isJson) {
            $post_param = json_encode($param);
        } else {
            $post_param = $param;
        }
        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 120, //超时时间
            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POST => 1, //POST
            CURLOPT_POSTFIELDS => $post_param, //post数据
            CURLOPT_ENCODING=>'gzip,deflate'
        ];
        if (strpos($url,'https') === false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 关闭对认证证书来源的检查
        }
        curl_setopt_array($ch, $curl_options);
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        if(json_last_error() != JSON_ERROR_NONE){
            $data = $res;
        }
        curl_close($ch);
        return $data;
    }

    public static function curlGet(string $url)
    {
        $header_options = [
            'content-type: application/json',
            'deviceType: php'
        ];
        //初始化
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_options);
        curl_setopt($ch, CURLOPT_URL,$url); // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);

        return $output;
    }

    private static function decrypt($key, $data, $privateKey)
    {
        $desKey = EncryptUtil::rsaDecrypt($key, $privateKey);
        $content = EncryptUtil::desDecrypt($data, $desKey);
        return json_decode($content, true);
    }

    public static function create_uuid()
    {
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $uuid;
    }

    public static function getToken(Client $client, string $userId)
    {
        //1,生成一个uuid
        $uuid = self::create_uuid();

        //2,把用户id,时间戳,appId,混淆字符串中间用“.”连接起来,生成待加密明文plainText
        $plainText = $userId . '.' . time() . '.' . $client->getAppId() . '.' . $client->getSignKey();

        //3,用第一步生成的uuid作为加密key,对第二步生成的plainText,进行DES加密得到密文cipherText1
        $cipherText1 = EncryptUtil::desEncrypt($plainText, $uuid);

        //4,对第一步生成的uuid进行，RSA加密得到密文cipherText2
        $cipherText2 = EncryptUtil::rsaEncrypt($uuid, $client->getPublicKey());

        //5,将第三步和第四步生成的密文用“.”连接起来得到最终token
        $tokenStr = $cipherText1 . '.' . $cipherText2;

        return $tokenStr;
    }


    public static function encryptResponseBody(Client $client, int $code, string $message, array $data)
    {
        //生成uuid 作为des加密key
        $uuid = self::create_uuid();

        //格式化响应
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
            //对称加密对响应参数加密
        $cipherText1 = EncryptUtil::desEncrypt(json_encode($response), $uuid);
            //对desKey加密
        $cipherText2 = EncryptUtil::rsaEncrypt($uuid, $client->getPublicKey());
        return $cipherText1 . "." . $cipherText2;
    }

    /**
     * @param Client $client          客户端类
     * @param string $requestBody     请求body
     * @return array
     * @throws Exception
     */
    public static function extractRequestParams(Client $client, string $requestBody)
    {
        $request = json_decode($requestBody, true);
        $jsonStr = json_encode($request['payload']) . time() . $client->getSignKey();

        //校验签名
        if(!$request['signature'] == md5($jsonStr)){
            throw new Exception('请求参数：' . $requestBody . '，签名验证失败');
        }

        return $request['payload'];
    }


}
