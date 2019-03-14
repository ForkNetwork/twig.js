<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class OddCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'odd';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('1 === ')
            ->subcompile($node->getNode('node'))
            ->raw(' % 2')
        ;
    }
}
