<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Convert
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public static function formEditObjectToStdObject($data)
    {
        $result = array();
        if (is_object($data)) {
            $objectKeys = array_keys(get_object_vars($data));
            if ($objectKeys) {
                $numItems = 0;
                foreach($objectKeys as $k) {
                    $numItems = max(count($data->{$k}), $numItems);
                }
                for($i=0; $i < $numItems; $i++) {
                    $tempObj = new StdClass;
                    foreach($objectKeys as $k) {
                        $value = $data->{$k}[$i];
                        $tempObj->{$k} = $value;
                    }
                    $result[] = $tempObj;
                }
            }
        }
        return $result;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public static function formEditStdObjectToOldObject($data)
    {
        $result = new StdClass;
        if (!count($data)) {
            return $result;
        }
        $keys = array_keys((array)$data[0]);
        array_reduce($keys, function($carry, $item){
            $carry->{$item} = [];
            return $carry;
        }, $result);

        array_reduce($data, function($carry, $item) use ($keys) {

            array_reduce($keys, function($carry, $subItem) use ($item) {
                $carry->{$subItem}[] = ((array)$item)[$subItem];
                return $carry;
            }, $carry);
            return $carry;
        }, $result);

        return $result;
    }
}
