<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class DivisibleByCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'divisible by';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('0 === ')
            ->subcompile($node->getNode('node'))
            ->raw(' % ')
            ->subcompile($node->getNode('arguments')->getNode(0))
        ;
    }
}
