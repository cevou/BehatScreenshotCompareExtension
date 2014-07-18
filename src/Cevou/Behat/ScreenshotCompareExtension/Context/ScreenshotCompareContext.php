<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

class ScreenshotCompareContext extends RawScreenshotCompareContext
{

    /**
     * Checks if the screenshot of the default session  is equal to a defined screen
     *
     * @Then /^the screenshot should be equal to "(?P<fileName>[^"]+)"$/
     */
    public function assertScreenshotCompare($fileName)
    {
        $this->compareScreenshot($this->getMink()->getDefaultSessionName(), $fileName);
    }

}