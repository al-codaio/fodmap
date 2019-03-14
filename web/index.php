<?php

require('../vendor/autoload.php');
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
  
  $coda = new CodaPHP\CodaPHP('75764dec-d6fc-4a72-9e56-8efe8be59301');
  $docID = 'BvPGe-8_sj';
  $tableID = 'grid-r5hUgXQ8WA';
  $table = $coda->getTable($docID, $tableID);
  
  //list rows with query not working below 
  // $rows = $coda->listRows($docID, $tableID, ['query' => array('Food Group' => 'Grains/Starches')]);
  
  //Return 20 last entered rows by index
  $rows = $coda->listRows($docID, $tableID)['items'];
  $sorted_rows = [];
  $sorted_rows_index = [];
  foreach ($rows as $row) {
  	if ($row['index'] > count($rows) - 20) {
  	  array_push($sorted_rows, $row);
  	}
  }

  //Sort by index desc order
  usort($sorted_rows, function ($a, $b) {
    return $b['index'] <=> $a['index'];
  });

  //See data in pretty print
  // echo "<pre>";
  // echo json_encode($sorted_rows, JSON_PRETTY_PRINT);
  // echo "</pre>";

  return $app['twig']->render('index.twig', array(
  	'rows' => $sorted_rows
  ));
});

$app->run();
