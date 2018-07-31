无忧帮帮项目
===============================

无忧帮帮新架构是基于 [Yii 2](http://www.yiiframework.com/)PHP框架开发，未来目标分别为API，平台管理，微信端，官网全面更新升级。


数据库：MySQL（阿里云RDS)  
PHP框架：Yii2  
缓存：Redis  
Session：Redis  
复杂数据：MongoDB  
搜素引擎：ElasticSearch  
队列：Redis + php-resque (后期升级阿里云消息队列MQ)  
短信：阿里大于  
邮件：SendCloud  
推送：极光  


我们使用[vagrant](https://www.vagrantup.com/) 统一开发环境，必须确保团队成员的开发环境是一致。
安装vagrant 1.9.5 [下载地址](https://www.vagrantup.com/)
按照VirtualBox 5.1 [下载地址](https://www.virtualbox.org/wiki/Downloads)

运行方法
```$xslt
    git clone https://git.281.com.cn/wuyou/wuyoux.git
    
    切换分支到开发分支develop后，安装必要插件
    vagrant plugin install vagrant-vbguest
    vagrant plugin install vagrant-hostmanager
    
    vagrant基本命令
    vagrant up  开启开发环境
    vagrant suspend 休眠开发环境(建议平时休眠就好)
    vagrant resume  从休眠中唤醒
    vagrant hald 关闭开发环境
``` 


源码目录结构
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
app-api
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
app-wechat
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
app-backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
app-frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```

Yii基本运行方法
--------------------
```$xslt
    ssh到vagrant开发环境，同步的开发目录是 /app
    ssh vagrant@192.168.28.11 密码 vagrant
    cd /app 
    php init #将会选择开发环境 
```
开发环境指向域名  
API：http://api.wuyou.dev  
后台：http://admin.wuyou.dev  
前台：http://www.wuyou.dev  
微信端：http://wx.wuyou.dev  
PHPMyAdmin: http://db.wuyou.dev/phpMyAdmin
Adminer: http://db.wuyou.dev/adminer.php
````

