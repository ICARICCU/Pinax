<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_dataAccessDoctrine_testing_ModelFactory
{
    /**
     * @param Faker\Generator $faker
     * @return array
     */
    abstract public function factory(Faker\Generator $faker);

    /**
     * @param pinax_dataAccessDoctrine_AbstractActiveRecord $model
     * @return void
     */
    abstract public function postInsert($model);

    /**
     * @param integer $number
     * @param array $values
     * @return array|pinax_dataAccessDoctrine_AbstractActiveRecord
     */
    public static function create($number = 1, array $values = [])
    {
        $faker = \Faker\Factory::create('it_IT');
        $factoryClassName = get_called_class();
        $self = new $factoryClassName;
        $modelName = preg_replace('/Factory$/', '', $factoryClassName);

        $models = [];
        for($i=0; $i<$number; $i++) {
            $model = pinax_ObjectFactory::createModel($modelName);
            $model->loadFromArray(array_merge($self->factory($faker), $values));
            $model->save();
            $models[] = $model;

            $self->postInsert($model);
        }

        return count($models) > 1 ? $models : $models[0];
    }
}
