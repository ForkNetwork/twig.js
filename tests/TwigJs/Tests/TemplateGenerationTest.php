<?php

namespace TwigJs\Tests;

use PHPUnit\Framework\TestCase;
use Twig_Loader_Array;
use TwigJs\Twig\TwigJsExtension;
use TwigJs\JsCompiler;

class TemplateGenerationTest extends TestCase
{
    /**
     * @dataProvider providesGenerationTests()
     *
     * @param string $inputFile
     * @param string $outputFile
     * @throws \Twig_Error_Syntax
     */
    public function testGenerate(string $inputFile, string $outputFile): void
    {
        $arrayLoader = new Twig_Loader_Array(array());
        $env = new \Twig_Environment($arrayLoader);
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

    /**
     * @return array
     */
    public function providesGenerationTests(): array
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
