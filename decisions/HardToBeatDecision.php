<?php


namespace bazilio\yii2\bandit\decisions;

/**
 * Automatically raises best case's hit rate
 * http://stevehanov.ca/blog/index.php?id=132
 * Class HardToBearDecision
 * @package bazilio\yii2\bandit\decisions
 */
class HardToBeatDecision implements DecisionInterface
{

    public static function getDecision($testCases)
    {
        usort(
            $testCases,
            function ($a, $b) {
                $ac = $a->getConversion();
                $bc = $b->getConversion();
                if ($ac == $bc) {
                    if ($a->hits === $b->hits) {
                        return 0;
                    }

                    return $a->hits > $b->hits;
                }

                return $ac > $bc;
            }
        );

        if (rand(0, 10) < 1) {
            // exploration
            $case = $testCases[array_rand($testCases)];
        } else {
            $case = $testCases[0];
        }

        return $case;
    }
}