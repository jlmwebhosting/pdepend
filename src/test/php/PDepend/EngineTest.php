<?php
/**
 * This file is part of PDepend.
 * 
 * PHP Version 5
 *
 * Copyright (c) 2008-2013, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
  */

namespace PDepend;

/**
 * Test case for \PDepend\Engine facade.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @covers \PDepend\Engine
 * @group unittest
 */
class EngineTest extends AbstractTest
{
    /**
     * Tests that the {@link \PDepend\Engine::addDirectory()} method
     * fails with an exception for an invalid directory.
     *
     * @return void
     */
    public function testAddInvalidDirectoryFail()
    {
        $dir = dirname(__FILE__) . '/foobar';
        $msg = "Invalid directory '{$dir}' added.";
        
        $this->setExpectedException('InvalidArgumentException', $msg);
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory($dir);
    }
    /**
     * Tests that the {@link \PDepend\Engine::addDirectory()} method
     * with an existing directory.
     *
     * @return void
     */
    public function testAddDirectory()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
    }

    /**
     * testAnalyzeMethodReturnsAnIterator
     *
     * @return void
     */
    public function testAnalyzeMethodReturnsAnIterator()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->addFileFilter(new \PDepend\Input\ExtensionFilter(array('php')));

        $this->assertInstanceOf('Iterator', $engine->analyze());
    }
    
    /**
     * Tests the {@link \PDepend\Engine::analyze()} method and the return
     * value.
     *
     * @return void
     */
    public function testAnalyze()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->addFileFilter(new \PDepend\Input\ExtensionFilter(array('php')));
        
        $metrics = $engine->analyze();
        
        $expected = array(
            'package1'  =>  true,
            'package2'  =>  true,
            'package3'  =>  true
        );
        
        foreach ($metrics as $metric) {
            unset($expected[$metric->getName()]);
        }
        
        $this->assertEquals(0, count($expected));
    }
    
    /**
     * Tests that {@link \PDepend\Engine::analyze()} throws an exception
     * if no source directory was set.
     *
     * @return void
     */
    public function testAnalyzeThrowsAnExceptionForNoSourceDirectory()
    {
        $engine = $this->createEngineFixture();
        $this->setExpectedException('RuntimeException', 'No source directory and file set.');
        $engine->analyze();
    }
    
    /**
     * testAnalyzeReturnsEmptyIteratorWhenNoPackageExists
     *
     * @return void
     */
    public function testAnalyzeReturnsEmptyIteratorWhenNoPackageExists()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->addFileFilter(new \PDepend\Input\ExtensionFilter(array(__METHOD__)));
       
        $this->assertEquals(0, $engine->analyze()->count()); 
    }
    
    /**
     * Tests that {@link \PDepend\Engine::analyze()} configures the
     * ignore annotations option correct.
     *
     * @return void
     */
    public function testAnalyzeSetsWithoutAnnotations()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->addFileFilter(new \PDepend\Input\ExtensionFilter(array('inc')));
        $engine->setWithoutAnnotations();
        $packages = $engine->analyze();
        
        $this->assertEquals(2, $packages->count());
        $this->assertEquals('pdepend.test', $packages->current()->getName());
        
        $function = $packages->current()->getFunctions()->current();
        
        $this->assertNotNull($function);
        $this->assertEquals('foo', $function->getName());
        $this->assertEquals(0, $function->getExceptionClasses()->count());
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::countClasses()} method
     * returns the expected number of classes.
     *
     * @return void
     */
    public function testCountClasses()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->addFileFilter(new \PDepend\Input\ExtensionFilter(array('php')));
        $engine->analyze();
        
        $this->assertEquals(10, $engine->countClasses());
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::countClasses()} method fails
     * with an exception if the code was not analyzed before.
     *
     * @return void
     */
    public function testCountClassesWithoutAnalyzeFail()
    {
        $this->setExpectedException(
            'RuntimeException', 
            'countClasses() doesn\'t work before the source was analyzed.'
        );
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->countClasses();
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::countPackages()} method
     * returns the expected number of packages.
     *
     * @return void
     */
    public function testCountPackages()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->analyze();
        
        $this->assertEquals(4, $engine->countPackages());
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::countPackages()} method
     * fails with an exception if the code was not analyzed before.
     *
     * @return void
     */
    public function testCountPackagesWithoutAnalyzeFail()
    {
        $this->setExpectedException(
            'RuntimeException', 
            'countPackages() doesn\'t work before the source was analyzed.'
        );
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->countPackages();
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::getPackage()} method
     * returns the expected {@link \PDepend\Source\AST\ASTNamespace} objects.
     *
     * @return void
     */
    public function testGetPackage()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->analyze();
        
        $packages = array(
            'package1', 
            'package2', 
            'package3'
        );
        
        $className = '\\PDepend\\Source\\AST\\ASTNamespace';
        
        foreach ($packages as $package) {
            $this->assertInstanceOf($className, $engine->getPackage($package));
        }
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::getPackage()} method fails
     * with an exception if the code was not analyzed before.
     *
     * @return void
     */
    public function testGetPackageWithoutAnalyzeFail()
    {
        $this->setExpectedException(
            'RuntimeException',
            'getPackage() doesn\'t work before the source was analyzed.'
        );
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->getPackage('package1');
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::getPackage()} method fails
     * with an exception if you request an invalid package.
     *
     * @return void
     */
    public function testGetPackageWithUnknownPackageFail()
    {
        $this->setExpectedException(
            'OutOfBoundsException',
            'Unknown package "package0".'
        );
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->analyze();
        $engine->getPackage('package0');
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::getPackages()} method
     * returns the expected {@link \PDepend\Source\AST\ASTNamespace} objects
     * and reuses the result of {@link \PDepend\Engine::analyze()}.
     *
     * @return void
     */
    public function testGetPackages()
    {
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        
        $package1 = $engine->analyze();
        $package2 = $engine->getPackages();
        
        $this->assertNotNull($package1);
        $this->assertNotNull($package2);
        
        $this->assertSame($package1, $package2);
    }
    
    /**
     * Tests that the {@link \PDepend\Engine::getPackages()} method
     * fails with an exception if the code was not analyzed before.
     *
     * @return void
     */
    public function testGetPackagesWithoutAnalyzeFail()
    {
        $this->setExpectedException(
            'RuntimeException', 
            'getPackages() doesn\'t work before the source was analyzed.'
        );
        
        $engine = $this->createEngineFixture();
        $engine->addDirectory(self::createCodeResourceUriForTest());
        $engine->getPackages();
    }

    /**
     * Tests the newly added support for single file handling.
     *
     * @return void
     */
    public function testSupportForSingleFileIssue90()
    {
        $engine = $this->createEngineFixture();
        $engine->addFile(self::createCodeResourceUriForTest());
        $engine->analyze();

        $packages = $engine->getPackages();
        $this->assertSame(1, $packages->count());

        $package = $packages->current();
        $this->assertSame(1, $package->getClasses()->count());
        $this->assertSame(1, $package->getInterfaces()->count());
    }

    /**
     * Tests that the addFile() method throws the expected exception when an
     * added file does not exist.
     *
     * @return void
     * @expectedException InvalidArgumentException
     */
    public function testAddFileMethodThrowsExpectedExceptionForFileThatNotExists()
    {
        $engine = $this->createEngineFixture();
        $engine->addFile(self::createRunResourceURI('pdepend_'));
    }
}
