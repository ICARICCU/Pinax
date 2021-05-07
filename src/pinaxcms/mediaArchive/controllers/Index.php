<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_Index extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute()
    {
        $this->checkPermissionForBackend();

        if ($this->isMediaPickerPage()) {
            $this->setFiltersForMediaPicker();
        } else {
            $this->setFiltersForMediaArchive();
        }

        if (stripos($this->application->getPageId(), 'tiny') !== false) {
            $this->setComponentsVisibility('buttonsBar', false);
        }
    }


    /**
     * @return void
     */
    private function setFiltersForMediaArchive()
    {
        $filterType = str_replace('all', '', __Request::get('tabs_state', 'allMedia'));
        if (empty($filterType) || $filterType=='mediaarchive') $filterType = 'media';
        $this->setQuery($filterType);
        $this->setOrder(__Request::get('tabs_sort_state', 'media_creationDate'));
    }

    /**
     * @return void
     */
    private function setFiltersForMediaPicker()
    {
        $mediaType = pinax_Request::get('mediaType', '');
        if ($mediaType=='ALL') $mediaType = '';
        $this->setComponentsVisibility('tabs', !$mediaType);
        $this->setQuery($mediaType ? : __Request::get('tabs_state', 'allMedia'));
        $this->setOrder(__Request::get('tabs_sort_state', 'media_creationDate'));
        $this->setComponentsAttribute('btnAdd', 'url', __Link::makeUrl('pinaxcmsMediaArchiveAdd', [], ['mediaType' => $mediaType]));
    }

    /**
     * @return boolean
     */
    private function isMediaPickerPage()
    {
        return stripos($this->application->getPageId(), 'picker') !== false;
    }

    /**
     * @param strin $filterType
     * @return void
     */
    private function setQuery($filterType)
    {
        $filterType = preg_replace('/^all/', '', $filterType);
        $query = 'all'.ucfirst(strtolower(str_replace(',', '_', $filterType)));
        $this->setComponentsAttribute('dp', 'query', $query);
    }

    /**
     * @param strin $filterType
     * @return void
     */
    private function setOrder($order)
    {
        $this->setComponentsAttribute('dp', 'order', $order);
        $this->setComponentsAttribute('dp', 'orderModifier', $order === 'media_title' ? 'ASC' : 'DESC');
    }
}


