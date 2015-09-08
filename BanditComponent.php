<?php

namespace bazilio\yii2\bandit;


class BanditComponent extends \yii\base\Component
{
    /**
     * @var string component name
     */
    public $connectionName;

    /**
     * @return \yii\redis\Connection
     */
    public function getConnection()
    {
        return \Yii::$app->{$this->connectionName};
    }
}