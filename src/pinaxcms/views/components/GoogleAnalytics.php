<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_GoogleAnalytics extends pinax_components_Component
{
	function render_html()
	{
		$siteProp = $this->_application->getSiteProperty();
		if ( isset( $siteProp[ 'analytics' ] ) && !empty( $siteProp[ 'analytics' ] ) && !__Config::get('DEBUG'))
		{
			$code = $siteProp[ 'analytics' ];
			$host = $_SERVER['SERVER_NAME'];
			$output =  <<< EOD
<script async src="https://www.googletagmanager.com/gtag/js?id={$code}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$code}', { 'anonymize_ip': true });
</script>
EOD;
		// add the code in the output buffer
		$this->addOutputCode($output);
		}
	}
}
