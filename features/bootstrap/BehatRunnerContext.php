<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class BehatRunnerContext.
 */
class BehatRunnerContext implements Context
{

    /**
     * @var string|null
     */
    private $workingDir;

    /**
     * @var string|null
     */
    private $phpBin;

    /**
     * @var Process|null
     */
    private $process;

    /**
     * @BeforeScenario
     */
    public function bootstrap()
    {
        $this->workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('BehatScreenshotCompareExtension_'));
        $this->getFilesystem()->mkdir($this->workingDir, 0777);

        $this->phpBin = $this->findPhpBinary();
        $this->process = new Process(null);
    }

    /**
     * @AfterScenario
     */
    public function removeWorkDir()
    {
        $this->getFilesystem()->remove($this->workingDir);
    }

    /**
     * @Given /^I configured the screenshot compare extension$/
     *
     * @todo: enforce browser_name: 'phantomjs'
     */
    public function iConfiguredTheScreenshotCompareExtension()
    {
        $config = <<<CONFIG
default:
  suites:
    default:
      contexts:
        - Cevou\Behat\ScreenshotCompareExtension\Context\ScreenshotCompareContext
        - Behat\MinkExtension\Context\MinkContext
  extensions:
    Cevou\Behat\ScreenshotCompareExtension:
      sessions:
        default: ~
        crop:
          crop:
            left: 8
            top: 8
            bottom: 92
            right: 192
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
            directory: '%paths.base%/compared_screenshots'
    Behat\MinkExtension:
      sessions:
        default:
          selenium2: ~
        crop:
          selenium2: ~
      base_url: http://localhost:8000
CONFIG;

        $content = new PyStringNode(explode("\n", $config), 0);
        $this->getFilesystem()->dumpFile($this->workingDir . '/behat.yml', $content->getRaw());
    }

    /**
     * @codingStandardsIgnoreLine
     * @Given /^a feature file that opens the url "(?P<url>[^"]*)" and compares it to "(?P<file>[^"]*)" with tag "(?P<tag>[^"]*)"$/
     */
    public function aFeatureFileWhichOpensUrlAndComparesToScreenshotWithTag($url, $file, $tag)
    {
        $feature = <<<FEATURE
Feature: Take a screenshot of an application and compare it with a previous taken screenshot

  $tag
  Scenario: Compare correct page with screenshot
    Given I am on "$url"
    Then the screenshot should be equal to "$file"
FEATURE;

        $this->copyScreenshotAndFeature($feature);
    }

    /**
     * @codingStandardsIgnoreLine
     * @Given /^a feature file that opens the url "(?P<url>[^"]*)" and generates it to "(?P<file>[^"]*)"$/
     */
    public function aFeatureFileWhichOpensUrlAndGeneratesToScreenshotWithTag($url, $file)
    {
        $feature = <<<FEATURE
Feature: Take a screenshot of an application and compare it with a previous taken screenshot

  Scenario: Compare correct page with screenshot
    Given I am on "$url"
    Then I generate the screenshot "$file"
    Then the screenshot should be equal to "$file"
FEATURE;

        $this->copyScreenshotAndFeature($feature);
    }

    /**
     * @Given /^a feature file that opens the url "(?P<url>[^"]*)" and compares it to "(?P<file>[^"]*)"$/
     */
    public function aFeatureFileWhichOpensUrlAndComparesToScreenshot($url, $file)
    {
        $feature = <<<FEATURE
Feature: Take a screenshot of an application and compare it with a previous taken screenshot

  Scenario: Compare correct page with screenshot
    Given I am on "$url"
    Then the screenshot should be equal to "$file"
FEATURE;

        $this->copyScreenshotAndFeature($feature);
    }

    /**
     * @When /^I run behat$/
     */
    public function iRunBehat()
    {
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(
            sprintf(
                '%s %s %s %s',
                $this->phpBin,
                escapeshellarg(BEHAT_BIN_PATH),
                strtr('--format-settings=\'{"timer": false}\'', [
                    '\'' => '"',
                    '"' => '\"',
                ]),
                '--format=progress'
            )
        );
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should pass$/
     */
    public function itShouldPass()
    {
        try {
            expect($this->process->getExitCode())->toBe(0);
        } catch (\Exception $e) {
            echo $this->getOutput();

            throw $e;
        }
    }

    /**
     * @Then /^it should fail$/
     */
    public function itShouldFail()
    {
        try {
            expect($this->process->getExitCode())->notToBe(0);
        } catch (\Exception $e) {
            echo $this->getOutput();

            throw $e;
        }
    }

    /**
     * @return string
     */
    private function getOutput()
    {
        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        return new Filesystem();
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    private function findPhpBinary()
    {
        $phpFinder = new PhpExecutableFinder();

        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $php;
    }

    /**
     * Copys the screenshot and feature.
     *
     * @param string $feature
     *   The feature to use, as a string.
     */
    private function copyScreenshotAndFeature($feature)
    {
        $this->getFilesystem()
            ->mirror(__DIR__ . '/../screenshots', $this->workingDir . '/features/screenshots');
        $content = new PyStringNode(explode("\n", $feature), 0);
        $this->getFilesystem()
            ->dumpFile($this->workingDir . '/features/compare_screenshot.feature', $content->getRaw());
    }
}
