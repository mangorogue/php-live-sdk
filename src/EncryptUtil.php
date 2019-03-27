<?php
/**
 * Created by PhpStorm.
 * User: Orange
 * Date: 2018/3/29 0029
 * Time: 11:17
 */

namespace Sdk;


class EncryptUtil
{

    const IV = '01234567';

    private static function getPrivateKey($privateKey)
    {
        return openssl_get_privatekey($privateKey);
    }

    private static function getPrivateKeyByPath($path)
    {
        return openssl_get_privatekey(file_get_contents($path));
    }

    private static function getPublicKey($publicKey)
    {
        return openssl_get_publickey($publicKey);
    }

    private static function getPublicKeyByPath($path)
    {
        return openssl_get_publickey(file_get_contents($path));
    }

    public static function rsaEncrypt(string $data, $publicKey)
    {
        openssl_public_encrypt($data, $secret, self::getPublicKey($publicKey));
        return base64_encode($secret);
    }

    public static function rsaEncryptByPath(string $data, $publicPath)
    {
        openssl_public_encrypt($data, $secret, self::getPublicKeyByPath($publicPath));
        return base64_encode($secret);
    }

    public static function rsaDecrypt(string $secret, $privateKey)
    {
        openssl_private_decrypt(base64_decode($secret), $data, self::getPrivateKey($privateKey));
        return $data;
    }

    public static function rsaDecryptByPath(string $secret, $privatePath)
    {
        openssl_private_decrypt(base64_decode($secret), $data, self::getPrivateKeyByPath($privatePath));
        return $data;
    }

    public static function desEncrypt(string $data, $key)
    {
        return openssl_encrypt($data, 'DES-EDE3-CBC', $key, 0, self::IV);
    }

    public static function desDecrypt(string $data, $key)
    {
        return openssl_decrypt($data, 'DES-EDE3-CBC', $key, 0, self::IV);
    }
}