<?php

namespace app\common\validate;

use think\Validate;

class Taglist extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['tag_id', 'require|number|gt:0', 'Tag ID必填|Tag ID必须是数字|Tag ID格式不正确'],
        ['article_id', 'require|number|gt:0', '文章ID必填|文章ID必须是数字|文章ID格式不正确'],
    ];

    protected $scene = [
        'add' => ['tag_id', 'article_id'],
        'edit' => ['tag_id', 'article_id'],
        'del' => ['id'],
    ];
}