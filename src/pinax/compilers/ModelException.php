<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_compilers_ModelException extends Exception
{
    public static function missingTableName($fileName)
    {
        return new self('Model '.$fileName.' without tablename attribute.');
    }

    public static function queryWithoutName($fileName)
    {
        return new self('Model '.$fileName.' with query define without name.');
    }

    public static function scriptParentError($fileName)
    {
        return new self('Model '.$fileName.' Script tag with wrong parent.');
    }
}
