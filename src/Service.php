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

use ItePHP\Contener\ServiceConfig;
use ItePHP\Core\EventManager;

class Service{
	
	private $engine;

	public function __construct(ServiceConfig $serviceConfig,EventManager $eventManager){
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../template');
		$this->engine = new \Twig_Environment($loader);

	}

	public function render($template,$data){
		return $this->engine->render($template, $data);
	}

}