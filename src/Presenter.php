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

	public function render(RequestConfig $requestConfig , Response $response){
		$this->config=$requestConfig;
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

	private function redirect(Response $response){
		if(!$this->config->isSilent())
			header('Location: '.$response->getHeader('location'));
	}

	private function displaySuccess(Response $response){
		$presenterConfig=$this->config->getPresenter();
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../template'); //TODO przekazywać do presentera config
		$twig = new \Twig_Environment($loader);

		$data=$response->getContent();
		$extensionDir=__DIR__.'/../../../../twig/extension';
		if(file_exists($extensionDir)){
			$hDir=opendir($extensionDir);
			while($file=readdir($hDir)){
				if($file=='.' || $file=='..'){
					continue;
				}
				$objName='\Twig\Extension\\'.pathinfo($file, PATHINFO_FILENAME);
				$twig->addExtension(new $objName($data));
			}
			
		}

		echo $twig->render($this->config->getController().'/'.$this->config->getMethod().'.twig', $data);

	}

	private function displayError(Response $response){
		$presenterConfig=$this->config->getPresenter();
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../template'); //TODO przekazywać do presentera config
		$twig = new \Twig_Environment($loader);
		$exception=$response->getContent();

		$data=array(
			'statusCode'=>$response->getStatusCode()
			,'message'=>$exception->getMessage()
			,'exception'=>get_class($exception)
			,'file'=>$exception->getFile()
			,'line'=>$exception->getLine()
			);

		if($this->config->isDebug())
			echo $twig->render('error.twig', $data);
		else
			echo $twig->render($response->getStatusCode().'.twig');

	}


	private function createIsAllowFunction($functionalities){
		return new \Twig_SimpleFunction('isAllow', function ($requireFunctionalities) use($functionalities){
			if(!is_array($requireFunctionalities))
				$requireFunctionalities=array($requireFunctionalities);
			foreach($requireFunctionalities as $requireFunctionality){
				if(in_array($requireFunctionality , $functionalities)){
					return true;
				}
			}
			return false;

		});
	}

}