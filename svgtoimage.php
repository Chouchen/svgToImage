<?
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

	private function _parseCircle($circleNode){
		$x = 0;
		$y = 0;
		$r = 0;
		$fill = '';
		$stroke = '';
		foreach($circleNode->attributes() as $name => $value){
			switch($name){
				//TODO style display:none
				case 'cx': $x = $value; break;
				case 'cy': $y = $value; break;
				case 'r': $r = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if($r == 0)
			return;
		$colorStroke = $this->_parseColor($stroke);
		$colorFill = $this->_parseColor($fill);
		if($this->_debug) $this->_log->message('Cercle - x : '.$x.' - y : '.$y.' - rayon : '.$r.' - fill : '.$colorFill[0].'-'.$colorFill[1].'-'.$colorFill[2].' - stroke : '.$colorStroke[0].'-'.$colorStroke[1].'-'.$colorStroke[2]);
		if($fill == ''){
			
			imageellipse ($this->_image , $x , $y , $r*2 , $r*2, imagecolorallocate($this->_image, $colorStroke[0], $colorStroke[1], $colorStroke[2]) );
		}else{
			
			imagefilledellipse($this->_image , $x , $y , $r*2 , $r*2 , imagecolorallocate($this->_image, $colorFill[0], $colorFill[1], $colorFill[2]) );
		}
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
		foreach($rectNode->attributes() as $name => $value){
			switch($name){
				//TODO style display:none
				case 'x': $x = $value; break;
				case 'y': $y = $value; break;
				case 'r': $r = $value; break;
				case 'width': $width = $value; break;
				case 'height': $height = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if($width == 0 || $height == 0)
			return;
		$colorStroke = $this->_parseColor($stroke);
		$colorFill = $this->_parseColor($fill);
		if($this->_debug) $this->_log->message('Rectangle - x : '.$x.' - y : '.$y.' - width : '.$width.' - height : '.$height.' - fill : '.$colorFill[0].'-'.$colorFill[1].'-'.$colorFill[2].' - stroke : '.$colorStroke[0].'-'.$colorStroke[1].'-'.$colorStroke[2]);
		if($fill == ''){
			imagerectangle($this->_image , $x , $y , $x+$width , $y+$height, imagecolorallocate($this->_image, $colorStroke[0], $colorStroke[1], $colorStroke[2]) ); //resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color
		}else{
			imagefilledrectangle ($this->_image , $x , $y , $x+$width , $y+$height, imagecolorallocate($this->_image, $colorFill[0], $colorFill[1], $colorFill[2]) );
		}
	}
	
	private function _parseDescription($desc){
		if($this->_debug) $this->_log->message('Ajout de la description : '.$desc);
		return imagestring ( $this->_image , 2, 10, $this->_getImageHeight()-20, $desc , imagecolorallocate($this->_image, 255, 255, 255));
	}

	public function toImage(){
		$writeDesc = null;
		$this->_image = imagecreatetruecolor($this->_getImageWidth(), $this->_getImageHeight());
		foreach($this->_svgXML->children() as $element){
			if($element->getName() == 'image')
				$this->_parseImage($element);
			if($element->getName() == 'circle')
				$this->_parseCircle($element);
			if($element->getName() == 'rect')
				$this->_parseRectangle($element);
			if($element->getName() == 'desc' && $this->_showDesc)
				$writeDesc = $element;
		}
		if($writeDesc) $this->_parseDescription($writeDesc);
		return imagepng($this->_image);
	}

}