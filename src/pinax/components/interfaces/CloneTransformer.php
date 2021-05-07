<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_components_interfaces_CloneTransformer
{
    /**
     * @param string $output
     * @return string
     */
    public function transformRender($output);

    /**
     * @param mixed
     * @return mixed
     */
    public function transformResult($result);

}
