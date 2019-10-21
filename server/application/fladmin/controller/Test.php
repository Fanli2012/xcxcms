<?php

namespace app\fladmin\controller;

class Test extends Base
{
    public function index()
    {
        echo md5('admin');
    }
}