<?php

namespace Cevou\Behat\ScreenshotCompareExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareAwareContext;

class ScreenshotCompareAwareInitializer implements ContextInitializer
{

    private $configurations;
    private $parameters;

    /**
     * Initializes initializer.
     *
     * @param array $configurations
     * @param array $parameters
     */
    public function __construct(array $configurations, array $parameters)
    {
        $this->configurations = $configurations;
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

        $context->setScreenshotCompareConfigurations($this->configurations);
        $context->setScreenshotCompareParameters($this->parameters);
    }

}