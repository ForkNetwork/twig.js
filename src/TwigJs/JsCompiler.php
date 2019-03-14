<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TwigJs;

use Twig\Compiler as TwigCompiler;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use TwigJs\Compiler\MacroCompiler;
use TwigJs\Compiler\Test\SameAsCompiler;
use TwigJs\Compiler\Test\OddCompiler;
use TwigJs\Compiler\Test\NullCompiler;
use TwigJs\Compiler\Test\NoneCompiler;
use TwigJs\Compiler\Test\EvenCompiler;
use TwigJs\Compiler\Test\EmptyCompiler;
use TwigJs\Compiler\Test\DivisibleByCompiler;
use TwigJs\Compiler\Test\DefinedCompiler;
use TwigJs\Compiler\ImportCompiler;
use TwigJs\Compiler\AutoEscapeCompiler;
use TwigJs\Compiler\Expression\TempNameCompiler;
use TwigJs\Compiler\BlockReferenceCompiler;
use TwigJs\Compiler\BodyCompiler;
use TwigJs\Compiler\SetCompiler;
use TwigJs\Compiler\SpacelessCompiler;
use TwigJs\Compiler\IncludeCompiler;
use TwigJs\Compiler\Expression\ConditionalCompiler;
use TwigJs\Compiler\Expression\ArrayCompiler;
use TwigJs\Compiler\Expression\FunctionCompiler;
use TwigJs\Compiler\Expression\ParentCompiler;
use TwigJs\Compiler\BlockCompiler;
use TwigJs\Compiler\Expression\BlockReferenceCompiler as ExpressionBlockReferenceCompiler;
use TwigJs\Compiler\Expression\Binary\SubCompiler;
use TwigJs\Compiler\Expression\Binary\RangeCompiler;
use TwigJs\Compiler\Expression\Binary\PowerCompiler;
use TwigJs\Compiler\Expression\Binary\OrCompiler;
use TwigJs\Compiler\Expression\Binary\NotInCompiler;
use TwigJs\Compiler\Expression\Binary\NotEqualCompiler;
use TwigJs\Compiler\Expression\Binary\MulCompiler;
use TwigJs\Compiler\Expression\Binary\ModCompiler;
use TwigJs\Compiler\Expression\Binary\LessEqualCompiler;
use TwigJs\Compiler\Expression\Binary\LessCompiler;
use TwigJs\Compiler\Expression\Binary\InCompiler;
use TwigJs\Compiler\Expression\Binary\GreaterEqualCompiler;
use TwigJs\Compiler\Expression\Binary\GreaterCompiler;
use TwigJs\Compiler\Expression\Binary\FloorDivCompiler;
use TwigJs\Compiler\Expression\Binary\EqualCompiler;
use TwigJs\Compiler\Expression\Binary\DivCompiler;
use TwigJs\Compiler\Expression\Binary\ConcatCompiler;
use TwigJs\Compiler\Expression\Binary\BitwiseXorCompiler;
use TwigJs\Compiler\Expression\Binary\BitwiseOrCompiler;
use TwigJs\Compiler\Expression\Binary\BitwiseAndCompiler;
use TwigJs\Compiler\Expression\Binary\AndCompiler;
use TwigJs\Compiler\Expression\Binary\AddCompiler;
use TwigJs\Compiler\Expression\Unary\PosCompiler;
use TwigJs\Compiler\Expression\Unary\NotCompiler;
use TwigJs\Compiler\Expression\Unary\NegCompiler;
use TwigJs\Compiler\Expression\GetAttrCompiler;
use TwigJs\Compiler\Expression\ConstantCompiler;
use TwigJs\Compiler\Expression\AssignNameCompiler;
use TwigJs\Compiler\ForCompiler;
use TwigJs\Compiler\ForLoopCompiler;
use TwigJs\Compiler\Expression\FilterCompiler;
use TwigJs\Compiler\PrintCompiler;
use TwigJs\Compiler\Expression\NameCompiler;
use TwigJs\Compiler\Expression\TestCompiler;
use TwigJs\Compiler\IfCompiler;
use TwigJs\Compiler\TextCompiler;
use TwigJs\Compiler\NodeCompiler;
use TwigJs\Compiler\ModuleCompiler;

