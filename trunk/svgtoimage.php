<?
// TODO
// ajouter header thanks to http://fr.php.net/manual/fr/function.image-type-to-mime-type.php
// prendre en compte l'opacité grâce à imagecolorallocatealpha ?
// conversion des couleurs "black" , "blue", etc en #000 etc.
// stroke-width pour tous \o/
// pour les rectangles avec point ou tiret http://fr.php.net/manual/fr/function.imagesetstyle.php

include 'log.php';
class SVGTOIMAGE{
	
	protected $_svg;
	protected $_svgXML;
	protected $_image;
	protected $_format;
	protected $_log;
	protected $_x;
	protected $_y;
	protected $_width;
	protected $_height;
	protected $_showDesc = false;
	private $transparentColor = array(0,0,255);
	public $_debug = true;
	
	//const TIRET = array($red, $red, $red, $red, $red, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
	
	private $colors = array(
		'black'			=> '#000000',
		'red'			=> '#FF0000',
		'white'			=> '#FFFFFF',
		'turquoise' 	=> '#00FFFF',
		'light grey'	=> '#C0C0C0',
		'light blue'	=> '#0000FF',
		'dark grey'		=> '#808080',
		'dark blue'		=> '#0000A0',
		'light purple' 	=> '#FF0080',
		'orange'		=> '#FF8040',
		'dark purple' 	=> '#800080',
		'brown'			=> '#804000',
		'yellow'		=> '#FFFF00',
		'burgundy'		=> '#800000',
		'pastel green'	=> '#00FF00',
		'forest green'	=> '#808000',
		'pink'			=> '#FF00FF',
		'grass green'	=> '#408080',
	);
	
	public function __construct($svg, $format = 'png'){
		if($this->_debug) $this->_log = new Log('log.dat');
		$this->_svg = $svg;
		if($this->_debug) $this->_log->message('Ouverture du fichier contentant : '.$svg);
		$this->_svgXML = simplexml_load_string($this->_svg);
		$this->_format = $format;
	}
	
	public static function load($file){
		$svg = file_get_contents($file);
		return new SVGTOIMAGE($svg);
	}
	
	public static function parse($xml){
		return new SVGTOIMAGE($xml);
	}
	
	public function __destruct(){
		imagedestroy($this->_image);
	}
	
	public function setShowDesc($showDesc = true){
		if(is_bool($showDesc)){
			if($this->_debug) $this->_log->message('Passage de showDesc en '.$showDesc);
			$this->_showDesc = $showDesc;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction showDesc, doit recevoir booléen, a reçu : '.$showDesc);
		}
	}
	
	public function setX($x){
		if(is_int($x)){
			if($this->_debug) $this->_log->message('Passage de x en '.$x);
			$this->_x = $x;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setX, doit recevoir int, a reçu : '.$x);
		}
	}
	
	public function setY($y){
		if(is_int($y)){
			if($this->_debug) $this->_log->message('Passage de y en '.$y);
			$this->_y = $y;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setY, doit recevoir int, a reçu : '.$y);
		}
	}
	
	public function setWidth($width){
		if(is_int($width)){
			if($this->_debug) $this->_log->message('Passage de width en '.$width);
			$this->_width = $width;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setWidth, doit recevoir int, a reçu : '.$width);
		}
	}
	
	public function setHeight($height){
		if(is_int($height)){
			if($this->_debug) $this->_log->message('Passage de height en '.$height);
			$this->_height = $height;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setHeight, doit recevoir int, a reçu : '.$height);
		}
	}
	
	/* return width and height from the SVG */
	private function _getImageSize(){
		$imageSize = array();
		$imageSize['width'] = $this->_svgXML->attributes()->width;
		$imageSize['height'] = $this->_svgXML->attributes()->height;
		if($this->_debug) $this->_log->message('taille de l\'image : largeur : '.$imageSize['width'].' - longueur : '.$imageSize['height']);
		return $imageSize;
	}
	
	private function _getImageWidth(){
		return isset($this->_width) ? $this->_width : $this->_svgXML->attributes()->width;
	}
	
