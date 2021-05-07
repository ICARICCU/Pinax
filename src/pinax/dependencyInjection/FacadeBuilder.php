<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dependencyInjection_FacadeBuilder
{
    public static function buildFacade($facedeName, $service)
    {
        $classDef = <<<EOD
class $facedeName {
    protected static \$object = null;

    public function __construct(\$object)
    {
        self::\$object = \$object;
    }
    public static function __callStatic(\$name, array \$arguments = [])
    {
        return call_user_func_array([self::\$object, \$name], \$arguments);
    }
}
EOD;

        eval($classDef);
        new $facedeName($service);
    }
}


