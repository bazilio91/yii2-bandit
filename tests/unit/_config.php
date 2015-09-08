<?php
return [
    'id' => 'test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 10,
        ],
        'bandit' => [
            'class' => 'bazilio\yii2\bandit\BanditComponent',
            'connectionName' => 'redis',
        ],
    ]

];