<?php
trait pinax_oaipmh_core_ResumptionTokenTrait
{

    /**
     * @return pinax_oaipmh_models_VO_ResumptionInfoVO
     */
    private function resumptionInfoOrParams()
    {
        $this->cleanToken();
        $resumptionToken = $this->getParam('resumptionToken', false, pinax_oaipmh_core_ParamsType::TYPE_GENERIC);
        if ($resumptionToken) {
            return $this->readResumptionToken($resumptionToken);
        }

        return pinax_oaipmh_models_VO_ResumptionInfoVO::create([
            'limitStart' => 0,
            'from' => $this->getParam('from', false, pinax_oaipmh_core_ParamsType::TYPE_DATE),
            'until' => $this->getParam('until', false, pinax_oaipmh_core_ParamsType::TYPE_DATE),
            'set' => $this->getParam('set', false, pinax_oaipmh_core_ParamsType::TYPE_GENERIC),
            'metadataSets' => $this->getParam('metadataPrefix', true, pinax_oaipmh_core_ParamsType::TYPE_METADATA_PREFIX),
        ]);
    }

    /**
     * @param string $resumptionToken
     * @return pinax_oaipmh_models_VO_ResumptionInfoVO
     */
    protected function readResumptionToken($resumptionToken)
    {
        $this->cleanToken();
        $fileName =  $this->tokenPath($resumptionToken);
        if (!file_exists($fileName)) {
            throw pinax_oaipmh_core_Exception::badResumptionToken();
        }

        $resumptionInfoVO = unserialize( file_get_contents( $fileName ) );
        $resumptionInfoVO->limitStart = $resumptionInfoVO->limitEnd;
        return $resumptionInfoVO;
    }


    /**
     * @param pinax_oaipmh_models_VO_ResumptionInfoVO $resumptionInfoVO
     * @return string
     */
    protected function createResumptionToken(pinax_oaipmh_models_VO_ResumptionInfoVO $resumptionInfoVO)
    {
        $isLast = $resumptionInfoVO->numRows <= $resumptionInfoVO->limitEnd;
		if ($isLast) {
            return '<resumptionToken completeListSize="'.$resumptionInfoVO->numRows.'" cursor="'.$resumptionInfoVO->limitStart.'"></resumptionToken>';
		}

        $expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+$this->tokenExpirationLength());
        $resumptionToken = $resumptionInfoVO->prefix.'-'.$this->tokenId();
        $fileName = $this->tokenPath($resumptionToken);
        file_put_contents( $fileName, serialize( $resumptionInfoVO ) );
        return '<resumptionToken expirationDate="'.$expirationdatetime.'" completeListSize="'.$resumptionInfoVO->numRows.'" cursor="'.$resumptionInfoVO->limitStart.'">'.$resumptionToken.'</resumptionToken>';
    }

    /**
     * @return void
     */
    protected function cleanToken()
    {
        pinax_helpers_Files::deleteDirectory($this->tokenFolder(), $this->tokenExpirationLength(), true);
    }

    /**
     * @param string $resumptionToken
     * @return string
     */
    protected function tokenPath($resumptionToken)
    {
        return $this->tokenFolder().$resumptionToken;
    }

    /**
     * @return string
     */
    protected function tokenFolder()
    {
        $folder = __Paths::get( 'CACHE' ).'/oai/';
        @mkdir($folder);
        return $folder;
    }

    /**
     * @return integer
     */
    private function tokenExpirationLength()
	{
		return 24*3600;
	}

    /**
     * @return string
     */
	private function tokenId()
	{
		return md5( microtime( true ) );
    }
}
