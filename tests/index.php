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

$db->exec('PRAGMA busy_timeout = 5000;');
$db->exec('PRAGMA synchronous = NORMAL;');

(\strcasecmp($db->selectValue('PRAGMA journal_mode;'), 'wal') === 0) or \fail(__LINE__);
(\strcasecmp($db->selectValue('PRAGMA encoding;'), 'utf-8') === 0) or \fail(__LINE__);

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
$v = (float) $db->selectValue('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ]); ($v > 28.31 && $v < 28.33) or \fail(__LINE__);
$v = (float) $db->selectValue('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ]); ($v > -0.01 && $v < 0.01) or \fail(__LINE__);
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
$v = (float) \current($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])); ($v > 28.31 && $v < 28.33) or \fail(__LINE__);
(\count($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) === 1) or \fail(__LINE__);
(\key($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) === 'axial_tilt_deg') or \fail(__LINE__);
$v = (float) \current($db->selectRow('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])); ($v > -0.01 && $v < 0.01) or \fail(__LINE__);
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
$v = (float) \current($res); ($v > 177.29 && $v < 177.31) or \fail(__LINE__);
\next($res);
(\key($res) === 'symbol') or \fail(__LINE__);
(\current($res) === "\xE2\x99\x80") or \fail(__LINE__);

// select > COUNT(*)
(\count($db->select('SELECT COUNT(*) AS cnt FROM planets')[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT COUNT(*) AS cnt FROM planets')[0]) === 'cnt') or \fail(__LINE__);
((int) \current($db->select('SELECT COUNT(*) AS cnt FROM planets')[0]) === 8) or \fail(__LINE__);
(\count($db->select('SELECT COUNT(symbol) AS cnt FROM planets')[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT COUNT(symbol) AS cnt FROM planets')[0]) === 'cnt') or \fail(__LINE__);
((int) \current($db->select('SELECT COUNT(symbol) AS cnt FROM planets')[0]) === 5) or \fail(__LINE__);
(\count($db->select('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])[0]) === 'cnt') or \fail(__LINE__);
((int) \current($db->select('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])[0]) === 0) or \fail(__LINE__);
(\count($db->select('SELECT COUNT(*) AS cnt FROM galaxies')[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT COUNT(*) AS cnt FROM galaxies')[0]) === 'cnt') or \fail(__LINE__);
((int) \current($db->select('SELECT COUNT(*) AS cnt FROM galaxies')[0]) === 0) or \fail(__LINE__);

// select > strings
(\count($db->select('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 'title') or \fail(__LINE__);
(\current($db->select('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 'Saturn') or \fail(__LINE__);
(\count($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === 'symbol') or \fail(__LINE__);
(\current($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === "\xE2\x99\x80") or \fail(__LINE__);
(\count($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])[0]) === 'symbol') or \fail(__LINE__);
(\current($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])[0]) === '') or \fail(__LINE__);
(\count($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === 'symbol') or \fail(__LINE__);
(\current($db->select('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === null) or \fail(__LINE__);

// select > integers
(\count($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 'rings') or \fail(__LINE__);
((int) \current($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0]) === 1) or \fail(__LINE__);
(\count($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === 'rings') or \fail(__LINE__);
((int) \current($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0]) === 0) or \fail(__LINE__);
(\count($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === 'discovery_year') or \fail(__LINE__);
((int) \current($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0]) === 1781) or \fail(__LINE__);
(\count($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])[0]) === 'discovery_year') or \fail(__LINE__);
(\current($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])[0]) === null) or \fail(__LINE__);

// select > floats/doubles
(\count($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])[0]) === 'axial_tilt_deg') or \fail(__LINE__);
$v = (float) \current($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])[0]); ($v > 28.31 && $v < 28.33) or \fail(__LINE__);
(\count($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])[0]) === 'axial_tilt_deg') or \fail(__LINE__);
$v = (float) \current($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])[0]); ($v > -0.01 && $v < 0.01) or \fail(__LINE__);
(\count($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])[0]) === 1) or \fail(__LINE__);
(\key($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])[0]) === 'axial_tilt_deg') or \fail(__LINE__);
(\current($db->select('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])[0]) === null) or \fail(__LINE__);

// select > not found
($db->select('SELECT title FROM planets WHERE title LIKE ?', [ 'X%' ]) === null) or \fail(__LINE__);
($db->select('SELECT rings FROM planets WHERE title LIKE ?', [ 'Y%' ]) === null) or \fail(__LINE__);
($db->select('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Z%' ]) === null) or \fail(__LINE__);

// select > three rows and three columns
$res = $db->select('SELECT title, axial_tilt_deg, symbol FROM planets WHERE title LIKE ? AND title LIKE ? ORDER BY title ASC', [ '%a%', '%s%' ]);
(\count($res) === 3) or \fail(__LINE__);
(\count($res[0]) === 3) or \fail(__LINE__);
(\count($res[1]) === 3) or \fail(__LINE__);
(\count($res[2]) === 3) or \fail(__LINE__);
($res[0]['title'] === 'Mars') or \fail(__LINE__);
($res[0]['axial_tilt_deg'] === null) or \fail(__LINE__);
($res[0]['symbol'] === "\xE2\x99\x82") or \fail(__LINE__);
($res[1]['title'] === 'Saturn') or \fail(__LINE__);
$v = (float) $res[1]['axial_tilt_deg']; ($v > 26.72 && $v < 26.74) or \fail(__LINE__);
($res[1]['symbol'] === null) or \fail(__LINE__);
($res[2]['title'] === 'Uranus') or \fail(__LINE__);
$v = (float) $res[2]['axial_tilt_deg']; ($v > 97.85 && $v < 97.87) or \fail(__LINE__);
($res[2]['symbol'] === null) or \fail(__LINE__);

// selectColumn > COUNT(*)
(\count($db->selectColumn('SELECT COUNT(*) AS cnt FROM planets')) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT COUNT(*) AS cnt FROM planets')[0] === 8) or \fail(__LINE__);
(\count($db->selectColumn('SELECT COUNT(symbol) AS cnt FROM planets')) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT COUNT(symbol) AS cnt FROM planets')[0] === 5) or \fail(__LINE__);
(\count($db->selectColumn('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT COUNT(*) AS cnt FROM planets WHERE title = ?', [ 'Sun' ])[0] === 0) or \fail(__LINE__);
(\count($db->selectColumn('SELECT COUNT(*) AS cnt FROM galaxies')) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT COUNT(*) AS cnt FROM galaxies')[0] === 0) or \fail(__LINE__);

// selectColumn > strings
(\count($db->selectColumn('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT title FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0] === 'Saturn') or \fail(__LINE__);
(\count($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0] === "\xE2\x99\x80") or \fail(__LINE__);
(\count($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ear%' ])[0] === '') or \fail(__LINE__);
(\count($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT symbol FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0] === null) or \fail(__LINE__);

// selectColumn > integers
(\count($db->selectColumn('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT rings FROM planets WHERE title LIKE ?', [ 'Sat%' ])[0] === 1) or \fail(__LINE__);
(\count($db->selectColumn('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT rings FROM planets WHERE title LIKE ?', [ 'Ven%' ])[0] === 0) or \fail(__LINE__);
(\count($db->selectColumn('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])) === 1) or \fail(__LINE__);
((int) $db->selectColumn('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Ura%' ])[0] === 1781) or \fail(__LINE__);
(\count($db->selectColumn('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Jup%' ])[0] === null) or \fail(__LINE__);

// selectColumn > floats/doubles
(\count($db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])) === 1) or \fail(__LINE__);
$v = (float) $db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Nep%' ])[0]; ($v > 28.31 && $v < 28.33) or \fail(__LINE__);
(\count($db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])) === 1) or \fail(__LINE__);
$v = (float) $db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mer%' ])[0]; ($v > -0.01 && $v < 0.01) or \fail(__LINE__);
(\count($db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])) === 1) or \fail(__LINE__);
($db->selectColumn('SELECT axial_tilt_deg FROM planets WHERE title LIKE ?', [ 'Mar%' ])[0] === null) or \fail(__LINE__);

// selectColumn > not found
($db->selectColumn('SELECT title FROM planets WHERE title LIKE ?', [ 'X%' ]) === null) or \fail(__LINE__);
($db->selectColumn('SELECT rings FROM planets WHERE title LIKE ?', [ 'Y%' ]) === null) or \fail(__LINE__);
($db->selectColumn('SELECT discovery_year FROM planets WHERE title LIKE ?', [ 'Z%' ]) === null) or \fail(__LINE__);

// selectColumn > three rows
$res = $db->selectColumn('SELECT title FROM planets WHERE title LIKE ? AND title LIKE ? ORDER BY title ASC', [ '%a%', '%s%' ]);
(\count($res) === 3) or \fail(__LINE__);
($res[0] === 'Mars') or \fail(__LINE__);
($res[1] === 'Saturn') or \fail(__LINE__);
($res[2] === 'Uranus') or \fail(__LINE__);

// 'UNIQUE' constraints
$uniqueIntegrityConstraintViolated = false;
try {
	$db->insert('stuff', [ 'label' => 'f6078d64ed1145f3bd56ea3da3332e5495e223d70f284b87' ]);
}
catch (\Delight\Db\Throwable\UniqueIntegrityConstraintViolationException $e) {
	$uniqueIntegrityConstraintViolated = true;
}
($uniqueIntegrityConstraintViolated === true) or \fail(__LINE__);

// 'NOT NULL' constraints
$notNullIntegrityConstraintViolated = false;
try {
	$db->insert('stuff', [ 'label' => null ]);
}
catch (\Delight\Db\Throwable\NotNullIntegrityConstraintViolationException $e) {
	$notNullIntegrityConstraintViolated = true;
}
($notNullIntegrityConstraintViolated === true) or \fail(__LINE__);

// 'CHECK' constraints
$checkIntegrityConstraintViolated = false;
try {
	$db->insert('stuff', [ 'label' => 'f6078d64ed1145f3bd56ea3da3332e5495e223d70f284b87ba58eca4543a695d4a01e92d92074954a73ba5b222ab34e2' ]);
}
catch (\Delight\Db\Throwable\CheckIntegrityConstraintViolationException $e) {
	$checkIntegrityConstraintViolated = true;
}
($checkIntegrityConstraintViolated === true) or \fail(__LINE__);

// clean up
$db->exec('DELETE FROM stuff');

echo 'ALL TESTS PASSED' . "\n";
