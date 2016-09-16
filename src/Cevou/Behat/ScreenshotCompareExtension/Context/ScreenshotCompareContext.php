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
        $screenshot_parameters = $this->getScreenshotParameters();
        $sessionName = $this->getMink()->getDefaultSessionName();

        if (!array_key_exists($sessionName, $this->getScreenshotConfiguration())) {
            throw new \LogicException(sprintf('The configuration for session \'%s\' is not defined.', $sessionName));
        }
        $screenshot_configuration = $this->getScreenshotConfiguration()[$sessionName];

        foreach ($screenshot_parameters['screenshot_config']['breakpoints'] as $breakpoint_name => $parameters) {
            $this->getSession()->resizeWindow($parameters['width'], $parameters['height']);
            $screenshot = $this->getSession()->getScreenshot();

            //Crop the image according to the settings.
            if (array_key_exists('crop', $screenshot_configuration)) {
                // Initiate Imagick object.
                $actualScreenshot = new \Imagick();
                $actualScreenshot->readImageBlob($screenshot);

                // Get the current size.
                $actualGeometry = $actualScreenshot->getImageGeometry();

                // Calculate new sizes.
                $crop = $screenshot_configuration['crop'];
                $cropWidth = $actualGeometry['width'] - $crop['right'] - $crop['left'];
                $cropHeight = $actualGeometry['height'] - $crop['top'] - $crop['bottom'];

                // Crop the image.
                $actualScreenshot->cropImage($cropWidth, $cropHeight, $crop['left'], $crop['top']);
                $screenshot = $actualScreenshot->getImageBlob();
            }

            // Create the directorys if they do not yet exist.
            $directory = $screenshot_parameters['screenshot_dir'] . '/' . $breakpoint_name . '/';
            $full_name = $directory . $filename;
            if (!file_exists($full_name)) {
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }
                file_put_contents($full_name, $screenshot);
            } else {
                throw new ExpectationException(
                    'Tried to generate ' . $full_name . ' but it already exists.',
                    $this->getSession()
                );
            }
        }
    }
}
