<?php


namespace bazilio\yii2\bandit\models;


use yii\redis\ActiveRecord;

class BanditRecord extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->bandit->getConnection();
    }
}
