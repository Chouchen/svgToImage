<?
// TODO
// prendre en compte l'opacité grâce à imagecolorallocatealpha ?
// ajout de title
// ajout de <text fill="#000000" x="541" y="258" transform="rotate(-0, 541, 258)" font-size="10" font-family="SansSerif" font-style="normal" font-weight="normal">0</text>
// refaire path en GROS polygon


include 'log.php';

class SVGTOIMAGE{
	
	protected $_svgXML;
	protected $_image;
	protected $_log;
	protected $_x;
	protected $_y;
	protected $_width;
	protected $_height;
	protected $_showDesc = false;
	protected $_desc;
	protected $_currentOptions;
	private $transparentColor = array(0,0,255);
	public $_debug = true; // change to false to stop debug mode

	/*
	 * array of path type
	 */
	private $pathType = array(
		'm'	=> 'MoveTo',
		'l' => 'LineTo',
		'h' => 'HorizontalLineTo',
		'v' => 'VerticalLineTo',
		'c'	=> 'CurveTo',
		'z' => 'EndingLine',
	);
	
	/* array of color names => rgb color
		because some svg creator uses them
		used http://www.yoyodesign.org/doc/w3c/svg1/types.html#ColorKeywords
		*/
	private $colors = array(
		'aliceblue'=>array(240, 248, 255),
		'antiquewhite'=>array(250, 235, 215),
		'aqua'=>array( 0, 255, 255),
		'aquamarine'=>array(127, 255, 212),
		'azure'=>array(240, 255, 255),
		'beige'=>array(245, 245, 220),
		'bisque'=>array(255, 228, 196),
		'black'=>array( 0, 0, 0),
		'blanchedalmond'=>array(255, 235, 205),
		'blue'=>array( 0, 0, 255),
		'blueviolet'=>array(138, 43, 226),
		'brown'=>array(165, 42, 42),
		'burlywood'=>array(222, 184, 135),
		'cadetblue'=>array( 95, 158, 160),
		'chartreuse'=>array(127, 255, 0),
		'chocolate'=>array(210, 105, 30),
		'coral'=>array(255, 127, 80),
		'cornflowerblue'=>array(100, 149, 237),
		'cornsilk'=>array(255, 248, 220),
		'crimson'=>array(220, 20, 60),
		'cyan'=>array( 0, 255, 255),
		'darkblue'=>array( 0, 0, 139),
		'darkcyan'=>array( 0, 139, 139),
		'darkgoldenrod'=>array(184, 134, 11),
		'darkgray'=>array(169, 169, 169),
		'darkgreen'=>array( 0, 100, 0),
		'darkgrey'=>array(169, 169, 169),
		'darkkhaki'=>array(189, 183, 107),
		'darkmagenta'=>array(139, 0, 139),
		'darkolivegreen'=>array( 85, 107, 47),
		'darkorange'=>array(255, 140, 0),
		'darkorchid'=>array(153, 50, 204),
		'darkred'=>array(139, 0, 0),
		'darksalmon'=>array(233, 150, 122),
		'darkseagreen'=>array(143, 188, 143),
		'darkslateblue'=>array( 72, 61, 139),
		'darkslategray'=>array( 47, 79, 79),
		'darkslategrey'=>array( 47, 79, 79),
		'darkturquoise'=>array( 0, 206, 209),
		'darkviolet'=>array(148, 0, 211),
		'deeppink'=>array(255, 20, 147),
		'deepskyblue'=>array( 0, 191, 255),
		'dimgray'=>array(105, 105, 105),
		'dimgrey'=>array(105, 105, 105),
		'dodgerblue'=>array( 30, 144, 255),
		'firebrick'=>array(178, 34, 34),
		'floralwhite'=>array(255, 250, 240),
		'forestgreen'=>array( 34, 139, 34),
		'fuchsia'=>array(255, 0, 255),
		'gainsboro'=>array(220, 220, 220),
		'ghostwhite'=>array(248, 248, 255),
		'gold'=>array(255, 215, 0),
		'goldenrod'=>array(218, 165, 32),
		'gray'=>array(128, 128, 128),
		'grey'=>array(128, 128, 128),
		'green'=>array( 0, 128, 0),
		'greenyellow'=>array(173, 255, 47),
		'honeydew'=>array(240, 255, 240),
		'hotpink'=>array(255, 105, 180),
		'indianred'=>array(205, 92, 92),
		'indigo'=>array( 75, 0, 130),
		'ivory'=>array(255, 255, 240),
		'khaki'=>array(240, 230, 140),
		'lavender'=>array(230, 230, 250),
		'lavenderblush'=>array(255, 240, 245),
		'lawngreen'=>array(124, 252, 0),
		'lemonchiffon'=>array(255, 250, 205),
		'lightblue'=>array(173, 216, 230),
		'lightcoral'=>array(240, 128, 128),
		'lightcyan'=>array(224, 255, 255),
		'lightgoldenrodyellow'=>array(250, 250, 210),
		'lightgray'=>array(211, 211, 211),
		'lightgreen'=>array(144, 238, 144),
		'lightgrey'=>array(211, 211, 211),
		'lightpink'=>array(255, 182, 193),
		'lightsalmon'=>array(255, 160, 122),
		'lightseagreen'=>array( 32, 178, 170),
		'lightskyblue'=>array(135, 206, 250),
		'lightslategray'=>array(119, 136, 153),
		'lightslategrey'=>array(119, 136, 153),
		'lightsteelblue'=>array(176, 196, 222),
		'lightyellow'=>array(255, 255, 224),
		'lime'=>array( 0, 255, 0),
		'limegreen'=>array( 50, 205, 50),
		'linen'=>array(250, 240, 230),
		'magenta'=>array(255, 0, 255),
		'maroon'=>array(128, 0, 0),
		'mediumaquamarine'=>array(102, 205, 170),
		'mediumblue'=>array( 0, 0, 205),
		'mediumorchid'=>array(186, 85, 211),
		'mediumpurple'=>array(147, 112, 219),
		'mediumseagreen'=>array( 60, 179, 113),
		'mediumslateblue'=>array(123, 104, 238),
		'mediumspringgreen'=>array( 0, 250, 154),
		'mediumturquoise'=>array( 72, 209, 204),
		'mediumvioletred'=>array(199, 21, 133),
		'midnightblue'=>array( 25, 25, 112),
		'mintcream'=>array(245, 255, 250),
		'mistyrose'=>array(255, 228, 225),
		'moccasin'=>array(255, 228, 181),
		'navajowhite'=>array(255, 222, 173),
		'navy'=>array( 0, 0, 128),
		'oldlace'=>array(253, 245, 230),
		'olive'=>array(128, 128, 0),
		'olivedrab'=>array(107, 142, 35),
		'orange'=>array(255, 165, 0),
		'orangered'=>array(255, 69, 0),
		'orchid'=>array(218, 112, 214),
		'palegoldenrod'=>array(238, 232, 170),
		'palegreen'=>array(152, 251, 152),
		'paleturquoise'=>array(175, 238, 238),
		'palevioletred'=>array(219, 112, 147),
		'papayawhip'=>array(255, 239, 213),
		'peachpuff'=>array(255, 218, 185),
		'peru'=>array(205, 133, 63),
		'pink'=>array(255, 192, 203),
		'plum'=>array(221, 160, 221),
		'powderblue'=>array(176, 224, 230),
		'purple'=>array(128, 0, 128),
		'red'=>array(255, 0, 0),
		'rosybrown'=>array(188, 143, 143),
		'royalblue'=>array( 65, 105, 225),
		'saddlebrown'=>array(139, 69, 19),
		'salmon'=>array(250, 128, 114),
		'sandybrown'=>array(244, 164, 96),
		'seagreen'=>array( 46, 139, 87),
		'seashell'=>array(255, 245, 238),
		'sienna'=>array(160, 82, 45),
		'silver'=>array(192, 192, 192),
		'skyblue'=>array(135, 206, 235),
		'slateblue'=>array(106, 90, 205),
		'slategray'=>array(112, 128, 144),
		'slategrey'=>array(112, 128, 144),
		'snow'=>array(255, 250, 250),
		'springgreen'=>array( 0, 255, 127),
		'steelblue'=>array( 70, 130, 180),
		'tan'=>array(210, 180, 140),
		'teal'=>array( 0, 128, 128),
		'thistle'=>array(216, 191, 216),
		'tomato'=>array(255, 99, 71),
		'turquoise'=>array( 64, 224, 208),
		'violet'=>array(238, 130, 238),
		'wheat'=>array(245, 222, 179),
		'white'=>array(255, 255, 255),
		'whitesmoke'=>array(245, 245, 245),
		'yellow'=>array(255, 255, 0),
		'yellowgreen'=>array(154, 205, 50)
		/*'black'			=> '#000000',
		'red'			=> '#FF0000',
		'white'			=> '#FFFFFF',
		'turquoise' 	=> '#00FFFF',
		'grey' 			=> '#CCCCCC',
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
		'grass green'	=> '#408080',*/
	);
	
