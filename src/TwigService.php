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

/**
 * Class TwigService
 * @package ItePHP\Twig
 */
class TwigService{

	/**
	 *
	 * @var Environment
	 */
	private $environment;

    /**
     * TwigService constructor.
     * @param Environment $environment
     */
	public function __construct(Environment $environment){
		$this->environment=$environment;
	}

	/**
	 * Render view.
	 *
	 * @param string $template
	 * @param mixed[] $data
	 * @return string
	 */
	public function render($template,$data=[]){
		$loader = new \Twig_Loader_Filesystem($this->environment->getRootPath().'/template');
		$twig = new \Twig_Environment($loader);

		$extensionDir=$this->environment->getSrcPath().'/Twig/Extension';
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