class JsCompiler extends TwigCompiler
{
    /** Whether the current expression is a template name */
    public $isTemplateName = false;

    /** The function name of the current template */
    public $templateFunctionName;

    /** Map for local variables */
    public $localVarMap = array();

    private $defines = array();

    private $scopes = array();
    private $scopeVariables = array();
    private $functionNamingStrategy;

    private $typeCompilers;
    private $filterCompilers;
    private $testCompilers;

    private $filterFunctions;
    private $functionMap;

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->typeCompilers = array(
            'Twig\Node\Node' => new NodeCompiler(),
            'Twig\Node\BodyNode' => new BodyCompiler(),
            'Twig\Node\ModuleNode' => new ModuleCompiler\GoogleCompiler(),
            'Twig\Node\BlockNode' => new BlockCompiler(),
            'Twig\Node\TextNode' => new TextCompiler(),
            'Twig\Node\IfNode' => new IfCompiler(),
            'Twig\Node\PrintNode' => new PrintCompiler(),
            'Twig\Node\ForNode' => new ForCompiler(),
            'Twig\Node\ForLoopNode' => new ForLoopCompiler(),
            'Twig\Node\SetNode' => new SetCompiler(),
            'Twig\Node\IncludeNode' => new IncludeCompiler(),
            'Twig\Node\SpacelessNode' => new SpacelessCompiler(),
            'Twig\Node\BlockReferenceNode' => new BlockReferenceCompiler(),
            'Twig\Node\AutoEscapeNode' => new AutoEscapeCompiler(),
            'Twig\Node\ImportNode' => new ImportCompiler(),
            'Twig\Node\MacroNode' => new MacroCompiler(),
            'Twig\Node\Expression\TempNameExpression' => new TempNameCompiler(),
            'Twig\Node\Expression\ConditionalExpression' => new ConditionalCompiler(),
            'Twig\Node\Expression\ArrayExpression' => new ArrayCompiler(),
            'Twig\Node\Expression\FunctionExpression' => new FunctionCompiler(),
            'Twig\Node\Expression\ParentExpression' => new ParentCompiler(),
            'Twig\Node\Expression\BlockReferenceExpression' => new ExpressionBlockReferenceCompiler(),
            'Twig\Node\Expression\AssignNameExpression' => new AssignNameCompiler(),
            'Twig\Node\Expression\TestExpression' => new TestCompiler(),
            'Twig\Node\Expression\NameExpression' => new NameCompiler(),
            'Twig\Node\Expression\FilterExpression' => new FilterCompiler(),
            'Twig\Node\Expression\Filter\DefaultFilter' => new Compiler\Expression\Filter\DefaultCompiler(),
            'Twig\Node\Expression\ConstantExpression' => new ConstantCompiler(),
            'Twig\Node\Expression\GetAttrExpression' => new GetAttrCompiler(),
            'Twig\Node\Expression\MethodCallExpression' => new Compiler\Expression\MethodCallCompiler(),
            'Twig\Node\Expression\Binary\AddBinary' => new AddCompiler(),
            'Twig\Node\Expression\Binary\AndBinary' => new AndCompiler(),
            'Twig\Node\Expression\Binary\BitwiseAndBinary' => new BitwiseAndCompiler(),
            'Twig\Node\Expression\Binary\BitwiseOrBinary' => new BitwiseOrCompiler(),
            'Twig\Node\Expression\Binary\BitwiseXorBinary' => new BitwiseXorCompiler(),
            'Twig\Node\Expression\Binary\ConcatBinary' => new ConcatCompiler(),
            'Twig\Node\Expression\Binary\DivBinary' => new DivCompiler(),
            'Twig\Node\Expression\Binary\EqualBinary' => new EqualCompiler(),
            'Twig\Node\Expression\Binary\FloorDivBinary' => new FloorDivCompiler(),
            'Twig\Node\Expression\Binary\GreaterBinary' => new GreaterCompiler(),
            'Twig\Node\Expression\Binary\GreaterEqualBinary' => new GreaterEqualCompiler(),
            'Twig\Node\Expression\Binary\InBinary' => new InCompiler(),
            'Twig\Node\Expression\Binary\LessBinary' => new LessCompiler(),
            'Twig\Node\Expression\Binary\LessEqualBinary' => new LessEqualCompiler(),
            'Twig\Node\Expression\Binary\ModBinary' => new ModCompiler(),
            'Twig\Node\Expression\Binary\MulBinary' => new MulCompiler(),
            'Twig\Node\Expression\Binary\NotEqualBinary' => new NotEqualCompiler(),
            'Twig\Node\Expression\Binary\NotInBinary' => new NotInCompiler(),
            'Twig\Node\Expression\Binary\OrBinary' => new OrCompiler(),
            'Twig\Node\Expression\Binary\PowerBinary' => new PowerCompiler(),
            'Twig\Node\Expression\Binary\RangeBinary' => new RangeCompiler(),
            'Twig\Node\Expression\Binary\SubBinary' => new SubCompiler(),
            'Twig\Node\Expression\Unary\NegUnary' => new NegCompiler(),
            'Twig\Node\Expression\Unary\NotUnary' => new NotCompiler(),
            'Twig\Node\Expression\Unary\PosUnary' => new PosCompiler(),
            'Twig\Node\Expression\Test\ConstantTest' => new Compiler\Expression\Test\ConstantCompiler(),
            'Twig\Node\Expression\Test\DefinedTest' => new Compiler\Expression\Test\DefinedCompiler(),
            'Twig\Node\Expression\Test\DivisiblebyTest' => new Compiler\Expression\Test\DivisiblebyCompiler(),
            'Twig\Node\Expression\Test\EvenTest' => new Compiler\Expression\Test\EvenCompiler(),
            'Twig\Node\Expression\Test\NullTest' => new Compiler\Expression\Test\NullCompiler(),
            'Twig\Node\Expression\Test\OddTest' => new Compiler\Expression\Test\OddCompiler(),
            'Twig\Node\Expression\Test\SameasTest' => new Compiler\Expression\Test\SameasCompiler(),
        );

