<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\Behat\Context\Context;

interface ScreenshotCompareAwareContext extends Context
{

    /**
     * Sets parameters provided for screenshot compare.
     *
     * @param array $parameters
     */
    public function setScreenshotCompareParameters(array $parameters);

} 