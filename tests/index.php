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

// selectValue > COUNT(*)
((int) $db->selectValue('SELECT COUNT(*) FROM planets') === 8) or \fail(__LINE__);
((int) $db->selectValue('SELECT COUNT(symbol) FROM planets') === 5) or \fail(__LINE__);
((int) $db->selectValue('SELECT COUNT(*) FROM planets WHERE title = ?', [ 'Sun' ]) === 0) or \fail(__LINE__);
((int) $db->selectValue('SELECT COUNT(*) FROM galaxies') === 0) or \fail(__LINE__);

// selectValue > strings
($db->selectValue('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ]) === 'Saturn') or \fail(__LINE__);
($db->selectValue('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ]) === "\xE2\x99\x80") or \fail(__LINE__);
($db->selectValue('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ]) === '') or \fail(__LINE__);
($db->selectValue('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ]) === null) or \fail(__LINE__);

// selectValue > integers
((int) $db->selectValue('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ]) === 1) or \fail(__LINE__);
((int) $db->selectValue('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ]) === 0) or \fail(__LINE__);
((int) $db->selectValue('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ]) === 1781) or \fail(__LINE__);
($db->selectValue('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ]) === null) or \fail(__LINE__);

// selectValue > floats/doubles
((float) $db->selectValue('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ]) > 28.31) or \fail(__LINE__);
((float) $db->selectValue('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ]) < 0.01) or \fail(__LINE__);
($db->selectValue('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ]) === null) or \fail(__LINE__);

// selectValue > not found
($db->selectValue('SELECT title FROM planets WHERE title LIKE ?', [ 'X%' ]) === null) or \fail(__LINE__);
($db->selectValue('SELECT rings FROM planets WHERE title LIKE ?', [ 'Y%' ]) === null) or \fail(__LINE__);
($db->selectValue('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Z%' ]) === null) or \fail(__LINE__);

// clean up
$db->exec('DELETE FROM stuff');

echo 'ALL TESTS PASSED' . "\n";
