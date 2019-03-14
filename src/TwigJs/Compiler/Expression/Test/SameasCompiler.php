<?php

namespace TwigJs\Compiler\Expression\Test;

use Twig\Node\Expression\TestExpression;
use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class SameasCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\Test\SameasTest';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\Test\SameasTest) {
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
                $node->getNode('arguments'),
                $node->getTemplateLine()
            )
        );
    }
}
