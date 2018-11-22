<?php

namespace TwigJs\Tests;

use DNode\DNode;
use PHPUnit\Framework\TestCase;
use React;
use React\EventLoop\StreamSelectLoop;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use TwigJs\Twig\TwigJsExtension;
use TwigJs\JsCompiler;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;

class FullIntegrationTest extends TestCase
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
     * @var \Twig_Environment
     */
    private $env;

    /**
     * @var \Twig_Loader_Array
     */
    private $arrayLoader;

    public function setDnode($dnode, $loop)
    {
        $this->dnode = $dnode;
        $this->loop = $loop;
    }

    public function setUp()
    {
        $this->arrayLoader = new Twig_Loader_Array(array());
        $this->env = new Twig_Environment($this->arrayLoader);
        $this->env->addExtension(new TwigJsExtension());
        $this->env->setLoader(
            new Twig_Loader_Chain(
                array(
                    $this->arrayLoader,
                    new Twig_Loader_Filesystem(__DIR__.'/Fixture/integration')
                )
            )
        );
        $this->env->setCompiler(new JsCompiler($this->env));
    }

    /**
     * @test
     * @dataProvider getIntegrationTests
     */
    public function integrationTest($file, $message, $condition, $templates, $exception, $outputs)
    {
        foreach ($outputs as $match) {
            $templateParameters = $match[1];
            $javascript = '';
            foreach ($templates as $name => $twig) {
                $this->arrayLoader->setTemplate($name, $twig);
            }
            foreach ($templates as $name => $twig) {
                $javascript .= $this->compileTemplate($twig, $name);
            }
            $expectedOutput = trim($match[3], "\n ");
            $renderedOutput = $this->renderTemplate('index', $javascript, $templateParameters);
            $this->assertEquals($expectedOutput, $renderedOutput);
        }
    }

    public function getIntegrationTests()
    {
        $directory = new RecursiveDirectoryIterator(__DIR__ . '/Fixture/integration');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/\.test/', RecursiveRegexIterator::GET_MATCH);
        $test = $this;
        $tests = array_map(
            function ($file) use ($test) {
                return $test->loadTest($file);
            },
            array_keys(iterator_to_array($regex))
        );
        return $tests;
    }

    public function loadTest($file)
    {
        $test = file_get_contents($file);

        // @codingStandardsIgnoreStart
        if (preg_match('/--TEST--\s*(.*?)\s*(?:--CONDITION--\s*(.*))?\s*((?:--TEMPLATE(?:\(.*?\))?--(?:.*?))+)\s*(?:--DATA--\s*(.*))?\s*--EXCEPTION--\s*(.*)/sx', $test, $match)) {
            $message = $match[1];
            $condition = $match[2];
            $templates = $this->parseTemplates($match[3]);
            $exception = $match[5];
            $outputs = array(array(null, $match[4], null, ''));
        } elseif (preg_match('/--TEST--\s*(.*?)\s*(?:--CONDITION--\s*(.*))?\s*((?:--TEMPLATE(?:\(.*?\))?--(?:.*?))+)--DATA--.*?--EXPECT--.*/s', $test, $match)) {
            $message = $match[1];
            $condition = $match[2];
            $templates = $this->parseTemplates($match[3]);
            $exception = false;
            preg_match_all('/--DATA--(.*?)(?:--CONFIG--(.*?))?--EXPECT--(.*?)(?=\-\-DATA\-\-|$)/s', $test, $outputs, PREG_SET_ORDER);
        } else {
            throw new \InvalidArgumentException(sprintf('Test "%s" is not valid.', $file));
        }
        // @codingStandardsIgnoreStart

        return array(
            $file,
            $message,
            $condition,
            $templates,
            $exception,
            $outputs
        );
    }

    /**
     * @param string $test
     * @return array
     */
    protected static function parseTemplates($test)
    {
        $templates = array();
        preg_match_all('/--TEMPLATE(?:\((.*?)\))?--(.*?)(?=\-\-TEMPLATE|$)/s', $test, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $templates[($match[1] ? $match[1] : 'index.twig')] = $match[2];
        }

        return $templates;
    }

    /**
     * @param string $source
     * @param string $name
     * @return string
     * @throws \Twig_Error_Syntax
     */
    private function compileTemplate($source, $name)
    {
        $source = new \Twig_Source($source, $name);
        $javascript = $this->env->compileSource($source);
        return $javascript;
    }

    /**
     * @param string $name
     * @param string $javascript
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    private function renderTemplate($name, $javascript, $parameters)
    {
        $output = '';
        $this->dnode->connect(7070, function ($remote, $connection) use ($name, $javascript, $parameters, &$output) {
            $remote->render($name, $javascript, $parameters, function ($rendered) use ($connection, &$output) {
                $output = trim($rendered, "\n ");
                $connection->end();
            });
        });
        $this->loop->run();
        return $output;
    }
}
