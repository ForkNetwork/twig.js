<?php
namespace TwigJs\Tests;

use DNode\DNode;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use React;
use React\EventLoop\StreamSelectLoop;

class Listener implements TestListener
{
    /**
     * @var StreamSelectLoop
     */
    private $loop;

    /**
     * @var DNode
     */
    private $dnode;

    /**
     * @param Test $test
     */
    public function startTest(Test $test): void
    {
        if ($test instanceof FullIntegrationTest) {
            $this->loop = new StreamSelectLoop();
            $this->dnode = new DNode($this->loop);
            $test->setDnode($this->dnode, $this->loop);
        }
    }

    /**
     * @param TestSuite $suite
     * @throws \Exception
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (isset($this->dnode)) {
            $exit = function ($remote, $connection) {
                $remote->exit(function () use ($connection) {
                    $connection->end();
                });
            };

            $this->dnode->on('error', function ($e) {
                // Do nothing.
                // This error means the dnode server isn't running, so it doesn't
                // matter that we can't connect to it in order to shut it down.
            });

            $this->dnode->connect(7070, $exit);
            $this->loop->run();
        }
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
    }
}
