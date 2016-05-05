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

use ItePHP\Core\Presenter as PresenterInterface;
use ItePHP\Provider\Response;
use ItePHP\Contener\RequestConfig;

class Presenter implements PresenterInterface{

	/**
	 * @var \Twig\Environment $twig
	 */
	private $twig;

    /**
     * {@inheritdoc}
     */
	public function render(RequestConfig $requestConfig , Response $response){
		$this->config=$requestConfig;

		$this->initEngine($response);

		if(!$requestConfig->isSilent()){
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
				if($response->getStatusCode()>=400)
					$this->displayError($response);
				else
					$this->displaySuccess($response);
		}
	}

	/**
	 * Init twig.
	 *
	 * @param \ItePHP\Provider\Response $response
	 */
	private function initEngine($response){
		$loader = new \Twig_Loader_Filesystem(ITE_ROOT.'/template');
		$this->twig = new \Twig_Environment($loader);

		$data=$response->getContent();
		$extensionDir=ITE_SRC.'/Twig/Extension';
		if(file_exists($extensionDir)){
			$hDir=opendir($extensionDir);
			while($file=readdir($hDir)){
				if($file=='.' || $file=='..'){
					continue;
				}
				$objName='\Twig\Extension\\'.pathinfo($file, PATHINFO_FILENAME);
				$this->twig->addExtension(new $objName($data));
			}

			closedir($hDir);			
		}

	}

	/**
	 * Support status code 3XX.
	 *
	 * @param \ItePHP\Provider\Response $response
	 */
	private function redirect(Response $response){
		if(!$this->config->isSilent())
			header('Location: '.$response->getHeader('location'));
	}

	/**
	 * Support status code 2XX.
	 *
	 * @param \ItePHP\Provider\Response $response
	 */
	private function displaySuccess(Response $response){
		$data=$response->getContent();

		echo $this->twig->render($this->config->getController().'/'.$this->config->getMethod().'.twig', $data);

	}

	/**
	 * Support status code 4XX-5XX.
	 *
	 * @param \ItePHP\Provider\Response $response
	 */
	private function displayError(Response $response){
		$exception=$response->getContent();

		$data=array(
			'statusCode'=>$response->getStatusCode()
			,'message'=>$exception->getMessage()
			,'exception'=>get_class($exception)
			,'file'=>$exception->getFile()
			,'line'=>$exception->getLine()
			);

		if($this->config->isDebug())
			echo $this->twig->render('error.twig', $data);
		else
			echo $this->twig->render($response->getStatusCode().'.twig');

	}

}