	/*
	 * constructor
	 * parse the svg with simplexml
	 */
	public function __construct($svg){
		if($this->_debug) $this->_log = new Log('log.dat');
		//if($this->_debug) $this->_log->message('Ouverture du fichier contentant : '.$svg);
		$this->_svgXML = simplexml_load_string($svg);
	}
	
	/*
	 * Construct with a file
	 * @param : string path to the file
	 * @return : instance of this class
	 */
	public static function load($file){
		$log = new Log('load.dat');
		$log->message('loading : '.$file);
		$svg = file_get_contents($file);
		return new SVGTOIMAGE($svg);
	}
	
	/*
	 * Construct with a string
	 * @param : string <svg>...</svg>
	 * @return : instance of this class
	 */
	public static function parse($xml){
		return new SVGTOIMAGE($xml);
	}
	
	/*
	 * Destroy the GD Image when finished
	 */
	public function __destruct(){
		imagedestroy($this->_image);
	}
	
	/*
	 * setter - option : show the description from the svg into the image if present
	 * @param boolean
	 */
	public function setShowDesc($showDesc = true){
		if(is_bool($showDesc)){
			if($this->_debug) $this->_log->message('Passage de showDesc en '.$showDesc);
			$this->_showDesc = $showDesc;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction showDesc, doit recevoir booléen, a reçu : '.$showDesc);
		}
	}
	
	/*
	 * setter - option : origin of the final image from the svg (default : 0)
	 * @param int
	 */
	public function setX($x){
		if(is_int($x)){
			if($this->_debug) $this->_log->message('Passage de x en '.$x);
			$this->_x = $x;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setX, doit recevoir int, a reçu : '.$x);
		}
	}
	
	/*
	 * setter - option : origin of the final image from the svg (default : 0)
	 * @param int
	 */
	public function setY($y){
		if(is_int($y)){
			if($this->_debug) $this->_log->message('Passage de y en '.$y);
			$this->_y = $y;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setY, doit recevoir int, a reçu : '.$y);
		}
	}
	
	/*
	 * setter - option : width of the final image (default : svg width)
	 * @param int
	 */
	public function setWidth($width){
		if(is_int($width)){
			if($this->_debug) $this->_log->message('Passage de width en '.$width);
			$this->_width = $width;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setWidth, doit recevoir int, a reçu : '.$width);
		}
	}
	
