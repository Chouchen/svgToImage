<?
// TODO
// prendre en compte l'opacité grâce à imagecolorallocatealpha ?
// pour les rectangles avec point ou tiret http://fr.php.net/manual/fr/function.imagesetstyle.php
// ajout de title

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
	private $transparentColor = array(0,0,255);
	public $_debug = true; // change to false to stop debug mode
	
	/* array of color names => hex color 
		because some svg creator uses them
		*/
	private $colors = array(
		'black'			=> '#000000',
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
		'grass green'	=> '#408080',
	);
	
	/*
	 * constructor
	 * parse the svg with simplexml
	 */
	public function __construct($svg){
		if($this->_debug) $this->_log = new Log('log.dat');
		if($this->_debug) $this->_log->message('Ouverture du fichier contentant : '.$svg);
		$this->_svgXML = simplexml_load_string($svg);
	}
	
	/*
	 * Construct with a file
	 * @param : string path to the file
	 * @return : instance of this class
	 */
	public static function load($file){
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
	
	/* return width and height from the SVG */
	private function _getImageSize(){
		$imageSize = array();
		$imageSize['width'] = $this->_svgXML->attributes()->width;
		$imageSize['height'] = $this->_svgXML->attributes()->height;
		if($this->_debug) $this->_log->message('taille de l\'image : largeur : '.$imageSize['width'].' - longueur : '.$imageSize['height']);
		return $imageSize;
	}
	
	/*
	 * @return int final image width
	 */
	private function _getImageWidth(){
		return isset($this->_width) ? $this->_width : $this->_svgXML->attributes()->width;
	}
	
	/*
	 * @return int final image height
	 */
	private function _getImageHeight(){
		return isset($this->_height) ? $this->_height : $this->_svgXML->attributes()->height;
	}
	
	/*
	 * @param string Color code (ie: #CCC , #FE4323, etc...)
	 * @return array with R | G | B
	 */
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
	
	/*
	 * Allocate color to the final image thanks to _parseColor (check if the color isn't spelled directly 'black')
	 * @param string color code
	 * @return imageallocate on the image
	 */
	private function _allocateColor($color){
		if($color != '' && array_key_exists(strtolower($color), $this->colors)){
			$arrayColor = $this->_parseColor($this->colors[$color]);
		}else{
			$arrayColor = $this->_parseColor($color);
		}
		return imagecolorallocate( $this->_image, $arrayColor[0], $arrayColor[1], $arrayColor[2] );
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
		foreach($imageNode->attributes() as $name => $value){
			switch($name){
				case 'x': $x = $value; break;
				case 'y': $y = $value; break;
				case 'width': $width = $value; break;
				case 'height': $height = $value; break;
				case 'href':
				case 'xlink:href':$href = $value; break;
				//case 'r' : $r = $value; break; // no, use transform instead !
				case 'transform': $transform = $value;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
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
		if(preg_match('/(\d+)/', $string, $array)) {
			return $array[1];
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
	 * add path/lineS/polyline whatever you name it.
	 * @param simpleXMLElement
	 * @return lines on the final image via _drawLine
	 */
	private function _parsePath($pathNode){
		// imagesetbrush
		// imagesetstyle  (pour dotted, dashed etc)
		$path = '';
		$strokeWidth = 1;
		$fill = '';
		$stroke = '';
		foreach($pathNode->attributes() as $name=>$value){
			switch($name){
				case 'd': case 'points': $path = $value; break;
				case 'stroke': $stroke = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if(substr($path, 0,1) != 'M' && !is_numeric(substr($path, 0,1))){
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

			}elseif(substr($pathArray[$i], 0, 1) == 'C'){
				
				$control1x = $this->_parseInt($pathArray[$i]);
				$control1y = $this->_parseInt($pathArray[$i+1]);
				$control2x = $this->_parseInt($pathArray[$i+2]);
				$control2y = $this->_parseInt($pathArray[$i+3]);
				$newX = $this->_parseInt($pathArray[$i+4]);
				$newY = $this->_parseInt($pathArray[$i+5]);
				
				// Algorithme de http://www.dreamstube.com/post/Bezier-Curves-In-PHP!.aspx ne fonctionne pas !
				$cx=3*($control1x-$lastX);
				$bx=3*($control2x-$control1x)-$cx;
				$ax=$newX-$lastX-$cx-$bx;
			
				$cy=3*($control1y-$lastY);
				$by=3*($control2y-$control1y)-$cy;
				$ay=$newY-$lastY-$cy-$by;
				if($this->_debug) $this->_log->message('ax : '.$ax.', ay : '.$ay);
				$function_x='('.$ax.')*$t*$t*$t+('.$bx.')*$t*$t+('.$cx.')*$t+'.$lastX;
				$function_y='('.$ay.')*$t*$t*$t+('.$by.')*$t*$t+('.$cy.')*$t+'.$lastY;
				$function_z=2;
				$j=0;
				for($t=0; $t<1; $t+=.01)
				{
					eval('$x_points[$j]=1*'.$function_x.';');
					eval('$y_points[$j]=1*'.$function_y.';');
					eval('$z_points[$j]=1*'.$function_z.';');
					if($this->_debug) $this->_log->message('cx : '.$x_points[$j].', cy : '.$y_points[$j]*(-1).' d: '.$z_points[$j]);
					imagearc($this->_image, $x_points[$j]+$this->_getImageWidth()/2, ($y_points[$j]*(-1))+$this->_getImageHeight()/2, $z_points[$j], $z_points[$j], 0, 360, $colorStroke);
					$j++;	
				}
				$lastX = $newX;
				$lastY = $newY;
				$i=$i+6;
			}elseif(is_numeric(substr($pathArray[$i], 0, 1))){
				switch($lastOpe){
					case 'L': 
						$newX = $this->_parseInt($pathArray[$i]);
						$newY = $this->_parseInt($pathArray[$i+1]);
						$this->_drawLine($lastX , $lastY , $newX , $newY , $colorStroke);
						$lastX = $newX;
						$lastY = $newY;
						$i=$i+2; 
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
					default : //polyline
						if($this->_debug) $this->_log->error('last opé inconnue '.$lastOpe); 
						$lastX = $this->_parseInt($pathArray[$i]);
						$lastY = $this->_parseInt($pathArray[$i+1]);
						$lastOpe = 'L';
						$i=$i+2; 
						break;
				}

			}elseif(substr($pathArray[$i], 0, 1) == 'Z'){
				$this->_drawLine($lastX , $lastY , $this->_parseInt($pathArray[0]) , $this->_parseInt($pathArray[1]) , $colorStroke);
				$lastOpe = 'Z'; //utile?
				$i++;
			}else 
				$i++; // au cas où pour éviter une boucle infinie.
			if($this->_debug) $this->_log->message('counter :'.$i);
		}
		imagecolordeallocate( $this->_image, $colorStroke);
		imagecolordeallocate( $this->_image, $colorFill);
		imagesetthickness ( $this->_image , 1 );
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
		foreach($rectNode->attributes() as $name => $value){
			switch($name){
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
			imagerectangle($this->_image , $x , $y , $x+$width , $y+$height, $colorStroke); 
		}else{
			imagefilledrectangle ($this->_image , $x , $y , $x+$width , $y+$height, $colorFill );
		}
		imagecolordeallocate($this->_image,$colorStroke);
		imagecolordeallocate($this->_image,$colorFill);
		imagesetthickness ( $this->_image , 1 );
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
		foreach($polyNode->attributes() as $name => $value){
			switch($name){
				// imagesetstyle  (pour dotted, dashed etc)
				case 'points' : $points = $value; break;
				case 'fill': $fill = ($value == 'none') ? '' : $value; break;
				case 'stroke': $stroke = $value; break;
				case 'stroke-width' : $strokeWidth = $value; break;
				case 'style' : if(strripos($value, 'display: none') || strripos($value, 'display:none')) return; break;
			}
		}
		if($points == '')
			return;
		$pointArray = split('[ ,]', $points);
		$colorStroke = $this->_allocateColor((string)$stroke);
		$colorFill = $this->_allocateColor((string)$fill);
		$thickness = imagesetthickness( $this->_image , (int)$strokeWidth );
		if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
		
		if($fill == ''){
			imagepolygon ( $this->_image , $pointArray , count($pointArray)/2 , $colorStroke );
		}else{
			imagefilledpolygon ($this->_image , $pointArray , count($pointArray)/2 , $colorFill );
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
	
	/*
	 * ignore group for the moment
	 * acts like ungrouped
	 */
	private function _parseGroup($groupNode){
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
		if($element->getName() == 'title')
			$this->_parseTitle($element);
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
		imagealphablending($this->_image, true);
		//imageantialias($this->_image, true); // On ne peut pas gérer l'épaisseur des traits si l'antialiasing est activé... lol ?
		foreach($this->_svgXML->children() as $element){
			$this->_chooseParse($element);
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