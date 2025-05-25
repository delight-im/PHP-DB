<?php

/*
 * PHP-DB (https://github.com/delight-im/PHP-DB)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\Db\Throwable;

/** Exception that is thrown when a 'FOREIGN KEY' integrity constraint is being violated */
class ForeignKeyIntegrityConstraintViolationException extends IntegrityConstraintViolationException {}
