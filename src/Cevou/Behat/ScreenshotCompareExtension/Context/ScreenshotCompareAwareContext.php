<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\Behat\Context\Context;
use Gaufrette\Filesystem;

interface ScreenshotCompareAwareContext extends Context
{

    /**
     * Sets the filesystem to save the screenshots on failure
     *
     * @param Filesystem $filesystem
     */
    public function setScreenshotCompareFilesystem(Filesystem $filesystem);

    /**
     * Sets parameters provided for screenshot compare.
     *
     * @param array $parameters
     */
    public function setScreenshotCompareParameters(array $parameters);

} 