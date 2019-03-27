<?php

/**
 * 常量类
 * @filename  Constant.php
 * @author    Orange
 * @date      2018-9-26 16:17:47
 */

namespace Sdk;


class Constant {

    /**
     *
     * pat 方实现
     *
     * 获取主播冻结状态
     */
    const ANCHOR_FROZEN_STATUS_URI =  "/api/external/getAnchorFrozenStatus";


    /**
     *
     * 渠道 方实现
     *
     * 查询主播最新可用收益余额
     */
    const QUERY_COIN_INCOME =  "/channel/live/queryCoinIncome";

    /**
     * 渠道 方实现
     *
     * 查询用户最新可用充值余额
     */
    const QUERY_COIN_BALANCE = "/channel/live/queryCoinBalance";

    /**
     * 渠道 方实现
     *
     * 礼物消费
     */
    const SEND_GIFT = "/channel/live/sendGift";

    /**
     * 渠道 方实现
     *
     * 发送短信验证码
     */
    const SEND_VERIFY_CODE ="/channel/live/sendVerifyCode";

    /**
     * 渠道 方实现
     *
     * 校验短信验证码
     */
    const VALIDATE_PHONE_AND_CODE ="/channel/live/validatePhoneAndCode";




    const ERROR_CODE = 100002;

    const SUCCESS_CODE = 100001;


    /**
     * info:1
     * error:2
     * noOutPut:3
     * 手动控制日志级别目的是为了解决和渠道方自己配置的日志冲突相冲突的问题
     *
     * 例如：渠道方配置的日志级别是info，但是不想输出jdk内的info日志打印
     *
     */
    const LOG_LEVEL = 3;

    /**
     * info:日志级别
     */
    const LOG_LEVEL_INFO = 1;

    /**
     * error：日志级别
     */
    const LOG_LEVEL_ERROR = 2;

    /**
     * 不输出任何日志
     */
    const LOG_LEVEL_NO_OUT_PUT = 3;

}