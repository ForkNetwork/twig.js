<?php

namespace TwigJs\Compiler\Expression\Test;

use Twig\Node\Expression\TestExpression;
use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class ConstantCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\Test\ConstantTest';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\Test\ConstantTest) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        $compiler->subcompile(
            new TestExpression(
                $node->getNode('node'),
                $node->getAttribute('name'),
                $node->hasNode('arguments') ? $node->getNode('arguments') : null,
                $node->getTemplateLine()
            )
        );
    }
}
