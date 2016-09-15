<?php

namespace spec\Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter;

use PhpSpec\ObjectBehavior;

class LocalAdapterFactorySpec extends ObjectBehavior
{

    function it_is_a_adapter_factory()
    {
        $this->shouldHaveType('Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter\AdapterFactory');
    }

    function it_is_named_local()
    {
        $this->getKey()->shouldReturn('local');
    }
}