        $this->testCompilers = array(
            'defined' => new DefinedCompiler(),
            'divisible by' => new DivisibleByCompiler(),
            'empty' => new EmptyCompiler(),
            'even' => new EvenCompiler(),
            'none' => new NoneCompiler(),
            'null' => new NullCompiler(),
            'odd' => new OddCompiler(),
            'same as' => new SameAsCompiler(),
        );

        $this->filterCompilers = array();
        $this->filterFunctions = array(
            '_default'    => 'twig.filter.def',
            'abs'         => 'twig.filter.abs',
            'batch'       => 'twig.filter.batch',
            'capitalize'  => 'twig.filter.capitalize',
            'default'     => 'twig.filter.def',
            'e'           => 'twig.filter.escape',
            'escape'      => 'twig.filter.escape',
            'first'       => 'twig.filter.first',
            'join'        => 'twig.filter.join',
            'json_encode' => 'twig.filter.json_encode',
            'keys'        => 'twig.filter.keys',
            'last'        => 'twig.filter.last',
            'length'      => 'twig.filter.length',
            'lower'       => 'twig.filter.lower',
            'merge'       => 'twig.filter.merge',
            'nl2br'       => 'twig.filter.nl2br',
            'replace'     => 'twig.filter.replace',
            'reverse'     => 'twig.filter.reverse',
            'title'       => 'twig.filter.title',
            'trim'        => 'twig.filter.trim',
            'upper'       => 'twig.filter.upper',
            'url_encode'  => 'encodeURIComponent',
        );

