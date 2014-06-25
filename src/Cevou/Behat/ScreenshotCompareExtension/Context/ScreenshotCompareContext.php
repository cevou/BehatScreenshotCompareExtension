<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Gaufrette\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ScreenshotCompareContext extends RawMinkContext implements ScreenshotCompareAwareContext
{
    /** @var  Filesystem */
    private $screenshotCompareFilesystem;
    private $screenshotCompareParameters;

    /**
     * Checks if the screenshot equals to a defined screen
     *
     * @Then /^the screenshot should be equal to "(?P<fileName>[^"]+)"$/
     */
    public function assertScreenshotCompare($fileName)
    {
        $screenshotDir = $this->screenshotCompareParameters['screenshot_dir'];
        $compareFile = $screenshotDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($compareFile)) {
            throw new FileNotFoundException(null, 0, null, $compareFile);
        }

        $actualScreenshot = new \Imagick();
        $actualScreenshot->readImageBlob($this->getSession()->getScreenshot());
        $compareScreenshot = new \Imagick($compareFile);

        $actualGeometry = $actualScreenshot->getImageGeometry();
        $compareGeometry = $compareScreenshot->getImageGeometry();
        if ($actualGeometry !== $compareGeometry) {
            throw new \ImagickException(sprintf("Screenshots don't have an equal geometry. Should be %sx%s but is %sx%s", $compareGeometry['width'], $compareGeometry['height'], $actualGeometry['width'], $actualGeometry['height']));
        }

        $result = $actualScreenshot->compareImages($compareScreenshot, \Imagick::METRIC_ROOTMEANSQUAREDERROR);

        if ($result[1] > 0) {
            $diffFileName = sprintf('%s_%s.%s', $this->getMinkParameter('browser_name'), date('d-m-y-H-i-s'), 'png');

            /** @var \Imagick $diffScreenshot */
            $diffScreenshot = $result[0];
            $diffScreenshot->setImageFormat("png");
            $this->screenshotCompareFilesystem->write($diffFileName, $diffScreenshot);

            throw new \ImagickException(sprintf("Files are not equal. Diff saved to %s", $diffFileName));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setScreenshotCompareFilesystem(Filesystem $filesystem)
    {
        $this->screenshotCompareFilesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function setScreenshotCompareParameters(array $parameters)
    {
        $this->screenshotCompareParameters = $parameters;
    }
}