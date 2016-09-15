<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\Mink\Exception\ExpectationException;

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

    /**
     * Helper to generate the screenshot.
     *
     * @Then I generate the screenshot :filename
     */
    public function iGenerateTheScreenshot($filename)
    {
        $configuration = $this->getScreenshotParameters();

        foreach ($configuration['screenshot_config']['breakpoints'] as $breakpoint_name => $parameters) {
            $this->getSession()->resizeWindow($parameters['width'], $parameters['height']);
            $screenshot = $this->getSession()->getScreenshot();
            $directory = $configuration['screenshot_dir'] . '/' . $breakpoint_name . '/';
            $full_name = $directory . $filename;
            if (!file_exists($full_name)) {
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }
                file_put_contents($full_name, $screenshot);
                //if (!file_exists('/Users/rob/Desktop/tmp/' . $breakpoint_name . '/')) {
                //    mkdir('/Users/rob/Desktop/tmp/' . $breakpoint_name . '/', 0777, true);
                //}
                //file_put_contents('/Users/rob/Desktop/tmp/' . $breakpoint_name . '/' . $filename, $screenshot);
            } else {
                throw new ExpectationException(
                    'Tried to generate ' . $full_name . ' but it already exists.',
                    $this->getSession()
                );
            }
        }
    }
}