        $this->functionMap = array(
            'max'    => 'twig.functions.max',
            'min'    => 'twig.functions.min',
            'random' => 'twig.functions.random',
            'range'  => 'twig.range',
        );
    }

    public function setDefines(array $defines)
    {
        $this->defines = $defines;
    }

    public function setDefine($key, $value)
    {
        $this->defines[$key] = $value;
    }

    public function getDefine($key)
    {
        return isset($this->defines[$key]) ? $this->defines[$key] : null;
    }

    public function setFunctionNamingStrategy(FunctionNamingStrategyInterface $strategy)
    {
        $this->functionNamingStrategy = $strategy;
    }

    /**
     * Returns the function name for the given template name.
     *
     * @param  ModuleNode $module
     * @return string
     */
    final public function getFunctionName(ModuleNode $module)
    {
        if (null === $this->functionNamingStrategy) {
            $this->functionNamingStrategy = new DefaultFunctionNamingStrategy();
        }

        return $this->functionNamingStrategy->getFunctionName($module);
    }

    public function setTypeCompilers(array $compilers)
    {
        $this->typeCompilers = $compilers;
    }

    public function addTypeCompiler(TypeCompilerInterface $compiler)
    {
        $this->typeCompilers[$compiler->getType()] = $compiler;
    }

    public function getTestCompiler($name)
    {
        return isset($this->testCompilers[$name]) ?
            $this->testCompilers[$name] : null;
    }

    public function addTestCompiler(TestCompilerInterface $compiler)
    {
        $this->testCompilers[$compiler->getName()] = $compiler;
    }

    public function getFilterFunction($name)
    {
        return isset($this->filterFunctions[$name]) ?
            $this->filterFunctions[$name] : null;
    }

    public function setFilterFunction($filterName, $functionName)
    {
        $this->filterFunctions[$filterName] = $functionName;
    }

    public function getFilterCompiler($name)
    {
        return isset($this->filterCompilers[$name]) ?
            $this->filterCompilers[$name] : null;
    }

    public function addFilterCompiler(FilterCompilerInterface $compiler)
    {
        $this->filterCompilers[$compiler->getName()] = $compiler;
    }

    public function setJsFunction($twigFunctionName, $jsFunctionName)
    {
        $this->functionMap[$twigFunctionName] = $jsFunctionName;
    }

    public function getJsFunction($twigFunctionName)
    {
        return isset($this->functionMap[$twigFunctionName]) ?
            $this->functionMap[$twigFunctionName] : null;
    }

    public function compile(Node $node, $indentation = 0)
    {
        // Call parent with an empty node in order to clear the previously compiled template.
        parent::compile(new Node, $indentation);

        $this->subcompile($node);
        return $this;
    }

    public function subcompile(Node $node, $raw = true)
    {
        if ($node instanceof \Twig\Profiler\Node\EnterProfileNode
            || $node instanceof \Twig\Profiler\Node\LeaveProfileNode) {
            return $this;
        }

        if (false === $raw) {
            $this->write('');
        }

        $nodeClass = get_class($node);

        if (!isset($this->typeCompilers[$nodeClass])) {
            throw new \RuntimeException(sprintf('There is no compiler for node type "%s".', $nodeClass));
        }

        $this->typeCompilers[$nodeClass]->compile($this, $node);

        return $this;
    }

    public function enterScope()
    {
        $this->scopes[] = $this->scopeVariables;
        $this->scopeVariables = array();

        return $this;
    }

    public function leaveScope()
    {
        if (false === $lastScope = array_pop($this->scopes)) {
            throw new \RuntimeException('leaveScope() must be called only after enterScope.');
        }

        $this->localVarMap = array_diff_key($this->localVarMap, $this->scopeVariables);
        $this->scopeVariables = $lastScope;
        $this->localVarMap = array_merge($this->localVarMap, $this->scopeVariables);

        return $this;
    }

    public function setVar($var, $localName)
    {
        $this->localVarMap[$var] =
        $this->scopeVariables[$var] = $localName;

        return $this;
    }

    public function unsetVar($var)
    {
        unset($this->localVarMap[$var]);

        return $this;
    }

    public function setTemplateName($bool)
    {
        $this->isTemplateName = (Boolean) $bool;

        return $this;
    }

    public function string($value)
    {
        return $this->repr($value);
    }

    public function repr($value)
    {
        $this->raw(json_encode($value));
        return $this;
    }
}
