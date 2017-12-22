<?php

namespace Ontic\SyncApi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface IController
{
    /**
     * @param Request $request
     */
    function setRequest(Request $request);

    /**
     * @param array $parameters
     */
    function setParameters($parameters);

    /**
     * @return Response
     */
    function defaultAction();
}