<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\Behat\Context\Context;

interface ScreenshotCompareAwareContext extends Context
{

    /**
     * Sets the filesystem to save the screenshots on failure
     *
     * @param array $filesystem
     */
    public function setScreenshotCompareConfigurations(array $configurations);

    /**
     * Sets parameters provided for screenshot compare.
     *
     * @param array $parameters
     */
    public function setScreenshotCompareParameters(array $parameters);
}
