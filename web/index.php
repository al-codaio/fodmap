<?php

// Enable error reporting but suppress deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);

require('../vendor/autoload.php');
$app = new Silex\Application();
$app['debug'] = false;  // Set debug mode to false

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
  
  try {
    $coda = new CodaPHP\CodaPHP('b5ff558d-e7fb-41bc-be9c-726ee84e9691');
    $docID = 'BvPGe-8_sj';
    $tableID = 'grid-r5hUgXQ8WA';
    
    // Test if we can get the table
    try {
      $table = $coda->getTable($docID, $tableID);
    } catch (Exception $e) {
      error_log("Table error: " . $e->getMessage());
      throw new Exception("Could not fetch table. Please verify your document ID and table ID.");
    }
    
    // Get rows with error checking
    try {
      $response = $coda->listRows($docID, $tableID);
    } catch (Exception $e) {
      error_log("Rows error: " . $e->getMessage());
      throw new Exception("Could not fetch rows. Please verify your API token has access to this document.");
    }
    
    if (!isset($response['items']) || empty($response['items'])) {
      throw new Exception("No rows found in the table. The table might be empty.");
    }
    
    $rows = $response['items'];
    $sorted_rows = [];
    
    // Debug the raw response
    error_log("Raw response: " . json_encode($response));
    
    foreach ($rows as $row) {
      if (isset($row['index']) && $row['index'] > count($rows) - 20) {
        array_push($sorted_rows, $row);
      }
    }

    //Sort by index desc order
    usort($sorted_rows, function ($a, $b) {
      return $b['index'] <=> $a['index'];
    });

    // Debug output
    error_log("Number of rows fetched: " . count($rows));
    error_log("Number of sorted rows: " . count($sorted_rows));
    error_log("Sample data: " . json_encode(array_slice($rows, 0, 1)));

    return $app['twig']->render('index.twig', array(
      'rows' => $sorted_rows
    ));
    
  } catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    return "Error fetching data: " . $e->getMessage() . "\n\nPlease verify:\n1. Your API token is valid\n2. The document ID is correct\n3. The table ID is correct\n4. Your API token has access to the document";
  }
});

$app->run();
