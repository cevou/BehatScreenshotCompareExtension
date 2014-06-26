<?php

use Behat\Behat\Context\Context;
use Symfony\Component\Process\Process;

class SeleniumServerContext implements Context
{
    /**
     * @var Process
     */
    private static $seleniumServer;

    /**
     * @BeforeSuite
     */
    public static function startSeleniumServer()
    {
        self::$seleniumServer = new Process('java -jar  vendor/emagister/selenium-server/bin/selenium-server.jar');
        self::$seleniumServer->start();

        sleep(1);
    }

    /**
     * @AfterSuite
     */
    public static function stopSeleniumServer()
    {
        self::$seleniumServer->stop();
    }
}