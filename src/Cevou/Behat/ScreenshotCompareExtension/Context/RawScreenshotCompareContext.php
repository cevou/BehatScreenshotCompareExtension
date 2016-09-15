<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Gaufrette\Filesystem as GaufretteFilesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class RawScreenshotCompareContext extends RawMinkContext implements ScreenshotCompareAwareContext
{
    private $screenshotCompareConfigurations;
    private $screenshotCompareParameters;

    /**
     * {@inheritdoc}
     */
    public function setScreenshotCompareConfigurations(array $configurations)
    {
        $this->screenshotCompareConfigurations = $configurations;
    }

    /**
     * {@inheritdoc}
     */
    public function setScreenshotCompareParameters(array $parameters)
    {
        $this->screenshotCompareParameters = $parameters;
    }

    public function getScreenshotParameters()
    {
        return $this->screenshotCompareParameters;
    }

    public function getScreenshotConfiguration()
    {
        return $this->screenshotCompareConfigurations;
    }

    /**
     * @param $sessionName
     * @param $fileName
     *
     * @throws \LogicException
     * @throws \ImagickException
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function compareScreenshot($sessionName, $fileName)
    {
        // Get the current session and config.
        $this->assertSession($sessionName);
        $session = $this->getSession($sessionName);

        if (!array_key_exists($sessionName, $this->getScreenshotConfiguration())) {
            throw new \LogicException(sprintf('The configuration for session \'%s\' is not defined.', $sessionName));
        }
        $screenshotParameters = $this->getScreenshotParameters();

        $configuration = $this->getScreenshotConfiguration()[$sessionName];

        $sourceFilesystem = new SymfonyFilesystem();

        // Iterate over the breakpoints and test the screenshots.
        foreach ($screenshotParameters['screenshot_config']['breakpoints'] as $breakpoint_name => $parameters) {
            $full_filename = $screenshotParameters['screenshot_dir'] .
                DIRECTORY_SEPARATOR . $breakpoint_name .
                DIRECTORY_SEPARATOR . $fileName;

            if (!$sourceFilesystem->exists($full_filename)) {
                throw new FileNotFoundException(null, 0, null, $full_filename);
            }

            $actualScreenshot = new \Imagick();
            $session->resizeWindow($parameters['width'], $parameters['height']);
            $actualScreenshot->readImageBlob($session->getScreenshot());
            $actualGeometry = $actualScreenshot->getImageGeometry();

            //Crop the image according to the settings
            if (array_key_exists('crop', $configuration)) {
                $crop = $configuration['crop'];
                $cropWidth = $actualGeometry['width'] - $crop['right'] - $crop['left'];
                $cropHeight = $actualGeometry['height'] - $crop['top'] - $crop['bottom'];
                $actualScreenshot->cropImage($cropWidth, $cropHeight, $crop['left'], $crop['top']);

                //Refresh geomerty information
                $actualGeometry = $actualScreenshot->getImageGeometry();
            }

            $compareScreenshot = new \Imagick($full_filename);
            $compareGeometry = $compareScreenshot->getImageGeometry();

            //ImageMagick can only compare files which have the same size.
            if ($actualGeometry !== $compareGeometry) {
                throw new \ImagickException(sprintf(
                    "Screenshots don't have an equal geometry. Should be %sx%s but is %sx%s",
                    $compareGeometry['width'],
                    $compareGeometry['height'],
                    $actualGeometry['width'],
                    $actualGeometry['height']
                ));
            }

            $result = $actualScreenshot->compareImages($compareScreenshot, \Imagick::METRIC_ROOTMEANSQUAREDERROR);

            if ($result[1] > 0) {
                /** @var GaufretteFilesystem $targetFilesystem */
                $targetFilesystem = $configuration['adapter'];

                $diffFileName = sprintf(
                    '%s_%s.%s',
                    $this->getMinkParameter('browser_name'),
                    date('d-m-y-H-i-s'),
                    'png'
                );

                /** @var \Imagick $diffScreenshot */
                $diffScreenshot = $result[0];
                $diffScreenshot->setImageFormat("png");
                $targetFilesystem->write($diffFileName, $diffScreenshot);

                throw new \ImagickException(sprintf("Files are not equal. Diff saved to %s", $diffFileName));
            }
        }
    }
}
