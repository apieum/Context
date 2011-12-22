<?php

/**
 * File TemplateTests.php
 *
 * PHP version 5.2
 *
 * @category Tests
 * @package  Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Template.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'Template.php';

/**
 * Test class for Template.
 * 
 * @category Tests
 * @package  Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     TemplateTest
 *
 */
class Context_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Context_Template
     */
    protected $object;

    /**
     * Sets up the fixture
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new Context_Template('context', 'getter');
    }

    /**
     * test markOff
     * 
     * @test
     * @return null
     */
    public function testMarkOff()
    {
        $this->assertEquals('\{string\}', $this->object->markOff('string'));
    }

    /**
     * test sanitize
     * 
     * @test
     * @return null
     */
    public function testSanitize()
    {
        $this->assertEquals('{string}', $this->object->sanitize('\{string\}'));
    }
}
?>
