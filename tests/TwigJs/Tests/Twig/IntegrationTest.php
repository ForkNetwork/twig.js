<?php

namespace TwigJs\Tests\Twig;

use PHPUnit\Framework\TestCase;
use TwigJs\Twig\TwigJsExtension;

class IntegrationTest extends TestCase
{
    /**
     * @throws \Twig_Error_Syntax
     */
    public function testNameIsSetOnModule()
    {
        $env = $this->getEnv();
        $source = new \Twig_Source('{% twig_js name="foo" %}', 'foo');
        $module = $env->parse($env->tokenize($source));

        $this->assertTrue($module->hasAttribute('twig_js_name'));
        $this->assertEquals('foo', $module->getAttribute('twig_js_name'));
        $this->assertEquals(0, count($module->getNode('body')));
    }

    /**
     * @return \Twig_Environment
     */
    private function getEnv()
    {
        $arrayLoader = new \Twig_Loader_Array();
        $env = new \Twig_Environment($arrayLoader);
        $env->addExtension(new TwigJsExtension());

        return $env;
    }
}
