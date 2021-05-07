<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_exceptions_GlobalException extends Exception
{
    public static function classNotExists($className)
    {
        return new self(sprintf('Class file "%s" does not exists', $className));
    }

    public static function resourceNotFound($file)
    {
        return new self(sprintf('Resource file "%s" was not found', $file));
    }

    public static function missingAttributeInComponent($tagName, $componentId, $attributeName)
    {
        return new self(sprintf('Attribute "%s" in component %s#%s is required', $attributeName, $tagName, $componentId));
    }
}
