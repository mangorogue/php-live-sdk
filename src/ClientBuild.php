<?php

/**
 * 客户端构造类
 * @filename  Client.php
 * @author    Orange
 * @date      2018-9-26 16:17:47
 */

namespace Sdk;

use Exception;

class ClientBuild {

    private $client;

    private function __construct()
    {
        $this->client = new Client();
    }

    public static function createClient()
    {
        $clientBuild = new ClientBuild();
        return $clientBuild;
    }

    public function setAppId(string $appId)
    {
        $this->client->setAppId($appId);
        return $this;
    }


    public function setSignKey(string $signKey)
    {
        $this->client->setSignKey($signKey);
        return $this;
    }

    public function setDomain(string $domain)
    {
        $this->client->setDomain($domain);
        return $this;
    }

    public function setPublicKey(string $publicKey)
    {
        $this->client->setPublicKey($publicKey);
        return $this;
    }

    public function setPrivateKey(string $privateKey)
    {
        $this->client->setPrivateKey($privateKey);
        return $this;
    }

    /**
     * @return Client
     * @throws Exception
     */
    public function build()
    {
        $this->assertEmpty("appId", $this->client->getAppId());
        $this->assertEmpty("domain", $this->client->getDomain());
        $this->assertEmpty("signKey", $this->client->getSignKey());
        $this->assertEmpty("privateKey", $this->client->getPrivateKey());
        $this->assertEmpty("publicKey", $this->client->getPublicKey());
        return $this->client;
    }

    /**
     * @param string $fieldName
     * @param string $str
     * @throws Exception
     */
    private function assertEmpty(string $fieldName, string $str)
    {
        if(!$str){
            throw new Exception($fieldName . "配置项不能为空");
        }
    }
}
