<?php

/*
 * PHP-DB (https://github.com/delight-im/PHP-DB)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/*
 * WARNING:
 *
 * Do *not* use these files from the `tests` directory as the foundation
 * for the usage of this library in your own code. Instead, please follow
 * the `README.md` file in the root directory of this project.
 */

// enable error reporting
\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

\header('Content-type: text/plain; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';

function fail($lineNumber) {
	// clean up
	$GLOBALS['db']->exec('DELETE FROM stuff');

	exit('Error on line ' . $lineNumber);
}

$db = \Delight\Db\PdoDatabase::fromPdo(
	new \PDO('sqlite:' . __DIR__ . '/../data/tests/main.sqlite')
);

// throw off 'PDOStatement#rowCount' method where used with 'SELECT' statements
$db->insert('stuff', [ 'label' => 'f6078d64ed1145f3bd56ea3da3332e5495e223d70f284b87' ]);

// clean up
$db->exec('DELETE FROM stuff');

echo 'ALL TESTS PASSED' . "\n";
