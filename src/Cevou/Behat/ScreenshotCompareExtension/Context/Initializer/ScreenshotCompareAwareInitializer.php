<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareAwareContext;
use Gaufrette\Filesystem;

class ScreenshotCompareAwareInitializer implements ContextInitializer
{

    private $filesystem;
    private $parameters;

    /**
     * Initializes initializer.
     *
     * @param Filesystem $filesystem
     * @param array $parameters
     */
    public function __construct(Filesystem $filesystem, array $parameters)
    {
        $this->filesystem = $filesystem;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof ScreenshotCompareAwareContext) {
            return;
        }

        $context->setScreenshotCompareFilesystem($this->filesystem);
        $context->setScreenshotCompareParameters($this->parameters);
    }

}