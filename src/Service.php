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
	
	/**
	 *
	 * @param ServiceConfig $serviceConfig
	 * @param EventManager @eventManager
	 */
	public function __construct(ServiceConfig $serviceConfig,EventManager $eventManager){
	}

	/**
	 * Render view.
	 *
	 * @param string $template
	 * @param array $data
	 */
	public function render($template,$data){
		$loader = new \Twig_Loader_Filesystem(ITE_ROOT.'/template');
		$twig = new \Twig_Environment($loader);

		$extensionDir=ITE_SRC.'/Twig/Extension';
		if(file_exists($extensionDir)){
			$hDir=opendir($extensionDir);
			while($file=readdir($hDir)){
				if($file=='.' || $file=='..'){
					continue;
				}
				$objName='\Twig\Extension\\'.pathinfo($file, PATHINFO_FILENAME);
				$twig->addExtension(new $objName($data));
			}

			closedir($hDir);			
		}

		return $twig->render($template, $data);
	}

}