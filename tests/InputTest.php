<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Compiler;

function _dump($value)
{
    fwrite(STDOUT, print_r($value, true));
}

function _quote($str)
{
    return preg_quote($str, '/');
}

/**
 * Input test - runs all the tests in inputs/ and compares their output to outputs/
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class InputTest extends TestCase
{
    private $scss;

    protected static $inputDir = 'inputs';
    protected static $outputDir = 'outputs';
    protected static $outputNumberedDir = 'outputs_numbered';

    /**
     * @dataProvider fileNameProvider
     */
    public function testInputFile($inFname, $outFname)
    {
        chdir(__DIR__);

        $this->scss = new Compiler();
        $this->scss->addImportPath('/'.self::$inputDir);

        if (getenv('BUILD')) {
            $this->buildInput($inFname, $outFname);
            $this->assertNull(null);
            return;
        }

        if (! is_readable($outFname)) {
            $this->fail("$outFname is missing, consider building tests with BUILD=1");
        }

        $input = file_get_contents($inFname);
        $output = file_get_contents($outFname);

        $this->assertEquals($output, $this->scss->compile($input, substr($inFname, strlen(__DIR__) + 1)));
    }


    public function testInputFileNoRoot()
    {
        chdir(__DIR__);

        $inFname = '/inputs/imports/relative.scss';
        $outFname = '/outputs/relative.css';

        $this->scss = new Compiler();
        $this->scss->addImportPath('/'.self::$inputDir);

        if (getenv('BUILD')) {
            $this->buildInput($inFname, $outFname);
            $this->assertNull(null);
            return;
        }

        if (! is_readable($outFname)) {
            $this->fail("$outFname is missing, consider building tests with BUILD=1");
        }

        $input = file_get_contents($inFname);
        $output = file_get_contents($outFname);

        $this->assertEquals($output, $this->scss->compile($input, substr($inFname, strlen(__DIR__) + 1)));
    }


    /**
     * Run all tests with line numbering
     *
     * @dataProvider numberedFileNameProvider
     */
    public function testLineNumbering($inFname, $outFname)
    {
        chdir(__DIR__);

        $this->scss = new Compiler();
        $this->scss->addImportPath(self::$inputDir);

        $this->scss->setLineNumberStyle(Compiler::LINE_COMMENTS);

        if (getenv('BUILD')) {
            $this->buildInput($inFname, $outFname);
            $this->assertNull(null);
            return;
        }

        if (! is_readable($outFname)) {
            $this->fail("$outFname is missing, consider building tests with BUILD=true");
        }

        $input = file_get_contents($inFname);
        $output = file_get_contents($outFname);

        $this->assertEquals($output, $this->scss->compile($input, substr($inFname, strlen(__DIR__) + 1)));
    }

    public function fileNameProvider()
    {
        return array_map(
            function ($a) {
                return [$a, InputTest::outputNameFor($a)];
            },
            self::findInputNames()
        );
    }

    public function numberedFileNameProvider()
    {
        return array_map(
            function ($a) {
                return [$a, InputTest::outputNumberedNameFor($a)];
            },
            self::findInputNames()
        );
    }

    // only run when env is set
    public function buildInput($inFname, $outFname)
    {
        $css = $this->scss->compile(file_get_contents($inFname), substr($inFname, strlen(__DIR__) + 1));

        file_put_contents($outFname, $css);
    }

    public static function findInputNames($pattern = '*')
    {
        $files = glob(__DIR__ . '/' . self::$inputDir . '/' . $pattern);
        $files = array_filter($files, 'is_file');

        if ($pattern = getenv('MATCH')) {
            $files = array_filter($files, function ($fname) use ($pattern) {
                return preg_match("/$pattern/", $fname);
            });
        }

        return $files;
    }

    public static function outputNameFor($input)
    {
        $front = _quote(__DIR__ . '/');
        $out = preg_replace("/^$front/", '', $input);

        $in = _quote(self::$inputDir . '/');
        $out = preg_replace("/$in/", self::$outputDir . '/', $out);
        $out = preg_replace('/.scss$/', '.css', $out);

        return __DIR__ . '/' . $out;
    }

    public static function outputNumberedNameFor($input)
    {
        $front = _quote(__DIR__ . '/');
        $out = preg_replace("/^$front/", '', $input);

        $in = _quote(self::$inputDir . '/');
        $out = preg_replace("/$in/", self::$outputNumberedDir . '/', $out);
        $out = preg_replace('/.scss$/', '.css', $out);

        return __DIR__ . '/' . $out;
    }

    public static function buildTests($pattern)
    {
        $files = self::findInputNames($pattern);

        foreach ($files as $file) {
        }
    }
}
