<?php

namespace TwigJs\Tests\Twig;

use PHPUnit\Framework\TestCase;
use TwigJs\Twig\TwigJsTokenParser;

class TwigJsTokenParserTest extends TestCase
{
    /**
     * @throws \Twig_Error_Syntax
     */
    public function testParse()
    {
        $env = $this->getEnv();
        $source = new \Twig_Source('{% twig_js name="foo" %}', 'foo');
        $stream = $env->tokenize($source);
        $token = $env->parse($stream)->getNode('body')->getNode(0);

        $this->assertInstanceOf('TwigJs\Twig\TwigJsNode', $token);
        $this->assertEquals('foo', $token->getAttribute('name'));
    }

    /**
     * @return \Twig_Environment
     */
    private function getEnv()
    {
        $arrayLoader = new \Twig_Loader_Array();
        $env = new \Twig_Environment($arrayLoader);
        $env->addTokenParser(new TwigJsTokenParser());

        return $env;
    }
}
