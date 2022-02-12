<?php

namespace Google\AdsApi\AdManager\v201905;


/**
 * This file was generated from WSDL. DO NOT EDIT.
 */
class updateProposalsResponse
{

    /**
     * @var \Google\AdsApi\AdManager\v201905\Proposal[] $rval
     */
    protected $rval = null;

    /**
     * @param \Google\AdsApi\AdManager\v201905\Proposal[] $rval
     */
    public function __construct(array $rval = null)
    {
      $this->rval = $rval;
    }

    /**
     * @return \Google\AdsApi\AdManager\v201905\Proposal[]
     */
    public function getRval()
    {
      return $this->rval;
    }

    /**
     * @param \Google\AdsApi\AdManager\v201905\Proposal[]|null $rval
     * @return \Google\AdsApi\AdManager\v201905\updateProposalsResponse
     */
    public function setRval(array $rval = null)
    {
      $this->rval = $rval;
      return $this;
    }

}