	/*
	 * setter - option : height of the final image (default : svg height)
	 * @param int
	 */
	public function setHeight($height){
		if(is_int($height)){
			if($this->_debug) $this->_log->message('Passage de height en '.$height);
			$this->_height = $height;
		}else{
			if($this->_debug) $this->_log->error('Erreur dans la fonction setHeight, doit recevoir int, a reçu : '.$height);
		}
	}
	
	/* 
	 * DEPRECATED
	 * return width and height from the SVG 
	 */
	private function _getImageSize(){
		$imageSize = array();
		$imageSize['width'] = $this->_svgXML->attributes()->width;
		$imageSize['height'] = $this->_svgXML->attributes()->height;
		if($this->_debug) $this->_log->message('taille de l\'image : largeur : '.$imageSize['width'].' - longueur : '.$imageSize['height']);
		return $imageSize;
	}
	
	/**
	 * @param string size of picture or element
	 * @return int "real" size of element. To eliminate SVG with centimeters
	 */	
	private function _getSizeType($value){
		$value = rtrim($value);
		switch(substr($value,-2,2)){
			case 'cm':
				return $value * 30; // approximatively
				break;
			case 'in':
				return $value * 12; // approximatively
				break;
			case 'px':
			case 'pt':
			default:
				return $value;
		}
	}
	
	/*
	 * @return int final image width
	 */
	private function _getImageWidth(){
		return isset($this->_width) ? $this->_width : $this->_getSizeType($this->_svgXML->attributes()->width);
	}
	
	/*
	 * @return int final image height
	 */
	private function _getImageHeight(){
		return isset($this->_height) ? $this->_height : $this->_getSizeType($this->_svgXML->attributes()->height);
	}
	
	/*
	 * @param string Color code (ie: #CCC , #FE4323, etc...)
	 * @return array with R | G | B
	 */
	private function _parseColor($colorCode){	
		if(is_string($colorCode) && strlen($colorCode) == 7){
			if($this->_debug) $this->_log->message('Parse Color '.$colorCode);
			return array(
				base_convert(substr($colorCode, 1, 2), 16, 10),
				base_convert(substr($colorCode, 3, 2), 16, 10),
				base_convert(substr($colorCode, 5, 2), 16, 10),
			);
		}
		if(is_string($colorCode) && strlen($colorCode) == 4){
			if($this->_debug) $this->_log->message('Parse Color '.$colorCode);
			return array(
				base_convert(substr($colorCode, 1, 1).substr($colorCode, 1, 1), 16, 10),
				base_convert(substr($colorCode, 2, 1).substr($colorCode, 2, 1), 16, 10),
				base_convert(substr($colorCode, 3, 1).substr($colorCode, 3, 1), 16, 10),
			);
		}
		if(is_array($colorCode) && count($colorCode) == 3){
			return $colorCode;		
		}
		if($this->_debug) $this->_log->error('Couleur mal indiquée '.$colorCode);	
		return array(0,0,0); // !#FFF || !#FFFFFF || !array(255,255,255) then black
	}
	
	/*
	 * Allocate color to the final image thanks to _parseColor (check if the color isn't spelled directly 'black')
	 * @param string color code
	 * @return imageallocate on the image
	 */
	private function _allocateColor($color){
		if($color != '' && array_key_exists(strtolower($color), $this->colors)){
			$arrayColor = $this->_parseColor($this->colors[$color]);
		}elseif($color != ''){
			$arrayColor = $this->_parseColor($color);
		}else return;
		return imagecolorallocate( $this->_image, $arrayColor[0], $arrayColor[1], $arrayColor[2] );
	}
	
	/*
	 * return an array to use with imagesetstyle 
	 * @param allocatecolorimage 
	 * @return array 
	 */
	private function _getDashedStroke($full, $empty, $color){
		$tiret = array();
		for($i=0;$i<$full;$i++){
			$tiret[] = $color;
		}
		for($i=0;$i<$empty;$i++){
			$tiret[] = IMG_COLOR_TRANSPARENT;
		}
		if($this->_debug) $this->_log->message('nouveaux tirets : '.implode('-', $tiret));
		return $tiret;
	}
	
