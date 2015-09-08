<?php

namespace bazilio\yii2\bandit\models;

class TestCase extends BanditRecord
{
    const COUNTER_HITS = 'hits';
    const COUNTER_REWARDS = 'rewards';

    /**
     * @return \yii\redis\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->bandit->getConnection();
    }

    public function attributes()
    {
        return [
            'id',
            'testId',
            'title',
            static::COUNTER_HITS,
            static::COUNTER_REWARDS
        ];
    }

    public function rules()
    {
        return [
            [['title', 'testId'], 'required'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->updateCounters([static::COUNTER_REWARDS => 1, static::COUNTER_HITS => 1]);
        }

        if ($changedAttributes['testId']) {
            static::getDb()->executeCommand('SREM', [Test::getTestCaseSetKey($changedAttributes['testId']), $this->id]);
        }

        static::getDb()->executeCommand('SADD', [Test::getTestCaseSetKey($this->testId), $this->id]);

        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        static::getDb()->executeCommand('SREM', [Test::getTestCaseSetKey($this->testId), $this->id]);
        parent::afterDelete();
    }

    /**
     * @return float|int rewards rate
     */
    public function getConversion()
    {
        $rewards = $this->{static::COUNTER_REWARDS};
        if ($rewards > 0) {
            return $this->{static::COUNTER_HITS} / $rewards;
        }

        return 0;
    }
}
