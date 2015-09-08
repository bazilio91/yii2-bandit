<?php

namespace bazilio\yii2\bandit\decisions;

interface DecisionInterface
{
    public static function getDecision($testCases);
}