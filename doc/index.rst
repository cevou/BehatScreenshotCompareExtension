Screenshot Compare Extension
============================

The screenshot compare extensions uses existing functionality of Behat and Mink
as well as Gaufrette and ImageMagick to take screenshots of web applications and
compare those to fixture screenshots of the applications to find regressions
that were introduced due to new coding.

The ScreenshotCompareExtensions provides the following functionality:

* Base ``Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext``
  context which provides a step definition to compare screenshots for your contexts
  or subcontexts. Or it could be even used as context on its own.

Installation
------------

This extension requires:

* Behat 3.0+
* MinkExtension 2.0+

Through Composer
~~~~~~~~~~~~~~~~

The easiest way to keep your suite updated is to use `Composer <http://getcomposer.org>`_:

1. Define dependencies in your ``composer.json``:

    .. code-block:: js

        {
            "require-dev": {
                ...

                "cevou/behat-screenshot-compare-extension": "~1.0@dev"
            }
        }

2. Install/update your vendors:

    .. code-block:: bash

        $ composer update cevou/behat-screenshot-compare-extension

3. Activate extension by specifying its class in your ``behat.yml``:

    .. code-block:: yaml

        # behat.yml
        default:
          # ...
          extensions:
            Cevou\Behat\ScreenshotCompareExtension:
              screenshot_dir: %paths.base%/features/screenshots
              sessions:
                default: ~
              screenshot_config:
                breakpoints:
                  desktop:
                    width: 1169
                    height: 500
                  tablet:
                    width: 960
                    height: 500
                  phone:
                    width: 560
                    height: 500
              adapters:
                default:
                  local:
                    directory: %paths.base%/error_screenshots

Usage
-----

After installing extension, there would be 3 usage options available for you:
-
1. Extending ``Cevou\Behat\ScreenshotCompareExtension\Context\RawScreenshotCompareContext`` in your feature suite.
   This will give you ability to use the compare screenshot functionality in your context. Just call
   ``compareScreenshot($sessionName, $fileName)``.

   ``RawScreenshotCompareContext`` doesn't provide any hooks or definitions, so you can inherit from it
   in as many contexts as you want - you'll never get ``RedundantStepException``.

2. Extending ``Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext``
   with one of your contexts. As this context provides step definitions and hooks, you can
   use it **only once** inside your feature context tree.

    .. code-block:: php

        <?php

        use Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext;

        class FeatureContext extends ScreenshotCompareContext
        {

        }

    .. warning::

        Keep in mind, that you can not have multiple step definitions with same regex.
        It will cause ``RedundantException``. So, you can inherit from ``ScreenshotCompareContext``
        only with one of your context/subcontext classes.

3. Adding ``Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext`` as context in
   your suite. Exactly like previous option, but gives you ability to keep your main context
   class clean.

    .. code-block:: yaml

        default:
          suites:
            my_suite:
              contexts:
                - FeatureContext
                - Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext

    .. note::

        Keep in mind, that you can not have multiple step definitions with same regex.
        It will cause ``RedundantException``. So, you can inherit from ``MinkContext``
        only with one of your context/subcontext classes.


Configuration
-------------

ScreenshotCompareExtension comes with flexible configuration system, that gives you
ability to configure Gaufrette inside Behat to fulfil all your needs.

Adapters
--------

You can register as many Gaufrette adapters as you want. You will need to choose one
adapter you want to use for a specific session.

.. code-block:: yaml

    default:
        extensions:
            Cevou\Behat\ScreenshotCompareExtension:
                sessions:
                    default:
                        adapter: 'first_adapter'
                adapters:
                    first_adapter:
                        local: ~
                    second_adapter:
                        safe_local: ~
                    third_adapter:
                        ftp: ~

You need to specify which adapter should be used in your tests using the adapter property. By default the adapter called
``default`` is used.

Adapters
~~~~~~~~

Basically ScreenshotCompareExtension can work with all Gaufrette adapters. Currently
there are three adapters implemented.

* ``LocalAdapter`` - Saves the files on the local file system. In order to use
  it, modify your ``behat.yml`` profile:

    .. code-block:: yaml

        default:
            extensions:
                Cevou\Behat\ScreenshotCompareExtension:
                    adapter: 'default'
                    adapters:
                        default:
                            local:
                                directory: %paths.base%/error_screenshots
                                create: true

* ``SafeLocalAdapter`` - Saves the files on the local file system. In order to use
  it, modify your ``behat.yml`` profile:

    .. code-block:: yaml

        default:
            extensions:
                Cevou\Behat\ScreenshotCompareExtension:
                    adapter: 'default'
                    adapters:
                        default:
                            safe_local:
                                directory: %paths.base%/error_screenshots
                                create: true

* ``FtpAdapter`` - Saves the files via FTP to an FTP server. In order to use
  it, modify your ``behat.yml`` profile:

    .. code-block:: yaml

        default:
            extensions:
                Cevou\Behat\ScreenshotCompareExtension:
                    adapter: 'default'
                    adapters:
                        default:
                            ftp:
                                directory: error_screenshots
                                host: ftp.server.com


Additional Parameters
~~~~~~~~~~~~~~~~~~~~~

There's other useful parameters, that you can use to configure your suite:

* ``screenshot_dir`` - the directory where the extension will look for the fixture screenshots
* ``session.crop.(left|top|right|bottom)`` - you can crop the image that is returned from the browser (for example to remove a
browser header which is part of the screenshot)
