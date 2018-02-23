<?php

namespace Ontic\SyncApi;

use Symfony\Component\HttpFoundation\Request;

abstract class BaseController implements IController
{
    /** @var  Request */
    private $request;
    /** @var array */
    private $parameters;

    /**
     * @param Request $request
     */
    function setRequest(Request $request)
    {
        $this->request = $request;
    }

    function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->parameters[$name];
    }
}