<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinaxcms_speakingUrl_IUrlResolver
{
    public function compileRouting($ar);

    /**
     * @param string $term
     * @param string $id
     * @param string $protocol
     * @param $filter
     * @return array
     */
    public function searchDocumentsByTerm($term, $id, $protocol='', $filter=[]);
    public function makeUrl($id);
    public function makeUrlFromRequest();
    public function makeLink($id);
}
