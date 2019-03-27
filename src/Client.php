<?php

/**
 * 客户端类
 * @filename  Client.php
 * @author    Orange
 * @date      2018-9-26 16:17:47
 */

namespace Sdk;

use Exception;

class Client
{
    /**
     *客户端appId
     */
    private $appId;

    /**
     *签名用到的key
     */
    private $signKey;

    /**
     * 域名
     */
    private $domain;

    /**
     * 渠道方-->pat方 响应加密公钥
     */
    private $publicKey;

    /**
     * 渠道方-->pat方 响应解密私钥
     */
    private $privateKey;


    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getSignKey()
    {
        return $this->signKey;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId(string $appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param mixed $signKey
     */
    public function setSignKey(string $signKey)
    {
        $this->signKey = $signKey;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param mixed $publicKey
     */
    public function setPublicKey(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param mixed $privateKey
     */
    public function setPrivateKey(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }


    /**
     * 获取主播冻结状态
     * @param string $anchorId
     * @return int
     * @throws Exception
     */
    public function queryAnchorFrozenStatus(string $anchorId)
    {
        $param = [
            'memberId' => $anchorId,
            'appId' => $this->getAppId()
        ];
        $response = ClientUtil::request($this, Constant::ANCHOR_FROZEN_STATUS_URI, $param);
        if ($response['code'] != Constant::SUCCESS_CODE) {
            throw new Exception($response['msg']);
        }
        $isFrozen = $response['data']['isFrozen'];

        return $isFrozen;
    }


    /**
     * 获取pat请求参数,此方法已验证签名无需手动验证
     * @param string $requestBody
     * @return array
     * @throws Exception
     */
    public function extractRequestParams(string $requestBody)
    {
        return ClientUtil::extractRequestParams($this, $requestBody);
    }

    /**
     *
     * 构建响应字符串
     *
     * @param int $code  响应编码
     * @param string $message   响应描述信息
     * @param array $data      响应业务数据
     * @return string
     * @throws Exception
     */
    public function createResponseBody(int $code, string $message, array $data)
    {
        return ClientUtil::encryptResponseBody($this, $code, $message, $data);
    }

    /**
     *  根据用户id生成token
     * @param $userId
     * @return string
     */
    public function getToken(string $userId)
    {
        return ClientUtil::getToken($this, $userId);
    }

}
