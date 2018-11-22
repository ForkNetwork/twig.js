<?php

namespace TwigJs\Tests;

use PHPUnit\Framework\TestCase;
use Twig_Loader_Array;
use TwigJs\Twig\TwigJsExtension;
use TwigJs\JsCompiler;

class TemplateGenerationTest extends TestCase
{
    /**
     * @dataProvider getGenerationTests
     */
    public function testGenerate($inputFile, $outputFile)
    {
        $this->arrayLoader = new Twig_Loader_Array(array());
        $env = new \Twig_Environment($this->arrayLoader);
        $env->addExtension(new TwigJsExtension());
        $env->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/Fixture/templates'));
        $env->setCompiler(new JsCompiler($env));

        $source = file_get_contents($inputFile);
        $source = new \Twig_Source($source, $inputFile);

        $this->assertEquals(
            file_get_contents($outputFile),
            $env->compileSource($source)
        );
    }

    public function getGenerationTests()
    {
        $tests = array();
        $files = new \RecursiveDirectoryIterator(
            __DIR__ . '/Fixture/templates',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        foreach ($files as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile()) {
                continue;
            }

            $tests[] = array(
                $file->getRealPath(),
                __DIR__.'/Fixture/generated/'.basename($file, '.twig').'.js',
            );
        }


        return $tests;
    }
}
