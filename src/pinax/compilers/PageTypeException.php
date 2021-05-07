<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_PageTypeException extends Exception
{
    public static function templateDefineNotValid($src)
    {
        return new self('The template define tag must have name attribute, file: '.$src);
    }

	public static function templateDefinitionDontExixts($name)
    {
        return new self('The template definition don\'t exixts: '.$name);
    }

	public static function templateDefinitionRequired($name, $src)
    {
        return new self('The template definition "'.$name.'" is required, file: '.$src);
    }
}
