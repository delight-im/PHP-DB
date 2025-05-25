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

// selectRow > COUNT(*)
(\count($db->selectRow('SELECT COUNT(*) AS cnt FROM planets')) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT COUNT(*) AS cnt FROM planets')) === 'cnt') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT COUNT(*) AS cnt FROM planets')) === 8) or \fail(__LINE__);
(\count($db->selectRow('SELECT COUNT(symbol) AS cnt FROM planets')) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT COUNT(symbol) AS cnt FROM planets')) === 'cnt') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT COUNT(symbol) AS cnt FROM planets')) === 5) or \fail(__LINE__);
(\count($db->selectRow('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])) === 'cnt') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])) === 0) or \fail(__LINE__);
(\count($db->selectRow('SELECT COUNT(*) AS cnt FROM galaxies')) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT COUNT(*) AS cnt FROM galaxies')) === 'cnt') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT COUNT(*) AS cnt FROM galaxies')) === 0) or \fail(__LINE__);

// selectRow > strings
(\count($db->selectRow('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 'title') or \fail(__LINE__);
(\current($db->selectRow('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 'Saturn') or \fail(__LINE__);
(\count($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 'symbol') or \fail(__LINE__);
(\current($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === "\xE2\x99\x80") or \fail(__LINE__);
(\count($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])) === 'symbol') or \fail(__LINE__);
(\current($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])) === '') or \fail(__LINE__);
(\count($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 'symbol') or \fail(__LINE__);
(\current($db->selectRow('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === null) or \fail(__LINE__);

// selectRow > integers
(\count($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 'rings') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 1) or \fail(__LINE__);
(\count($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 'rings') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 0) or \fail(__LINE__);
(\count($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 'discovery_year') or \fail(__LINE__);
((int) \current($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 1781) or \fail(__LINE__);
(\count($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])) === 'discovery_year') or \fail(__LINE__);
(\current($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])) === null) or \fail(__LINE__);

// selectRow > floats/doubles
(\count($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])) === 'axial_tilt_deg') or \fail(__LINE__);
((float) \current($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])) > 28.31) or \fail(__LINE__);
(\count($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) === 'axial_tilt_deg') or \fail(__LINE__);
((float) \current($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) < 0.01) or \fail(__LINE__);
(\count($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])) === 'axial_tilt_deg') or \fail(__LINE__);
(\current($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])) === null) or \fail(__LINE__);

// selectRow > not found
($db->selectRow('SELECT title FROM planets WHERE title LIKE ?', [ 'X%' ]) === null) or \fail(__LINE__);
($db->selectRow('SELECT rings FROM planets WHERE title LIKE ?', [ 'Y%' ]) === null) or \fail(__LINE__);
($db->selectRow('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Z%' ]) === null) or \fail(__LINE__);

// selectRow > three columns
$res = $db->selectRow('SELECT title, axial_tilt_deg, symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ]);
(\count($res) === 3) or \fail(__LINE__);
(\key($res) === 'title') or \fail(__LINE__);
(\current($res) === 'Venus') or \fail(__LINE__);
\next($res);
(\key($res) === 'axial_tilt_deg') or \fail(__LINE__);
((float) \current($res) > 177.29) or \fail(__LINE__);
\next($res);
(\key($res) === 'symbol') or \fail(__LINE__);
(\current($res) === "\xE2\x99\x80") or \fail(__LINE__);

// clean up
$db->exec('DELETE FROM stuff');

echo 'ALL TESTS PASSED' . "\n";
