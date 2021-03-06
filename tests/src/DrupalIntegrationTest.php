<?php declare(strict_types=1);

namespace PHPStan\Drupal;

final class DrupalIntegrationTest extends AnalyzerTestBase {

    public function testInstallPhp() {
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/core/install.php');
        $this->assertCount(0, $errors->getErrors());
        $this->assertCount(0, $errors->getInternalErrors());
    }

    public function testTestSuiteAutoloading() {
        $paths = [
            __DIR__ . '/../fixtures/drupal/core/tests/TestSuites/FunctionalJavascriptTestSuite.php',
            __DIR__ . '/../fixtures/drupal/core/tests/TestSuites/FunctionalTestSuite.php',
            __DIR__ . '/../fixtures/drupal/core/tests/TestSuites/KernelTestSuite.php',
            __DIR__ . '/../fixtures/drupal/core/tests/TestSuites/UnitTestSuite.php',
        ];
        foreach ($paths as $path) {
            $errors = $this->runAnalyze($path);
            $this->assertCount(1, $errors->getErrors(), $path);
            $this->assertEquals('Unsafe usage of new static().', $errors->getErrors()[0]->getMessage());
            $this->assertCount(0, $errors->getInternalErrors(), print_r($errors->getInternalErrors(), true));
        }

        // Abstract doesn't warn on static constructor.
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/core/tests/TestSuites/TestSuiteBase.php');
        $this->assertCount(0, $errors->getErrors(), $path);
        $this->assertCount(0, $errors->getInternalErrors(), print_r($errors->getInternalErrors(), true));
    }

    public function testDrupalTestInChildSiteContant() {
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/modules/phpstan_fixtures/src/DrupalTestInChildSiteContant.php');
        $this->assertCount(0, $errors->getErrors());
        $this->assertCount(0, $errors->getInternalErrors());
    }

    public function testExtensionReportsError() {
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/modules/phpstan_fixtures/phpstan_fixtures.module');
        $this->assertCount(2, $errors->getErrors(), var_export($errors, true));
        $this->assertCount(0, $errors->getInternalErrors(), var_export($errors, true));

        $errors = $errors->getErrors();
        $error = array_shift($errors);
        $this->assertEquals('If condition is always false.', $error->getMessage());
        $error = array_shift($errors);
        $this->assertEquals('Function phpstan_fixtures_MissingReturnRule() should return string but return statement is missing.', $error->getMessage());
    }

    public function testExtensionTestSuiteAutoloading() {
        $paths = [
            __DIR__ . '/../fixtures/drupal/modules/module_with_tests/tests/src/Unit/ModuleWithTestsTest.php',
            __DIR__ . '/../fixtures/drupal/modules/module_with_tests/tests/src/Traits/ModuleWithTestsTrait.php',
            __DIR__ . '/../fixtures/drupal/modules/module_with_tests/tests/src/TestSite/ModuleWithTestsTestSite.php',
        ];
        foreach ($paths as $path) {
            $errors = $this->runAnalyze($path);
            $this->assertCount(0, $errors->getErrors(), print_r($errors, true));
            $this->assertCount(0, $errors->getInternalErrors(), print_r($errors, true));
        }
    }

    public function testServiceMapping() {
        $errorMessages = [
            '\Drupal calls should be avoided in classes, use dependency injection instead',
            'Call to an undefined method Drupal\Core\Entity\EntityManager::thisMethodDoesNotExist().',
        ];
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/modules/phpstan_fixtures/src/TestServicesMappingExtension.php');
        $this->assertCount(2, $errors->getErrors());
        $this->assertCount(0, $errors->getInternalErrors());
        foreach ($errors->getErrors() as $key => $error) {
            $this->assertEquals($errorMessages[$key], $error->getMessage());
        }
    }

    public function testAppRootPseudoService() {
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/modules/phpstan_fixtures/src/AppRootParameter.php');
        $this->assertCount(0, $errors->getErrors(), var_export($errors, TRUE));
        $this->assertCount(0, $errors->getInternalErrors(), var_export($errors, TRUE));
    }

    public function testThemeSettingsFile() {
        $errors = $this->runAnalyze(__DIR__ . '/../fixtures/drupal/core/modules/system/tests/themes/test_theme_settings/theme-settings.php');
        $this->assertCount(0, $errors->getErrors(), var_export($errors, TRUE));
        $this->assertCount(0, $errors->getInternalErrors(), var_export($errors, TRUE));
    }
}
