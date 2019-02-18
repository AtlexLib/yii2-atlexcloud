AtlexCloud Extention
====================
AtlexCloud Yii2 Extention FTP SWIFT OpenStack

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist atlexlib/yii2-atlexcloud "*"
```

or add

```
"atlexlib/yii2-atlexcloud": "*"
```

to the require section of your `composer.json` file.



Register Modul in config/web.php
-----
```php
$config['modules']['atlexcloud'] = [
    'class' => 'app\modules\atlex\atlexcloud',
];

return $config;

```


Register cloud connection parameters in config/params.php
-----
```php


return [
    'adminEmail' => 'admin@example.com',



    'atlexcloud' => [

        'project' => 'your-cloud-project',
        'local_folder' => 'local_storage', //[root path of current Yii project] / local_storage  create folder and set full permission chmod 777

        'openstack' => [
            'url' => 'https://your-url',
            'user' => 'your-user',
            'password' => 'your-password',
        ],

        's3' => [
            'url' => 'https://your-url',
            'user' => 'your-user',
            'password' => 'your-password',
        ],

        'ftp' => [
            'url' => 'your-url',
            'user' => 'your-user',
            'password' => 'your-password',
        ],

        'default_adapter' => 's3'
    ]

];

```


Once the extension is installed, simply use it in your code by  :

```php

<b>How to use AtlexCloudWidget in view</b>

<?= app\modules\atlex\components\AtlexCloudWidget::widget(); ?>


```
