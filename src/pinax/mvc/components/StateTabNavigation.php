<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_mvc_components_StateTabNavigation extends pinax_components_Component
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('addWrapDiv',        false,  false,          COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('cssClass',          false, __Config::get('pinax.tab.cssClass'),  COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassCurrent',   false, __Config::get('pinax.tab.cssClassCurrent'), COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassItem',          false, __Config::get('pinax.tab.cssClassItem'), COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassLink',          false, __Config::get('pinax.tab.cssClassLink'),  COMPONENT_TYPE_STRING);
        $this->defineAttribute('forceLink',         false,  true,           COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('addQueryString',    false,  false,          COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('routeUrl',          false,  'moduleAction', COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function render_html()
    {
        $view = $this->_parent;
        $cssClass = trim($this->getAttribute('cssClass'));
        $cssClassItem = trim($this->getAttribute('cssClassItem'));
        $cssClassLink = trim($this->getAttribute('cssClassLink'));
        $cssClassCurrent = trim($this->getAttribute('cssClassCurrent'));
        $queryString = $this->getAttribute('addQueryString') ? '?'.http_build_query($_GET) : '';
        $forceLink = $this->getAttribute('forceLink');
        $output = '';

        foreach ( $view->childComponents  as $c ) {
            if ( !is_a( $c, 'pinax_mvc_components_State' ) ) {
                continue;
            }

            $id = $c->getId();
            $label = $c->getAttribute('label');
            $draw = $c->getAttribute('draw');
            $aclResult = $this->evalueteAcl($c->getAttribute('acl'));
            if ( !$draw || empty($label) || !$aclResult) {
                continue;
            }

            $finalCssClassItem = $cssClassItem.($c->isCurrentState() ? ' '.$cssClassCurrent : '');

            if (!empty($finalCssClassItem) && !$forceLink) {
                $liContent = $label;
            } else {
                $url = $c->getAttribute('url');

                $url = $url ?
                        __Link::makeUrl( $url) :
                        __Link::makeUrl( $this->getAttribute('routeUrl'), ['title' => $label, 'action' => $c->getStateAction()]);

                $liContent = pinax_helpers_Html::renderTag('a', [
                                    'title' => $label,
                                    'id' => $id,
                                    'href' => $url.$queryString,
                                    'class' => $cssClassLink.($c->isCurrentState() ? ' '.$cssClassCurrent : '')],
                                    true,
                                    $label);
            }

            $output .= pinax_helpers_Html::renderTag('li',
                            [ 'class' => $finalCssClassItem ],
                            true,
                            $liContent);
        }

        if ($this->getAttribute('addWrapDiv')) {
            $output  = '<div id="'.$this->getId().'"><ul '.($cssClass ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>'.
                        $output.
                        '</ul></div>';
        } else {
            $output  = '<ul id="'.$this->getId().'"'.($cssClass ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>'.
                        $output.
                        '</ul>';
        }
        $this->addOutputCode($output);
    }
}
