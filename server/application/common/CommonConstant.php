<?php

namespace app\common;

/**
 * 一般/公用常量定义
 */
class CommonConstant
{
    // *********************************魔术文本数字定义*********************************
    // 数据库中表示真假的int值
    const db_true = 1;
    const db_false = 0;
    // 数据库中表示正常和禁用状态的int值
    const db_enabled = 1;
    const db_disabled = 0;
    // 数据库中表示性别的int值
    const db_sex_male = 1;
    const db_sex_female = 2;
    const db_sex_other = 0;
    // 客户端类型
    const client_type_android = 'android';
    const client_type_ios = 'ios';

    // 手机号正则
    const phone_reg_expression = "^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[57])[0-9]{8}$";


    /************10001-10099  系统相关错误；10101-10199，接口相关；10201-10299，支付相关；10301-10399 ，用户相关；10801-10899 第三方相关 ；10901-10999 保留给第三方定义 ；101001+具体业务相关。***********/

    //1-99  系统相关错误
    const e_system_no = 10000;//'无错误'
    const e_system_general = 10001;//'一般错误'
    const e_system_in = 10002;//'系统错误'
    const e_system_config_miss = 10003;//'配置缺少'
    const e_system_upload_file = 10004;//'文件上传错误'

    //101-199，接口相关
    const e_api_sign_miss = 10101;//'签名参数缺失'
    const e_api_sign_wrong = 10102; //'接口签名验证失败'
    const e_app_miss = 10103;//'appid不存在'
    const e_app_disabled = 10104;//'appId已被禁用'

    //301-399 ，用户相关
    const e_user_disabled = 10301;//'用户已被禁用'
    const e_user_miss = 10302;//'用户不存在'
    const e_user_pass_wrong = 10303;//'用户密码错误'
    const e_user_role_disabled = 10304;//'用户组已被禁用'
    const e_api_multiple_device_login = 10305;//'用户在其它设备登录'
    const e_api_user_token_miss = 10306;//'用户访问令牌缺失'
    const e_api_user_token_expire = 10307;//'用户访问令牌过期'
    const e_api_user_login_type = 10308;//'用户登录方式错误'
}