
安装单元测试扩展
php composer.phar config -g repo.packagist composer https://packagist.phpcomposer.com
php composer.phar update topthink/think-testing

进行单元测试
php think unit

通常在ThinkPHP中进行单元测试需要遵守以下的规范：
1.测试类保存在tests目录下
2.针对某个控制器的测试类命名规则为xxxTest.php，比如针对Index控制器进行测试的话，则测试的命名为：IndexTest.php
3.测试类通常继承自TestCase，命名空间通常为tests。
4.针对某个操作的测试通常命名为testxxx，比如针对Index控制器下的index操作，其测试方法命名为：testIndex，并且需要为公有方法(public)。
5.建议：当对同一个操场进行多种测试的时候，测试方法的命名可以在尾部递增数字，然后使用注释进行说明，而不用去想具体的测试范围所对应的名字。比如testIndex1，testIndex2.