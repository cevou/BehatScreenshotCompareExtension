<?php

namespace spec\Cevou\Behat\ScreenshotCompareExtension\ServiceContainer;

use PhpSpec\ObjectBehavior;

class ScreenshotCompareExtensionSpec extends ObjectBehavior
{

    function it_is_a_testwork_extension()
    {
        $this->shouldHaveType('Behat\Testwork\ServiceContainer\Extension');
    }

    function it_is_named_screenshot_compare()
    {
        $this->getConfigKey()->shouldReturn('screenshot_compare');
    }
}
