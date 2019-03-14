<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class EmptyCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'empty';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('twig.empty(')
            ->subcompile($node->getNode('node'))
            ->raw(')')
        ;
    }
}
