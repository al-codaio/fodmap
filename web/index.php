<?php

require('../vendor/autoload.php');
$coda = new CodaPHP\CodaPHP('75764dec-d6fc-4a72-9e56-8efe8be59301');
$docID = 'BvPGe-8_sj';
$tableID = 'grid-r5hUgXQ8WA';

$table = $coda->getTable($docID, $tableID);
// $rows = $coda->listRows($docID, $tableID, ['query' => array('Food Group' => 'Grains/Starches')]);
$rows = $coda->listRows($docID, $tableID, ['limit' => 25]);

// echo "<pre>";
// echo json_encode($rows, JSON_PRETTY_PRINT);
// echo "</pre>";

// var_dump(json_encode($result, JSON_PRETTY_PRINT));


$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig' array(
  	'rows' => $rows;
  ));
});

$app->run();