	private function _getImageHeight(){
		return isset($this->_height) ? $this->_height : $this->_svgXML->attributes()->height;
	}
	
	
	private function _parseColor($colorCode){	
		if(strlen($colorCode) == 7){
			if($this->_debug) $this->_log->message('Parse Color '.$colorCode);
			return array(
				base_convert(substr($colorCode, 1, 2), 16, 10),
				base_convert(substr($colorCode, 3, 2), 16, 10),
				base_convert(substr($colorCode, 5, 2), 16, 10),
			);
		}
		if(strlen($colorCode) == 4){
			if($this->_debug) $this->_log->message('Parse Color '.$colorCode);
			return array(
				base_convert(substr($colorCode, 1, 1).substr($colorCode, 1, 1), 16, 10),
				base_convert(substr($colorCode, 2, 1).substr($colorCode, 2, 1), 16, 10),
				base_convert(substr($colorCode, 3, 1).substr($colorCode, 3, 1), 16, 10),
			);
		}
		if($this->_debug) $this->_log->error('Couleur mal indiquée '.$colorCode);	
		return array(0,0,0);
	}
	
	private function _allocateColor($color){
		if($color != '' && array_key_exists($color, $this->colors)){
			$arrayColor = $this->_parseColor($this->colors[$color]);
		}else{
			$arrayColor = $this->_parseColor($color);
		}
		return imagecolorallocate( $this->_image, $arrayColor[0], $arrayColor[1], $arrayColor[2] );
	}
	
