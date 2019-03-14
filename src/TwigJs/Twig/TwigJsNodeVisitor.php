<?php

namespace TwigJs\Twig;

use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

class TwigJsNodeVisitor implements NodeVisitorInterface
{
    /**
     * @var TwigJsNode
     */
    private $moduleNode;

    public function enterNode(Node $node, Environment $env)
    {
        if ($node instanceof \Twig\Node\ModuleNode) {
            return $this->moduleNode = $node;
        }

        return $node;
    }

    public function leaveNode(Node $node, Environment $env)
    {
        if ($node instanceof TwigJsNode) {
            if ($node->hasAttribute('name')) {
                $this->moduleNode->setAttribute(
                    'twig_js_name',
                    $node->getAttribute('name')
                );
            }

            return false;
        }

        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
