<?php

/**
 * ItePHP: Framework PHP (http://itephp.com)
 * Copyright (c) NewClass (http://newclass.pl)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the file LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) NewClass (http://newclass.pl)
 * @link          http://itephp.com ItePHP Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace ItePHP\Twig;

use ItePHP\Core\AbstractResponse;
use ItePHP\Core\Environment;
use ItePHP\Core\Request;

/**
 * Class TwigPresenter
 * @package ItePHP\Twig
 */
class TwigResponse extends AbstractResponse
{

    /**
     * @var \Twig_Environment $twig
     */
    private $twig;

    /**
     *
     * @var Environment
     */
    private $environment;

    /**
     * @var string
     */
    private $template;

    /**
     *
     * @param Environment $environment
     * @param Request $request
     */
    public function __construct(Environment $environment,Request $request)
    {
        $this->environment = $environment;
        $actionConfig=$request->getConfig();
        if($actionConfig===null){
            return;
        }
        $controllerName = str_replace('\\', '/', $actionConfig->getClass());
        $methodName = $actionConfig->getMethod();
        $this->setTemplate($controllerName.'/'.$methodName);

    }

    /**
     * @param string $template
     */
    public function setTemplate($template){
        $this->template=$template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Support status code 2XX.
     *
     */
    private function displaySuccess()
    {
        $data = $this->getContent();
        if ($data == null) {
            $data = array();
        }
        echo $this->twig->render($this->getTemplate() . '.twig', $data);
    }

    /**
     * Support status code 4XX-5XX.
     */
    private function displayError()
    {
        $exception = $this->getContent();

        $data = array(
            'statusCode' => $this->getStatusCode(),
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        );

        if ($this->environment->isDebug()) {
            echo $this->twig->render('error.twig', $data);
        } else {
            echo $this->twig->render($this->getStatusCode() . '.twig');
        }
    }

    /**
     * Generate content like: html, json etc.
     * @return void
     */
    public function renderBody()
    {
        $this->twig = new TwigService($this->environment);
        if ($this->getStatusCode() >= 400) {
            $this->displayError();
        } else {
            $this->displaySuccess();
        }
    }
}