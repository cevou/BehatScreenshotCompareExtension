<?php

namespace spec\Cevou\Behat\ScreenshotCompareExtension\ServiceContainer;

use Behat\Behat\Context\Context;
use Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareAwareContext;
use PhpSpec\ObjectBehavior;

class ScreenshotCompareAwareInitializerSpec extends ObjectBehavior
{

    function it_is_a_context_initializer()
    {
        $this->shouldHaveType('Behat\Behat\Context\Initializer\ContextInitializer');
    }

    function it_does_nothing_for_basic_contexts(Context $context)
    {
        $this->initializeContext($context);
    }

    function it_injects_mink_and_parameters_in_mink_aware_contexts(ScreenshotCompareAwareContext $context, $mink)
    {
        $context->setScreenshotCompareParameters(array('screenshot_dir' => 'foo'))->shouldBeCalled();
        $this->initializeContext($context);
    }
}
