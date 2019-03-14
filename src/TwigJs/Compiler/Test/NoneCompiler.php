<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class NoneCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'none';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('(null === ')
            ->subcompile($node->getNode('node'))
            ->raw(')')
        ;
    }
}
