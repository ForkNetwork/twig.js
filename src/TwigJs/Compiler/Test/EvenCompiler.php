<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class EvenCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'even';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('(0 === ')
            ->subcompile($node->getNode('node'))
            ->raw(' % 2)')
        ;
    }
}
