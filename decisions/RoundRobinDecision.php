<?php


namespace bazilio\yii2\bandit\decisions;

/**
 * Splits hits between cases equally
 * Class RoundRobinDecision
 * @package bazilio\yii2\bandit\decisions
 */
class RoundRobinDecision implements DecisionInterface
{
    public static function getDecision($testCases)
    {
        usort(
            $testCases,
            function ($a, $b) {
                if ($a->hits === $b->hits) {
                    return 0;
                }

                return $a->hits > $b->hits;
            }
        );

        return end($testCases);
    }
}