<?php
/**
 * Codeception CLI
 */

require_once dirname(__FILE__).'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application('Codeception', Codeception\Codecept::VERSION);
$app->add(new Codeception\Command\Build('build'));
$app->add(new Codeception\Command\Run('run'));
$app->add(new Codeception\Command\Console('console'));
$app->add(new Codeception\Command\Bootstrap('bootstrap'));
$app->add(new Codeception\Command\GenerateCept('generate:cept'));
$app->add(new Codeception\Command\GenerateCest('generate:cest'));
$app->add(new Codeception\Command\GenerateTest('generate:test'));
$app->add(new Codeception\Command\GeneratePhpUnit('generate:phpunit'));
$app->add(new Codeception\Command\GenerateSuite('generate:suite'));
$app->add(new Codeception\Command\GenerateHelper('generate:helper'));
$app->add(new Codeception\Command\GenerateScenarios('generate:scenarios'));
$app->add(new Codeception\Command\Clean('clean'));
$app->add(new Codeception\Command\GenerateGroup('generate:group'));
$app->add(new Codeception\Command\GeneratePageObject('generate:pageobject'));
$app->add(new Codeception\Command\GenerateStepObject('generate:stepobject'));
$app->run(); 