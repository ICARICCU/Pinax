<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_dataAccessDoctrine_RecordIterator2tables extends pinax_dataAccessDoctrine_RecordIterator
{
    protected $conditionNumber;
    protected $hasSelect;
    protected $languageSet;

    protected function resetQuery()
    {
        parent::resetQuery();
        $this->languageSet = false;
    }

    public function whereLanguageIs($value)
    {
        $this->qb->andWhere($this->expr->eq($this->ar->getLanguageField(), ':language'))
                 ->setParameter(':language', $value);
        $this->languageSet = true;
        return $this;
    }

    public function allLanguages()
    {
        $this->languageSet = true;
        return $this;
    }

    public function exec()
    {
        if (!$this->languageSet && $this->ar->getLanguageField()) {
            $this->whereLanguageIs($this->ar->getLanguageId());
        }

        parent:: exec();
    }
}
