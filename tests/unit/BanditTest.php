<?php
namespace bazilio\yii2\bandit\tests\tests\unit;

use bazilio\yii2\bandit\decisions\HardToBeatDecision;
use bazilio\yii2\bandit\models\Test;
use bazilio\yii2\bandit\models\TestCase;

class BanditTest extends \yii\codeception\TestCase
{
    public $appConfig = '@tests/unit/_config.php';
    /** @var  \yii\redis\Connection */
    protected $redis;

    protected function setUp()
    {
        parent::setUp();

        $this->redis = \Yii::$app->bandit->getConnection();
    }

    public function loadFixtures($fixtures = null)
    {
        $test = new Test(['title' => 'rewards']);
        $test->save();

        $testCaseA = new TestCase(['title' => 'A', 'testId' => $test->id]);
        $testCaseA->save();

        $testCaseB = new TestCase(['title' => 'B', 'testId' => $test->id]);
        $testCaseB->save();

        $testCaseC = new TestCase(['title' => 'C', 'testId' => $test->id]);
        $testCaseC->save();
    }


    protected function tearDown()
    {
        $this->redis->executeCommand('FLUSHDB');
        parent::tearDown();
    }


    public function testTestCase()
    {
        /** @var Test $test */
        $test = Test::findOne(1);
        /** @var TestCase $testCase */
        $testCase = TestCase::findOne(1);

        $this->assertEquals(
            [1, 2, 3],
            \Yii::$app->redis->executeCommand('SMEMBERS', [Test::getTestCaseSetKey($test->id)])
        );

        $testCase->delete();
        $this->assertEquals(
            [2, 3],
            \Yii::$app->redis->executeCommand('SMEMBERS', [Test::getTestCaseSetKey($test->id)])
        );
    }

    public function testHitsAndRewards()
    {
        /** @var Test $test */
        $test = Test::findOne(1);
        /** @var TestCase $testCase */
        $testCase = TestCase::findOne(1);

        $testCase->updateCounters([TestCase::COUNTER_HITS => 1]);

        $this->assertEquals(2, $testCase->getConversion());
    }

    public function testRoundRobinDecision()
    {
        /** @var Test $test */
        $test = Test::findOne(1);
        /** @var TestCase $testCaseA */
        $testCaseA = TestCase::findOne(1);
        /** @var TestCase $testCaseB */
        $testCaseB = TestCase::findOne(1);
        /** @var TestCase $testCaseC */
        $testCaseC = TestCase::findOne(1);

        for ($i = 90; $i--;) {
            $testCase = $test->decide();
            $testCase->updateCounters([TestCase::COUNTER_REWARDS => rand(0, 100)]);
        }

        $testCaseA->refresh();
        $testCaseB->refresh();
        $testCaseC->refresh();

        $this->assertEquals($testCaseA->hits, $testCaseB->hits, $testCaseC->hits);
    }

    /**
     * Do a wrong trend at the start and recover
     */
    public function testHardToBeatDecision()
    {
        /** @var Test $test */
        $test = Test::findOne(1);
        /** @var TestCase $testCaseA */
        $testCaseA = TestCase::findOne(1);
        /** @var TestCase $testCaseB */
        $testCaseB = TestCase::findOne(1);
        /** @var TestCase $testCaseC */
        $testCaseC = TestCase::findOne(1);

        $test->decision = get_class(new HardToBeatDecision());

        for ($i = 9000; $i--;) {
            $testCase = $test->decide();
            $successRate = $i < 8000 ? (7 - $testCase->id * 2) : $testCase->id * 2;

            $testCase->updateCounters([TestCase::COUNTER_REWARDS => (rand(1, 1000) < $successRate * 10 ? 1 : 0)]);
        }

        $testCaseA->refresh();
        $testCaseB->refresh();
        $testCaseC->refresh();

        $this->assertTrue($testCaseA->hits > $testCaseC->hits);
    }
}
