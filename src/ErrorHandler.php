<?php

/*
 * PHP-DB (https://github.com/delight-im/PHP-DB)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\Db;

use PDOException;
use Delight\Db\Throwable\CheckIntegrityConstraintViolationException;
use Delight\Db\Throwable\DatabaseNotFoundError;
use Delight\Db\Throwable\DatabaseNotWritableError;
use Delight\Db\Throwable\Error;
use Delight\Db\Throwable\Exception;
use Delight\Db\Throwable\ForeignKeyIntegrityConstraintViolationException;
use Delight\Db\Throwable\IntegrityConstraintViolationException;
use Delight\Db\Throwable\NoDatabaseSelectedError;
use Delight\Db\Throwable\NotNullIntegrityConstraintViolationException;
use Delight\Db\Throwable\SyntaxError;
use Delight\Db\Throwable\TableNotFoundError;
use Delight\Db\Throwable\UniqueIntegrityConstraintViolationException;
use Delight\Db\Throwable\UnknownColumnError;
use Delight\Db\Throwable\WrongCredentialsError;

/**
 * Handles, processes and re-throws exceptions and errors
 *
 * For more information about possible exceptions and errors, see:
 *
 * https://en.wikipedia.org/wiki/SQLSTATE
 *
 * https://dev.mysql.com/doc/mysql-errors/8.4/en/server-error-reference.html
 */
final class ErrorHandler {

	/**
	 * Handles the specified exception thrown by PDO and tries to throw a more specific exception or error instead
	 *
	 * @param PDOException $e the exception thrown by PDO
	 * @throws Exception
	 * @throws Error
	 */
	public static function rethrow(PDOException $e) {
		// the 2-character class of the error (if any) has the highest priority
		$errorClass = null;
		// the 3-character subclass of the error (if any) has a medium priority
		$errorSubClass = null;
		// the full error code itself has the lowest priority
		$error = null;

		// if an error code is available
		if (!empty($e->getCode())) {
			// remember the error code
			$error = $e->getCode();

			// if the error code is an "SQLSTATE" error
			if (strlen($e->getCode()) === 5) {
				// remember the class as well
				$errorClass = substr($e->getCode(), 0, 2);
				// and remember the subclass
				$errorSubClass = substr($e->getCode(), 2);
			}
		}

		if ($errorClass === '3D') {
			throw new NoDatabaseSelectedError($e->getMessage());
		}
		elseif ($errorClass === '23') {
			// SQLite: 'UNIQUE' integrity constraint violation
			if ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 19 UNIQUE constraint failed') !== false) {
				throw new UniqueIntegrityConstraintViolationException($e->getMessage());
			}
			// MySQL: 'UNIQUE' integrity constraint violation
			elseif ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') !== false) {
				throw new UniqueIntegrityConstraintViolationException($e->getMessage());
			}
			// SQLite: 'NOT NULL' integrity constraint violation
			elseif ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 19 NOT NULL constraint failed') !== false) {
				throw new NotNullIntegrityConstraintViolationException($e->getMessage());
			}
			// MySQL: 'NOT NULL' integrity constraint violation
			elseif ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 1048 Column') !== false) {
				throw new NotNullIntegrityConstraintViolationException($e->getMessage());
			}
			// SQLite: 'CHECK' integrity constraint violation
			elseif ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 19 CHECK constraint failed') !== false) {
				throw new CheckIntegrityConstraintViolationException($e->getMessage());
			}
			// SQLite: 'FOREIGN KEY' integrity constraint violation
			elseif ($errorSubClass === '000' && \stripos($e->getMessage(), 'Integrity constraint violation: 19 FOREIGN KEY constraint failed') !== false) {
				throw new ForeignKeyIntegrityConstraintViolationException($e->getMessage());
			}
			else {
				throw new IntegrityConstraintViolationException($e->getMessage());
			}
		}
		// MySQL: 'CHECK' integrity constraint violation
		elseif ($errorClass === '22' && $errorSubClass === '001' && \stripos($e->getMessage(), 'String data, right truncated: 1406 Data too long for column') !== false) {
			throw new CheckIntegrityConstraintViolationException($e->getMessage());
		}
		elseif ($errorClass === '42') {
			if ($errorSubClass === 'S02') {
				throw new TableNotFoundError($e->getMessage());
			}
			elseif ($errorSubClass === 'S22') {
				throw new UnknownColumnError($e->getMessage());
			}
			else {
				throw new SyntaxError($e->getMessage());
			}
		}
		// SQLite: Table not found
		elseif ($errorClass === 'HY' && $errorSubClass === '000' && \stripos($e->getMessage(), 'General error: 1 no such table') !== false) {
			throw new TableNotFoundError($e->getMessage());
		}
		// SQLite: Column not found
		elseif ($errorClass === 'HY' && $errorSubClass === '000' && \stripos($e->getMessage(), 'General error: 1 no such column') !== false) {
			throw new UnknownColumnError($e->getMessage());
		}
		// SQLite: Syntax error
		elseif ($errorClass === 'HY' && $errorSubClass === '000' && \stripos($e->getMessage(), 'General error: 1') !== false && \stripos($e->getMessage(), 'syntax error') !== false) {
			throw new SyntaxError($e->getMessage());
		}
		// SQLite: Database not writable
		elseif ($errorClass === 'HY' && $errorSubClass === '000' && \stripos($e->getMessage(), 'General error: 8 attempt to write a readonly database') !== false) {
			throw new DatabaseNotWritableError($e->getMessage());
		}
		else {
			if ($error === 1044) {
				throw new WrongCredentialsError($e->getMessage());
			}
			elseif ($error === 1049) {
				throw new DatabaseNotFoundError($e->getMessage());
			}
			else {
				throw new Error($e->getMessage());
			}
		}
	}

}
