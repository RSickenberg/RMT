<?php

namespace Liip\RD\Tests\Functional;

class PrerequisitesTest extends RDFunctionalTestBase
{

    public function testDisplayLastChange()
    {
        $this->createJsonConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('display-last-changes'),
            'vcs' => 'git'
        ));
        $this->initGit();
        exec('git tag 1');
        exec('echo "foo" > fileFoo');
        exec('git add fileFoo');
        exec('git commit -m "Add a simple file"');
        exec('git mv fileFoo fileBar');
        exec('git commit -m "Rename foo to bar"');

        exec('./RD release', $consoleOutput, $exitCode);
        $consoleOutput = implode("\n", $consoleOutput);
        $this->assertNotContains("First commit", $consoleOutput);
        $this->assertContains("Add a simple file", $consoleOutput);
        $this->assertContains("Rename foo to bar", $consoleOutput);
    }

    public function testWorkingCopyCheck()
    {
        $this->createJsonConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'),
            'vcs' => 'git'
        ));
        $this->initGit();
        exec('git tag 1');

        exec('touch toto');
        exec('./RD release', $consoleOutput, $exitCode);
        $this->assertEquals(1, $exitCode);
        $this->assertContains("local modification", implode("\n", $consoleOutput));

        exec('./RD release --ignore-check', $consoleOutput, $exitCode);
        $this->assertEquals(0, $exitCode);
        exec('git tag', $tags);
        $this->assertEquals(array('1','2'), $tags);
    }

}
