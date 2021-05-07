<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait pinax_dataAccessRepository_EntityFromRequestTrait
{
    use pinax_dataAccessRepository_EntityBuilderTrait;

    public static function fromRequest($request)
    {
        return self::createEntity(get_class(), (array)$request);
    }
}
