<?php

namespace TwigJs\Twig;

use Twig\Extension\AbstractExtension;

class TwigJsExtension extends AbstractExtension
{
    public function getTokenParsers()
    {
        return array(new TwigJsTokenParser());
    }

    public function getNodeVisitors()
    {
        return array(new TwigJsNodeVisitor());
    }

    public function getName()
    {
        return 'twig_js';
    }
}
