Yii2 A/B testing tool
===================================

This extension provides code to A/B test your views with [hard to beat](http://stevehanov.ca/blog/index.php?id=132)
 strategy.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bazilio/yii2-bandit
```

or add

```json
"bazilio/yii2-bandit": "*"
```

to the require section of your composer.json.

Requirements
-----------
- php >=5.4
- Backends:
  - [yii2-redis](https://github.com/yiisoft/yii2-redis)

Configuration
-------------

```php
'components' => [
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,
    ],
    'bandit' => [
        'class' => 'bazilio\yii2\bandit\BanditComponent',
        'connection' => 'redis'
    ]
]
```