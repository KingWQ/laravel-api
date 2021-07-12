#### 测试编写
1. 数据不插入数据库    
```
use DatabaseTransactions; 
```
2. phpunit分为功能测试(Feature)和单元测试(Unit)
    > Feature接口层面的测试, Unit函数层面的测试，改一下继承的TestCase

```
# 生成feature
php artisan make:test AuthTest
# 生成unit
php artisan make:test AuthTest --unit
```

#### 测试使用
1. phpunit 是所有测试都执行
2. phpunit 加命名空间 这个类的测试执行
```
phpunit tests/Feature/AuthTest
```
3. phpunit 类下面group分组 在类方法注释标明分组
```
phpunit tests/Feature/AuthTest --group register
```
4. phpunit 类下面filter过滤搜索
```
phpunit tests/Feature/AuthTest --filter RegisterMobile
```