	/**
	 * @param node $element
	 * @return array options
	 */
	private function _getParams($element){
		$options = $this->_currentOptions;
		foreach($element->attributes() as $name => $value){
			switch($name){
				case 'x': 
				case 'y': 
				case 'r':
				case 'width': 
				case 'height': 
					$options[$name] = $this->_getSizeType($value);
					break;
				case 'cx':
					$options['x'] = $this->_getSizeType($value);
					break;
				case 'cy':
					$options['y'] = $this->_getSizeType($value);
					break;
				case 'xlink:href':
					$options['href'] = $value; 
					break;
				case 'style' : 
					$allStyle = split('[;:]', $value);
					$i = 0;
					while ($i < count($allStyle)) {
						if($allStyle[$i] == 'display' && $allStyle[$i+1] == 'none') return; // display:none? Stop looking for info
						if($allStyle[$i] == 'fill') $options['fill'] = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke') $options['stroke'] = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke-width') $options['strokeWidth'] = $allStyle[$i+1]; 
						$i=$i+2;
					}
					break;
				case 'd': 
				case 'points': 
					$options['path'] = $value; 
					break;
				case 'fill': 
					$options['fill'] = ($value == 'none') ? '' : $value; 
					break;
				case 'stroke-width' : 
					$options['strokeWidth'] = $value; 
					break;
				case 'stroke-dasharray' : 
					$options['strokeDasharray'] = $value; 
					break;
				case 'font-size': 
					$options['fontSize'] = $value; 
					break;
				case 'font-family': 
					$options['fontFamily'] = $value; 
					break;
				case 'font-style': 
					$options['fontStyle'] = $value; 
					break;
				case 'font-weight': 
					$options['fontWeight'] = $value; 
					break;
				default:
					$options[$name] = $value;
					break;				
			}
		}
		return $options;
	}
	
	/*
	 * add the given image from svg to the final image
	 * @param simpleXMLElement
	 * @return imagecopy
	 */
	private function _parseImage($imageNode){
		$x = 0;
		$y = 0;
		$width = 0;
		$height = 0;
		$href = '';
		$transform = '';
		$r = 0;
		/*foreach($imageNode->attributes() as $name => $value){
			switch($name){
				case 'x': $x = $value; break;
				case 'y': $y = $value; break;
				case 'width': $width = $value; break;
				case 'height': $height = $value; break;
				case 'href':
				case 'xlink:href':$href = $value; break;
				//case 'r' : $r = $value; break; // no, use transform instead !
				case 'transform': $transform = $value;
				case 'style' : 
					$allStyle = split('[;:]', $value);
					$i = 0;
					while ($i < count($allStyle)) {
						if($allStyle[$i] == 'display' && $allStyle[$i+1] == 'none') return;
						if($allStyle[$i] == 'fill') $fill = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke') $stroke = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke-width') $strokeWidth = $allStyle[$i+1]; 
						$i=$i+2;
					}
					//if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; 
					break;
			}
		}*/
		extract($this->_getParams($imageNode));
		if($transform != ''){
			$transforms = split('[()]', $transform);
			$nb = count($transforms);
			for($i = 0; $i < $nb; $i++){
				// rotation
				if($transforms[$i] == 'rotate'){
					$rotinfo = $transforms[$i+1];
					$rotinfo = explode(' ', $rotinfo);
					$r = $rotinfo[0];
				}
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
				if($newImage = imagerotate($newImage, - floatval($r), -1)){
					$this->_log->message('Rotating image');
				}else
					$this->_log->error('Rotating image');		
			}else{
				$newImage = imagerotate($newImage, - floatval($r), -1);
			}
			$blue = imagecolorallocate($newImage, $this->transparentColor[0], $this->transparentColor[1],$this->transparentColor[2]);
			imagecolortransparent($newImage, $blue);
		}
		$newWidth = imagesx($newImage);
		$newHeight = imagesy($newImage);

		imagecopy($this->_image,$newImage,($newWidth == $width) ? $x : $x-($newWidth-$width)/2,($newHeight == $height) ? $y : $y-($newHeight-$height)/2,0,0,imagesx($newImage) , imagesy($newImage)); // Thanks Raphael & GD for saying things wrong.
	}

	/*
	 * Check if the given SVG xml is W3C valid
	 * DEPRECATED and unused anymore
	 * @param string <svg>...</svg>
	 * @return boolean
	 */
	private function _pathIsW3C($path){
		if(strripos($path, ','))
			return true;
		return false;
	}
	
	/*
	 * small function to find int into a string - works like java parseint
	 * @param string containing numbers
	 * @return int
	 */
	private function _parseInt($string){
		if(preg_match('/[-]?(\d+)/', $string, $array)) {
		//if(preg_match('/[-]?(\d+)/', $string, $array)) {
			return $array[0];
			//return $array[1];
		} else {
			return 0;
		}
	}
	
	/*
	 * add a line to the final image
	 * @param $x1, $y1, $x2, $y2 int position of segment
	 * @param imagecolorallocate color (via _allocatecolor !)
	 * @return imageline
	 */
	private function _drawLine($x1, $y1, $x2, $y2, $color){
		if(!imageline( $this->_image , $x1, $y1, $x2, $y2, $color )){
			if($this->_debug) $this->_log->error('Chemin erroné : '.$x1.' - '.$y1.' - '.$x2.' - '.$y2);
		}else{
			if($this->_debug) $this->_log->message('Chemin : '.$x1.' - '.$y1.' - '.$x2.' - '.$y2);
		}
	}
	
	/*
	 * add a curve to the final image
	 * @param $startX, $startY, $control1X, $control1Y, $control2X, $control2Y, $endX, $endY int position of start, controls and end points
	 * @param imagecolorallocate color (via _allocatecolor !)
	 * @return lots of imagesetpixel
	 * Algorithme de http://www.dreamstube.com/post/Bezier-Curves-In-PHP!.aspx
	 */
	private function _drawCurve($startX, $startY, $control1X, $control1Y, $control2X, $control2Y, $endX, $endY, $color){
		$cx=3*($control1X-$startX);
		$bx=3*($control2X-$control1X)-$cx;
		$ax=$endX-$startX-$cx-$bx;
		
		$cy=3*($control1Y-$startY);
		$by=3*($control2Y-$control1Y)-$cy;
		$ay=$endY-$startY-$cy-$by;
		//if($this->_debug) $this->_log->message('ax : '.$ax.', ay : '.$ay);
		for($t=0; $t<1; $t+=.01)
		{
			$xt = $ax * $t * $t * $t + $bx * $t * $t + $cx * $t + $startX;
			$yt = $ay * $t * $t * $t + $by * $t * $t + $cy * $t + $startY;
			imagesetpixel ( $this->_image , $xt , $yt , $color );
		}
	}
	
	
	/*EXPERIMENT*/
	
	// Calculate the coordinate of the Bezier curve at $t = 0..1
	private function _Bezier_eval($p1,$p2,$p3,$p4,$t) {
		 // lines between successive pairs of points (degree 1)
		$q1  = array((1-$t) * $p1[0] + $t * $p2[0],(1-$t) * $p1[1] + $t * $p2[1]);
		$q2  = array((1-$t) * $p2[0] + $t * $p3[0],(1-$t) * $p2[1] + $t * $p3[1]);
		$q3  = array((1-$t) * $p3[0] + $t * $p4[0],(1-$t) * $p3[1] + $t * $p4[1]);
		// curves between successive pairs of lines. (degree 2)
		$r1  = array((1-$t) * $q1[0] + $t * $q2[0],(1-$t) * $q1[1] + $t * $q2[1]);
		$r2  = array((1-$t) * $q2[0] + $t * $q3[0],(1-$t) * $q2[1] + $t * $q3[1]);
		// final curve between the two 2-degree curves. (degree 3)
		return array((1-$t) * $r1[0] + $t * $r2[0],(1-$t) * $r1[1] + $t * $r2[1]);
	}

	// Calculate the squared distance between two points
	private function _Point_distance2($p1,$p2) {
		$dx = $p2[0] - $p1[0];
		$dy = $p2[1] - $p1[1];
		return $dx * $dx + $dy * $dy;
	}

	// Convert the curve to a polyline
	private function _Bezier_convert($p1,$p2,$p3,$p4,$tolerance) {
		$t1 = 0.0;
		$prev = $p1;
		$t2 = 0.1;
		$tol2 = $tolerance * $tolerance;
		$result []= $prev[0];
		$result []= $prev[1];
		while ($t1 < 1.0) {
			if ($t2 > 1.0) {
				$t2 = 1.0;
			}
			$next = $this->_Bezier_eval($p1,$p2,$p3,$p4,$t2);
			$dist = $this->_Point_distance2($prev,$next);
			while ($dist > $tol2) {
				// Halve the distance until small enough
				$t2 = $t1 + ($t2 - $t1) * 0.5;
				$next = $this->_Bezier_eval($p1,$p2,$p3,$p4,$t2);
				$dist = $this->_Point_distance2($prev,$next);
			}
			// the image*polygon functions expect a flattened array of coordiantes
			$result []= $next[0];
			$result []= $next[1];
			$t1 = $t2;
			$prev = $next;
			$t2 = $t1 + 0.1;
		}
		return $result;
	}

	// Draw a Bezier curve on an image
	private function _Bezier_drawfilled($p1,$p2,$p3,$p4,$color) {
		$polygon = $this->_Bezier_convert($p1,$p2,$p3,$p4,0.1);
	//	if($this->_debug) $this->_log->message('polygon : '.implode(' - ',  $polygon));
	//	imagefilledpolygon($this->_image,$polygon,count($polygon)/2,$color);
	// test
		return $polygon;
	}
	/*END OF EXPERIMENT*/
	
	private function _drawPolygon($polygon, $stroke, $fill = ''){
		if($fill != '' && count($polygon) > 6){
			imagefilledpolygon($this->_image,$polygon,count($polygon)/2,$colorFill);
		}elseif(count($polygon) > 6){
			imagepolygon($this->_image, $polygon, count($polygon)/2, IMG_COLOR_STYLED);
		}
	}
	
	
	/*
	 * add path/lineS/polyline whatever you name it.
	 * @param simpleXMLElement
	 * @return lines on the final image via _drawLine
	 */
	private function _parsePath($pathNode){
		$path = '';
		$strokeWidth = 1;
		$fill = '';
		$stroke = '';
		$strokeDasharray = '';
		/*foreach($pathNode->attributes() as $name=>$value){
			switch($name){
				case 'd': case 'points': $path = $value; break;
				case 'stroke': $stroke = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'stroke-dasharray' : $strokeDasharray = $value; break;
				$allStyle = split('[;:]', $value);
					$i = 0;
					while ($i < count($allStyle)) {
						if($allStyle[$i] == 'display' && $allStyle[$i+1] == 'none') return;
						if($allStyle[$i] == 'fill') $fill = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke') $stroke = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke-width') $strokeWidth = $allStyle[$i+1]; 
						$i=$i+2;
					}
					//if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; 
					break;
			}
		}*/
		extract($this->_getParams($pathNode));
		if(strtolower(substr($path, 0,1)) != 'm' && !is_numeric(substr($path, 0,1))){
			if($this->_debug) $this->_log->error('Mauvais path rencontré : '.$path);
			return;
		}

		$thickness = imagesetthickness( $this->_image , $this->_parseInt($strokeWidth) );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		else $this->_log->message('épaisseur du trait à : '.$this->_parseInt($strokeWidth));
		$colorStroke = $stroke != '' ? $this->_allocateColor((string)$stroke) : $this->_allocateColor('black');
		$colorFill = $fill != '' ? $this->_allocateColor((string)$fill) : '';
		//$colorFill = $fill != '' ? $this->_allocateColor((string)$fill) : $this->_allocateColor('black');
		if($strokeDasharray != ''){
			$strokeDasharray = explode(',', $strokeDasharray);
			imagesetstyle ( $this->_image , $this->_getDashedStroke($strokeDasharray[0], $strokeDasharray[1], $colorStroke ));
		}else
			imagesetstyle ( $this->_image , $this->_getDashedStroke(10, 0, $colorStroke ));
			
		$lastOpe = '';
		
		$pathArray = split('[ ,]', $path); 
		
		// Si le path est de format 'm 100 100 l 100 100 z' il faut recoller les morceaux
		if(array_key_exists(strtolower($pathArray[0]), $this->pathType)){
			$j = 0;
			do{
				if(array_key_exists(strtolower($pathArray[$j]), $this->pathType)){
					$pathArray[$j] = $pathArray[$j].$pathArray[$j+1];
					$pathArray[$j+1] = '~';
					$j++;
					$newNb = count($pathArray);
					for($k = $j; $k<=$newNb; $k++){
						$pathArray[$k] = $pathArray[$k+1];
					}
				}else{
					if($pathArray[$j] == '' || $pathArray[$j] == null)
						unset($pathArray[$j]);
					$j++;
				}
			}while(isset($pathArray[$j]));
			//if($this->_debug) $this->_log->message('Path reconstruit ! '.implode(', ',$pathArray));
		}
		
		
		$nbArray = count($pathArray);
		//$nbLine = (($nbArray-1)/2)-1;
		$polyPoints = array();
		$i = 0;
		$lastX = 0;
		$lastY = 0;
		$lastMX = 0;
		$lastMY = 0;
		while ($i < $nbArray) {
			// Changement de départ
			if(strtolower(substr($pathArray[$i], 0, 1)) == 'm'){
				if(isset($pathArray[$i-1])){
					$this->_drawPolygon($polyPoints, $colorStroke, $colorFill);
					$polyPoints = array();
					$lastX = 0;
					$lastY = 0;
					$lastMX = 0;
					$lastMY = 0;
				}
				$lastX = $this->_parseInt($pathArray[$i]);
				$lastMX = $this->_parseInt($pathArray[$i]);
				$lastY = $this->_parseInt($pathArray[$i+1]);
				$lastMY = $this->_parseInt($pathArray[$i+1]);
				$lastOpe = 'm';
				$i=$i+2;
			// Ligne
			}elseif(strtolower(substr($pathArray[$i], 0, 1)) == 'l' || (is_numeric($pathArray[$i]) && strtolower($lastOpe) == 'l')){
				if(substr($pathArray[$i], 0, 1) == 'L'){
					$newX = $this->_parseInt($pathArray[$i]);
					$newY = $this->_parseInt($pathArray[$i+1]);
				}else{
					$newX = $lastX + $this->_parseInt($pathArray[$i]);
					$newY = $lastY + $this->_parseInt($pathArray[$i+1]);
				}
				$polyPoints = array_merge($polyPoints,array($lastX, $lastY, $newX, $newY));
				//$this->_drawLine($lastX , $lastY , $newX , $newY , IMG_COLOR_STYLED);
				$lastOpe = 'l';
				$lastX = $newX;
				$lastY = $newY;
				$i=$i+2;
			// Ligne horizontale
			}elseif(strtolower(substr($pathArray[$i], 0, 1)) == 'h' || (is_numeric($pathArray[$i]) && strtolower($lastOpe) == 'h')){
				if(substr($pathArray[$i], 0, 1) == 'H'){
					$newX = $this->_parseInt($pathArray[$i]);
				}else{
					$newX = $lastX + $this->_parseInt($pathArray[$i]);
				}	
				//$this->_drawLine($lastX , $lastY , $newX , $lastY , IMG_COLOR_STYLED);
				$polyPoints = array_merge($polyPoints,array($lastX, $lastY, $newX, $newY));
				$lastOpe = 'h';
				$lastX = $newX;
				$i++;
			// Ligne verticale
			}elseif(strtolower(substr($pathArray[$i], 0, 1)) == 'v' || (is_numeric($pathArray[$i]) && strtolower($lastOpe) == 'v')){
				if(substr($pathArray[$i], 0, 1) == 'V'){
					$newY = $this->_parseInt($pathArray[$i]);
				}else{
					$newY = $lastY + $this->_parseInt($pathArray[$i]);
				}
				//$this->_drawLine($lastX , $lastY , $lastX , $newY , IMG_COLOR_STYLED);
				$polyPoints = array_merge($polyPoints,array($lastX, $lastY, $newX, $newY));
				$lastY = $newY;
				$lastOpe = 'v';
				$i++;
			// Courbe
			}elseif(strtolower(substr($pathArray[$i], 0, 1)) == 'c' || (is_numeric($pathArray[$i]) && strtolower($lastOpe) == 'c')){
				/*SPECIF !!! http://www.w3.org/TR/SVG/paths.html*/
				if(substr($pathArray[$i], 0, 1) == 'C'){
					$control1x = $this->_parseInt($pathArray[$i]);
					$control1y = $this->_parseInt($pathArray[$i+1]);
					$control2x = $this->_parseInt($pathArray[$i+2]);
					$control2y = $this->_parseInt($pathArray[$i+3]);
					$newX = $this->_parseInt($pathArray[$i+4]);
					$newY = $this->_parseInt($pathArray[$i+5]);
				}else{
					$control1x = $lastX + $this->_parseInt($pathArray[$i]);
					$control1y = $lastY + $this->_parseInt($pathArray[$i+1]);
					$control2x = $lastX + $this->_parseInt($pathArray[$i+2]);
					$control2y = $lastY + $this->_parseInt($pathArray[$i+3]);
					$newX = $lastX + $this->_parseInt($pathArray[$i+4]);
					$newY = $lastY + $this->_parseInt($pathArray[$i+5]);
				}
				
				/*EXPERIMENT $this->_drawCurve($lastX, $lastY, $control1x, $control1y, $control2x, $control2y, $newX, $newY, IMG_COLOR_STYLED);*/
				//if($fill != ''){
				$polyPoints = array_merge($polyPoints,$this->_Bezier_drawfilled(array($lastX, $lastY),array($control1x, $control1y),array($control2x, $control2y),array($newX, $newY),$colorFill));
				//}else{
				//	$this->_drawCurve($lastX, $lastY, $control1x, $control1y, $control2x, $control2y, $newX, $newY, IMG_COLOR_STYLED);
				//}
				
				$lastX = $newX;
				$lastY = $newY;
				$lastOpe = 'c';
				$i=$i+6;
			// Dernière ligne droite
			}elseif(strtolower(substr($pathArray[$i], 0, 1)) == 'z' || (is_numeric(substr($pathArray[$i], 0, 1)) && strtolower($lastOpe) == 'z')){
				if($lastOpe == 'z' && $this->_debug) $this->_log->error('2 bouclages dans une boucle'); 
				$polyPoints = array_merge($polyPoints,array($lastX, $lastY, $lastMX, $lastMY));
				//$this->_drawLine($lastX , $lastY , $lastMX , $lastMY , IMG_COLOR_STYLED);
				$lastOpe = 'z';
				$i++;
			// Polyline
			}else{ 
				$lastX = $this->_parseInt($pathArray[$i+2]);
				$lastY = $this->_parseInt($pathArray[$i+3]);
				$lastOpe = 'l'; // s'il n'a aucune lettre, c'est une polyline, donc des... lignes.
				$i=$i+2; 
			}
			//if($this->_debug) $this->_log->message('counter :'.$i);
		}
		
		$this->_drawPolygon($polyPoints, $colorStroke, $colorFill);
		
		imagecolordeallocate( $this->_image, $colorStroke);
		imagecolordeallocate( $this->_image, $colorFill);
		imagesetthickness ( $this->_image , 1 );
		imagesetstyle ( $this->_image , $this->_getDashedStroke(10, 0, $colorStroke ));
	}
	
	/*
	 * add a circle in the final image
	 * @param SimpleXMLElement
	 * @return 
	 */
	private function _parseCircle($circleNode){
		$x = 0;
		$y = 0;
		$r = 0;
		$strokeWidth = 1;
		$fill = '';
		$stroke = '';
		/*foreach($circleNode->attributes() as $name => $value){
			switch($name){
				case 'cx': $x = $this->_getSizeType($value); break;
				case 'cy': $y = $this->_getSizeType($value); break;
				case 'r': $r = $this->_getSizeType($value); break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
				case 'stroke-width' : $strokeWidth = $value; break;
			}
		}*/
		extract($this->_getParams($circleNode));
		if($r == 0)
			return;
		if($this->_debug) $this->_log->message('Cercle - x : '.$x.' - y : '.$y.' - rayon : '.$r.'-'.$colorStroke[2].' - épaisseur : '.$strokeWidth);
		
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
			
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		
		if($fill != '' || ($fill=='' && $stroke=='')){
			imagefilledarc($this->_image , $x , $y , $r*2 , $r*2 ,0,359.9, $colorFill, IMG_ARC_PIE );
			//imageellipse ($this->_image , $x , $y , $r*2 , $r*2, $colorStroke );
		}
		if($stroke !=''){
			imagearc($this->_image , $x , $y , $r*2 , $r*2,0,359.9, $colorStroke );			
		}
		imagecolordeallocate( $this->_image, $colorStroke);
		imagecolordeallocate( $this->_image, $colorFill);
		imagesetthickness ( $this->_image , 1 );
	}
	
	/*
	 * add text in the final image <text fill="#000000" x="541" y="258" transform="rotate(-0, 541, 258)" font-size="10" font-family="SansSerif" font-style="normal" font-weight="normal">0</text>
	 * @param SimpleXMLElement
	 * @return 
	 */
	private function _parseText($textNode){
		$x = 0;
		$y = 0;
		$r = 0;
		$strokeWidth = 1;
		$fill = '';
		$fontSize = 10;
		$fontFamily = 'SansSerif';
		$fontStyle = 'normal';
		$fontWeight = 'normal';
		/*foreach($textNode->attributes() as $name => $value){
			switch($name){
				case 'x': $x = $this->_getSizeType($value); break;
				case 'y': $y = $this->_getSizeType($value); break;
				//case 'r': $r = $value; break; // todo
				case 'fill': $fill = $value; break;
				case 'font-size': $fontSize = $value; break;
				case 'font-family': $fontFamily = $value; break;
				case 'font-style': $fontStyle = $value; break;
				case 'font-weight': $fontWeight = $value; break;
			}
		}*/
		extract($this->_getParams($textNode));
		if($textNode == '')
			return;
			
		$colorStroke = $this->_allocateColor((string)$fill);
		
		imagestring ( $this->_image , 2 , $x , $y , $textNode , $fill );
		
		imagecolordeallocate( $this->_image, $colorStroke);
	}
	
	/*
	 * add a rectangle to the final image
	 * @param simpleXMLElement
	 * @return a nice rectangle !
	 */
	private function _parseRectangle($rectNode){
		$x = 0;
		$y = 0;
		$width = 0;
		$height = 0;
		$r = 0;
		$fill = '';
		$stroke = '';
		$strokeWidth = 1;
		$strokeDasharray = '';
		/*foreach($rectNode->attributes() as $name => $value){
			switch($name){
				// imagesetstyle  (pour dotted, dashed etc)
				case 'x': $x = $this->_getSizeType($value); break;
				case 'y': $y = $this->_getSizeType($value); break;
				case 'r': $r = $this->_getSizeType($value); break;
				case 'width': $width = $this->_getSizeType($value); break;
				case 'height': $height = $this->_getSizeType($value); break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'stroke-dasharray' : $strokeDasharray = $value; break;
				//case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
				case 'style' : 
					$allStyle = split('[;:]', $value);
					$i = 0;
					while ($i < count($allStyle)) {
						if($allStyle[$i] == 'display' && $allStyle[$i+1] == 'none') return;
						if($allStyle[$i] == 'fill') $fill = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke') $stroke = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke-width') $strokeWidth = $allStyle[$i+1]; 
						$i=$i+2;
					}
					//if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; 
					break;
			}
		}*/
		extract($this->_getParams($rectNode));
		if($width == 0 || $height == 0)
			return;
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($strokeDasharray != ''){
			$strokeDasharray = explode(',', $strokeDasharray);
			imagesetstyle ( $this->_image , $this->_getDashedStroke($strokeDasharray[0], $strokeDasharray[1], $colorStroke ));
		}else
			imagesetstyle ( $this->_image , $this->_getDashedStroke(10, 0, $colorStroke ));
		
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		if($this->_debug) $this->_log->message('Rectangle - x : '.$x.' - y : '.$y.' - width : '.$width.' - height : '.$height.' - fill : '.$colorFill[0].'-'.$colorFill[1].'-'.$colorFill[2].' - stroke : '.$colorStroke[0].'-'.$colorStroke[1].'-'.$colorStroke[2]);
		if($fill != '' || ($fill=='' && $stroke=='')){
			imagefilledrectangle ($this->_image , $x , $y , $x+$width , $y+$height, $colorFill );
		}
		if($stroke != ''){
			imagerectangle($this->_image , $x , $y , $x+$width , $y+$height, IMG_COLOR_STYLED); 
		}
		imagecolordeallocate($this->_image,$colorStroke);
		imagecolordeallocate($this->_image,$colorFill);
		imagesetthickness ( $this->_image , 1 );
		imagesetstyle ( $this->_image , $this->_getDashedStroke(10, 0, $colorStroke ));
	}
	
	/*
	 * add a polygon in the final image
	 * @param simpleXMLElement
	 * @return po-po-po-polygon !
	 */
	private function _parsePolygon($polyNode){
		$points = '';
		$fill = '';
		$stroke = '';
		$strokeWidth = 1;
		/*foreach($polyNode->attributes() as $name => $value){
			switch($name){
				// imagesetstyle  (pour dotted, dashed etc)
				case 'points' : $points = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				$allStyle = split('[;:]', $value);
					$i = 0;
					while ($i < count($allStyle)) {
						if($allStyle[$i] == 'display' && $allStyle[$i+1] == 'none') return;
						if($allStyle[$i] == 'fill') $fill = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke') $stroke = $allStyle[$i+1]; 
						if($allStyle[$i] == 'stroke-width') $strokeWidth = $allStyle[$i+1]; 
						$i=$i+2;
					}
					//if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; 
					break;
			}
		}*/
		extract($this->_getParams($polyNode));
		if($points == '')
			return;
		$pointArray = split('[ ,]', $points);
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		
		if($fill != ''){
			imagefilledpolygon ($this->_image , $pointArray , count($pointArray)/2 , $colorFill );
		}
		if($stroke != ''){
			imagepolygon ( $this->_image , $pointArray , count($pointArray)/2 , $colorStroke );
		}
		imagecolordeallocate($this->_image,$colorStroke);
		imagecolordeallocate($this->_image,$colorFill);
		imagesetthickness ( $this->_image , 1 );
	}
	
	/*
	 * add the description text in the final image
	 * @param string the description
	 * @return boolean
	 */
	private function _parseDescription($desc){
		if($this->_debug) $this->_log->message('Ajout de la description : '.$desc);
		return imagestring ( $this->_image , 2, 10, $this->_getImageHeight()-20, $desc , imagecolorallocate($this->_image, 255, 255, 255));
	}
	
	/**
	 * @param node <g>
	 * get group attributes to pass it to children
	 * parse children
	 */
	private function _parseGroup($groupNode){
		$this->_currentOptions = $this->_getParams($groupNode);
		foreach($groupNode->children() as $element){
			$this->_chooseParse($element);
		}
	}
	
	/*
	 * select what to parse 
	 * @param simpleXMLElement
	 * @return the selected function 
	 */
	private function _chooseParse($element){
		if($element->getName() == 'image')
			$this->_parseImage($element);
		if($element->getName() == 'circle')
			$this->_parseCircle($element);
		if($element->getName() == 'rect')
			$this->_parseRectangle($element);
		if($element->getName() == 'path')
			$this->_parsePath($element);
		if($element->getName() == 'polygon')
			$this->_parsePolygon($element);
		if($element->getName() == 'polyline')
			$this->_parsePath($element);
		if($element->getName() == 'g')
			$this->_parseGroup($element);
		if($element->getName() == 'text')
			$this->_parseText($element);
		//if($element->getName() == 'defs')
		//	$this->_parseDefs($element);
		//if($element->getName() == 'title')
		//	$this->_parseTitle($element);
		if($element->getName() == 'desc' && $this->_showDesc)
			$this->_desc = $element;
	}

	/*
	 * parse everything, main function 
	 * @param string format of the ouput 'png' 'gif' jpg'
	 * @param string path where you want to save the file (with the final name), null will just show the image but not saved on server
	 * @return the image 
	 */
	public function toImage($format = 'png', $path = null){
		$writeDesc = null;
		$this->_image = imagecreatetruecolor($this->_getImageWidth(), $this->_getImageHeight());
		imagefilledrectangle($this->_image, 0, 0 , $this->_getImageWidth(), $this->_getImageHeight(), $this->_allocateColor('white'));
		imagealphablending($this->_image, true);
		//imageantialias($this->_image, true); // On ne peut pas gérer l'épaisseur des traits si l'antialiasing est activé... lol ?
		foreach($this->_svgXML->children() as $element){
			$this->_chooseParse($element);
			$this->_currentOptions = array();
		}
		if($this->_showDesc && $this->_desc != null) $this->_parseDescription($this->_desc);
		//imagefilter ( $this->_image , IMG_FILTER_SMOOTH, 6);
		switch($format){
			case 'gif' :
				header("Content-type: " . image_type_to_mime_type(IMAGETYPE_GIF));
				return imagegif($this->_image, $path);
			case 'jpg':
				header("Content-type: " . image_type_to_mime_type(IMAGETYPE_JPEG));
				return imagejpeg($this->_image, $path);
			case 'png' :
			default :
				header("Content-type: " . image_type_to_mime_type(IMAGETYPE_PNG));
				return imagepng($this->_image, $path);
		}
	}

}