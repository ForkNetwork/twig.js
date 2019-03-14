<?php

namespace TwigJs;

use Twig\Node\Expression\FilterExpression;

interface FilterCompilerInterface
{
    public function getName();
    public function compile(JsCompiler $compiler, FilterExpression $filter);
}
