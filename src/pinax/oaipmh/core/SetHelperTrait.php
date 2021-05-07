<?php
trait pinax_oaipmh_core_SetHelperTrait
{
	/**
     * Non si può usare percché non itera tra array di oggetti
     * new RecursiveIteratorIterator(new RecursiveArrayIterator());
     *
     * @param array $array
     * @return array
     */
    private function flatten($array) {
        if (!is_array($array)) {
            return array($array);
        }

        $result = array();
        foreach ($array as $value) {
            $result = array_merge($result, $this->flatten($value));
        }

        return $result;
	}

	/**
     * @param string[] $sets
     * @return pinax_oaipmh_core_SetInterface[]
     */
    protected function modelsMap($sets)
    {
        $modelsMap = [];
        foreach($sets as $setClass) {
    	    $setInfo = $setClass->getSetInfo();
    	    //$model = $setClass->getModelName();
            $modelsMap[$setInfo['setSpec']] = $setClass;
        }

        return $modelsMap;
	}

    /**
     * @param pinax_oaipmh_core_SetInterface[] $sets
     * @param string $setSpec
     * @return pinax_oaipmh_core_SetInterface
     */
    private function getSet($sets, $setSpec)
    {
        foreach($sets as $setClass) {
            $setInfo = $setClass->getSetInfo();
            if ($setInfo['setSpec']==$setSpec) {
                return $setClass;
            }
        }

        throw pinax_oaipmh_core_Exception::cannotDisseminateFormat($setSpec);
    }
}
