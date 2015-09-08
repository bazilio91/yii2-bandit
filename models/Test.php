<?php

namespace bazilio\yii2\bandit\models;

/**
 * Class Test
 * @package bazilio\yii2\bandit\models
 *
 * @property $id mixed pk
 * @property $title string
 * @property $decision string Decision class name
 */
class Test extends BanditRecord
{
    public static $decisions = [
        \bazilio\yii2\bandit\decisions\HardToBeatDecision::class => 'Round robin',
        \bazilio\yii2\bandit\decisions\RoundRobinDecision::class => 'Hard to beat',
    ];

    public function attributes()
    {
        return [
            'id',
            'title',
            'decision'
        ];
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
        ];
    }

    /**
     * @param $id string Test::$id
     * @return string
     */
    public static function getTestCaseSetKey($id)
    {
        return get_called_class() . ":cases:$id";
    }

    private static $testCases;

    public function getTestCases()
    {
        if (is_null(self::$testCases)) {
            $ids = $this->getDb()->executeCommand('SMEMBERS', [static::getTestCaseSetKey($this->id)]);
            if (!$ids) {
                self::$testCases = [];
            }

            self::$testCases = TestCase::findAll($ids);

        }

        return self::$testCases;
    }

    /**
     * @return \bazilio\yii2\bandit\decisions\DecisionInterface
     */
    public function getDecisionClass()
    {
        if (empty($this->decision)) {
            reset(static::$decisions);
            return key(static::$decisions);
        } else {
            return $this->decision;
        }
    }

    /**
     * @return TestCase
     */
    public function decide()
    {
        $testCases = $this->getTestCases();

        $testCases = array_map(
            function ($r) {
                $r->refresh();
                return $r;
            },
            $testCases
        );

        $decisionClass = $this->getDecisionClass();
        $case = $decisionClass::getDecision($testCases);
        $case->updateCounters([TestCase::COUNTER_HITS => 1]);

        return $case;
    }
}
