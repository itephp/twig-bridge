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

use ItePHP\Core\Environment;
use ItePHP\Core\Presenter;
use ItePHP\Core\Response;
use ItePHP\Core\Request;

/**
 * Class TwigPresenter
 * @package ItePHP\Twig
 */
class TwigPresenter implements Presenter{

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
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment){
        $this->environment=$environment;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Request $request , Response $response){
        $this->twig=new TwigService($this->environment);
        if(!$this->environment->isSilent()){
            header('HTTP/1.1 '.$response->getStatusCode().' '.$response->getStatusMessage());
            foreach($response->getHeaders() as $name=>$value){
                header($name.': '.$value);
            }
        }

        switch($response->getStatusCode()){
            case 300:
            case 301:
            case 302:
            case 303:
            case 305:
            case 307:
                $this->redirect($response);
                break;
            default:
                if($response->getStatusCode()>=400){
                    $this->displayError($request,$response);
                }
                else{
                    $this->displaySuccess($request,$response);
                }
        }
    }

    /**
     * @param Response $response
     */
    private function redirect(Response $response){
        if($this->environment->isSilent()){
            return;
        }
        header('Location: '.$response->getHeader('location'));
    }

    /**
     * Support status code 2XX.
     *
     * @param Request $request
     * @param Response $response
     */
    private function displaySuccess(Request $request,Response $response){
        $data=$response->getContent();
        if($data==null){
            $data=array();
        }
        $controllerName=str_replace('\\', '/', $request->getConfig()->getClass());
        $methodName=$request->getConfig()->getMethod();
        echo $this->twig->render($controllerName.'/'.$methodName.'.twig', $data);
    }

    /**
     * Support status code 4XX-5XX.
     *
     * @param Request $request
     * @param Response $response
     */
    private function displayError(Request $request,Response $response){
        $exception=$response->getContent();

        $data=array(
            'statusCode'=>$response->getStatusCode()
        ,'message'=>$exception->getMessage()
        ,'exception'=>get_class($exception)
        ,'file'=>$exception->getFile()
        ,'line'=>$exception->getLine()
        );

        if($this->environment->isDebug()){
            echo $this->twig->render('error.twig', $data);
        }
        else{
            echo $this->twig->render($response->getStatusCode().'.twig');
        }
    }

}