	private function _parseImage($imageNode){
		$x = 0;
		$y = 0;
		$width = 0;
		$height = 0;
		$href = '';
		$r = 0;
		foreach($imageNode->attributes() as $name => $value){
			switch($name){
				case 'x': $x = $value; break;
				case 'y': $y = $value; break;
				case 'width': $width = $value; break;
				case 'height': $height = $value; break;
				case 'href':
				case 'xlink:href':$href = $value; break;
				case 'r' : $r = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if($width == 0 || $height == 0 || $href == '')
			return;
		if($this->_debug) $this->_log->message('Image - x : '.$x.' - y : '.$y.' - largeur : '.$width.' - longueur : '.$height.' - url : '.$href.' - angle : '.$r);	
		$imageTypeArray = explode('.', $href);
		$lastElementFromImageType = count($imageTypeArray);
		$imageType = $imageTypeArray[$lastElementFromImageType-1];
		if($imageType == 'jpg' || $imageType == 'jpeg')
			$newImage = imagecreatefromjpeg($href);
		else if($imageType == 'png')
			$newImage = imagecreatefrompng($href);
		else if($imageType == 'gif')
			$newImage = imagecreatefromgif($href);
		else return;
		
		imagealphablending($newImage, true);
		
		//rotating the image if needed
		if($r != 0){
			if($this->_debug){
				if($newImage = imagerotate($newImage, - floatval($r), -1))
					$this->_log->message('Rotating image');
				else
					$this->_log->error('Rotating image');		
			}else{
				$newImage = imagerotate($newImage, - floatval($r), -1);
			}
			$blue = imagecolorallocate($newImage, $this->transparentColor[0], $this->transparentColor[1],$this->transparentColor[2]);
			imagecolortransparent($newImage, $blue);
		}
		
		imagecopy($this->_image,$newImage,$x,$y,0,0,imagesx($newImage) , imagesy($newImage));
	}

	private function _pathIsW3C($path){
		if(strripos($path, ','))
			return true;
		return false;
	}
	
	private function _parseInt($string){
		if(preg_match('/(\d+)/', $string, $array)) {
			return $array[1];
		} else {
			return 0;
		}
	}
	
	private function _drawLine($x1, $y1, $x2, $y2, $color){
		if(!imageline( $this->_image , $x1, $y1, $x2, $y2, $color )){
			if($this->_debug) $this->_log->error('Chemin erroné : '.$x1.' - '.$y1.' - '.$x2.' - '.$y2);
		}else{
			if($this->_debug) $this->_log->message('Chemin : '.$x1.' - '.$y1.' - '.$x2.' - '.$y2);
		}
	}
	
	private function _parsePath($pathNode){
	// <path d="M50,50 A30,30 0 0,1 35,20 L100,100 M110,110 L100,0" style="stroke:#660000; fill:none;"/> 
	//<path d="M20 150 L150 350 Z" />
		// imagesetbrush
		// imagesetstyle  (pour dotted, dashed etc)
		$path = '';
		$strokeWidth = 1;
		$fill = '';
		$stroke = '';
		foreach($pathNode->attributes() as $name=>$value){
			switch($name){
				case 'd': $path = $value; break;
				case 'stroke': $stroke = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if(substr($path, 0,1) != 'M'){
			if($this->_debug) $this->_log->error('Mauvais path rencontré : '.$path);
			return;
		}

		$thickness = imagesetthickness( $this->_image , $this->_parseInt($strokeWidth) );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		else $this->_log->message('épaisseur du trait à : '.$this->_parseInt($strokeWidth));
		$colorStroke = $stroke != '' ? $this->_allocateColor((string)$stroke) : $this->_allocateColor('black');
		$colorFill = $fill != '' ? $this->_allocateColor((string)$fill) : $this->_allocateColor('black');
		$lastOpe = '';
		
		$pathArray = split('[ ,]', $path); //explode(' ', $path);
		$nbArray = count($pathArray);
		$nbLine = (($nbArray-1)/2)-1;
		if($this->_debug) $this->_log->message($nbLine.' lignes à dessiner sur un path de '.$nbArray);
		//for($i = 2; $i < $nbArray; ){
		$i = 0;
		$lastX = 0;
		$lastY = 0;
		while ($i < $nbArray) {
			// Changement de départ
			if(substr($pathArray[$i], 0, 1) == 'M'){
				$lastX = $this->_parseInt($pathArray[$i]);
				$lastY = $this->_parseInt($pathArray[$i+1]);
				$lastOpe = 'M';
				$i=$i+2;

			}elseif(substr($pathArray[$i], 0, 1) == 'L'){
				$newX = $this->_parseInt($pathArray[$i]);
				$newY = $this->_parseInt($pathArray[$i+1]);
				$this->_drawLine($lastX , $lastY , $newX , $newY , $colorStroke);
				$lastOpe = 'L';
				$lastX = $newX;
				$lastY = $newY;
				$i=$i+2;

			}elseif(substr($pathArray[$i], 0, 1) == 'H'){
				$newX = $this->_parseInt($pathArray[$i]);
				$this->_drawLine($lastX , $lastY , $newX , $lastY , $colorStroke);
				$lastOpe = 'H';
				$lastX = $newX;
				$i++;

			}elseif(substr($pathArray[$i], 0, 1) == 'V'){
				$newY = $this->_parseInt($pathArray[$i]);
				$this->_drawLine($lastX , $lastY , $lastX , $newY , $colorStroke);
				$lastY = $newY;
				$lastOpe = 'V';
				$i++;

			}elseif(is_numeric(substr($pathArray[$i], 0, 1))){
				switch($lastOpe){
					case 'L': 
						$newX = $this->_parseInt($pathArray[$i]);
						$newY = $this->_parseInt($pathArray[$i+1]);
						$this->_drawLine($lastX , $lastY , $newX , $newY , $colorStroke);
						$lastX = $newX;
						$lastY = $newY;
						break;
					case 'H': 
						$newX = $this->_parseInt($pathArray[$i]);
						$this->_drawLine($lastX , $lastY , $newX , $lastY , $colorStroke);
						$lastX = $newX;
						$i++;
				 		break;
					case 'V': 
						$newY = $this->_parseInt($pathArray[$i]);
						$this->_drawLine($lastX , $lastY , $lastX , $newY , $colorStroke);
						$lastY = $newY;
						$i++;
						break;
					case 'Z': 
						if($this->_debug) $this->_log->error('2 bouclages dans une boucle'); 
						$i++;
						break;
					default : 
						if($this->_debug) $this->_log->error('last opé inconnue '.$lastOpe); 
						$i++; 
						break;
				}

			}elseif(substr($pathArray[$i], 0, 1) == 'Z'){
				$this->_drawLine($lastX , $lastY , $this->_parseInt($pathArray[0]) , $this->_parseInt($pathArray[1]) , $colorStroke);
				$lastOpe = 'Z'; //utile?
				$i++;
			}else 
				$i++; // au cas où.
			if($this->_debug) $this->_log->message('counter :'.$i);
		}
		imagecolordeallocate( $this->_image, $colorStroke);
		imagecolordeallocate( $this->_image, $colorFill);
		imagesetthickness ( $this->_image , 1 );
	}
	
	private function _parseCircle($circleNode){
		$x = 0;
		$y = 0;
		$r = 0;
		$strokeWidth = 1;
		$fill = '';
		$stroke = '';
		foreach($circleNode->attributes() as $name => $value){
			switch($name){
				case 'cx': $x = $value; break;
				case 'cy': $y = $value; break;
				case 'r': $r = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
				case 'stroke-width' : $strokeWidth = $value; break;
			}
		}
		if($r == 0)
			return;
		if($this->_debug) $this->_log->message('Cercle - x : '.$x.' - y : '.$y.' - rayon : '.$r.'-'.$colorStroke[2].' - épaisseur : '.$strokeWidth);
		
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
			
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		
		if($fill == ''){
			imagearc($this->_image , $x , $y , $r*2 , $r*2,0,359.9, $colorStroke );
			//imageellipse ($this->_image , $x , $y , $r*2 , $r*2, $colorStroke );
		}else{
			
			imagefilledarc($this->_image , $x , $y , $r*2 , $r*2 ,0,359.9, $colorFill, IMG_ARC_PIE );
		}
		imagecolordeallocate( $this->_image, $colorStroke);
		imagecolordeallocate( $this->_image, $colorFill);
		imagesetthickness ( $this->_image , 1 );
	}
	
	private function _parseRectangle($rectNode){
	//<rect x="168" y="275" width="52" height="70" r="0" rx="0" ry="0" fill="none" stroke="#000" stroke-dasharray="8,3" transform="rotate(21.91207728 194 310)" style="opacity: 1; display: none; " opacity="1"></rect>
		$x = 0;
		$y = 0;
		$width = 0;
		$height = 0;
		$r = 0;
		$fill = '';
		$stroke = '';
		$strokeWidth = 1;
		foreach($rectNode->attributes() as $name => $value){
			switch($name){
			// imagesetthickness ( resource $image , int $thickness )
				// imagesetstyle  (pour dotted, dashed etc)
				case 'x': $x = $value; break;
				case 'y': $y = $value; break;
				case 'r': $r = $value; break;
				case 'width': $width = $value; break;
				case 'height': $height = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if($width == 0 || $height == 0)
			return;
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		if($this->_debug) $this->_log->message('Rectangle - x : '.$x.' - y : '.$y.' - width : '.$width.' - height : '.$height.' - fill : '.$colorFill[0].'-'.$colorFill[1].'-'.$colorFill[2].' - stroke : '.$colorStroke[0].'-'.$colorStroke[1].'-'.$colorStroke[2]);
		if($fill == ''){
			imagerectangle($this->_image , $x , $y , $x+$width , $y+$height, $colorStroke); //resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color
		}else{
			imagefilledrectangle ($this->_image , $x , $y , $x+$width , $y+$height, $colorFill );
		}
		imagecolordeallocate($this->_image,$colorStroke);
		imagecolordeallocate($this->_image,$colorFill);
		imagesetthickness ( $this->_image , 1 );
	}
	
	private function _parseDescription($desc){
		if($this->_debug) $this->_log->message('Ajout de la description : '.$desc);
		return imagestring ( $this->_image , 2, 10, $this->_getImageHeight()-20, $desc , imagecolorallocate($this->_image, 255, 255, 255));
	}

	public function toImage(){
		//$test = Imagick::__construct('http://labs.shikiryu.com/experimental-cut/images/pieces/2.png');
		$writeDesc = null;
		$this->_image = imagecreatetruecolor($this->_getImageWidth(), $this->_getImageHeight());
		imagealphablending($this->_image, true);
		//imageantialias($this->_image, true); // On ne peut pas gérer l'épaisseur des traits si l'antialiasing est activé... lol ?
		foreach($this->_svgXML->children() as $element){
			if($element->getName() == 'image')
				$this->_parseImage($element);
			if($element->getName() == 'circle')
				$this->_parseCircle($element);
			if($element->getName() == 'rect')
				$this->_parseRectangle($element);
			if($element->getName() == 'path')
				$this->_parsePath($element);
			if($element->getName() == 'desc' && $this->_showDesc)
				$writeDesc = $element;
		}
		if($writeDesc) $this->_parseDescription($writeDesc);
		//imagefilter ( $this->_image , IMG_FILTER_SMOOTH, 6);
		return imagepng($this->_image);
	}

}