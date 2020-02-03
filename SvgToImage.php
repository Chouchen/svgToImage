<?php
// TODO
// prendre en compte l'opacité grâce à imagecolorallocatealpha ?
// ajout de title
// ajout de <text fill="#000000" x="541" y="258" transform="rotate(-0, 541, 258)" font-size="10" font-family="SansSerif" font-style="normal" font-weight="normal">0</text>

include 'Log.php';

class SvgToImage
{
    protected $_svgXML;
    protected $_image;
    protected $_log;
    protected $_x;
    protected $_y;
    protected $_width;
    protected $_height;
    protected $_showDesc = false;
    protected $_desc;
    protected $_currentOptions = [];
    private $transparentColor = [0, 0, 255];
    public $_debug = true; // change to false to stop debug mode

    /**
     * array of path type
     */
    private $pathType = [
        'm' => 'MoveTo',
        'l' => 'LineTo',
        'h' => 'HorizontalLineTo',
        'v' => 'VerticalLineTo',
        'c' => 'CurveTo',
        'z' => 'EndingLine',
    ];

    /**
     * array of color names => rgb color
     * because some svg creator uses them
     * used http://www.yoyodesign.org/doc/w3c/svg1/types.html#ColorKeywords
     */
    private $colors = [
        'aliceblue' => [240, 248, 255],
        'antiquewhite' => [250, 235, 215],
        'aqua' => [0, 255, 255],
        'aquamarine' => [127, 255, 212],
        'azure' => [240, 255, 255],
        'beige' => [245, 245, 220],
        'bisque' => [255, 228, 196],
        'black' => [0, 0, 0],
        'blanchedalmond' => [255, 235, 205],
        'blue' => [0, 0, 255],
        'blueviolet' => [138, 43, 226],
        'brown' => [165, 42, 42],
        'burlywood' => [222, 184, 135],
        'cadetblue' => [95, 158, 160],
        'chartreuse' => [127, 255, 0],
        'chocolate' => [210, 105, 30],
        'coral' => [255, 127, 80],
        'cornflowerblue' => [100, 149, 237],
        'cornsilk' => [255, 248, 220],
        'crimson' => [220, 20, 60],
        'cyan' => [0, 255, 255],
        'darkblue' => [0, 0, 139],
        'darkcyan' => [0, 139, 139],
        'darkgoldenrod' => [184, 134, 11],
        'darkgray' => [169, 169, 169],
        'darkgreen' => [0, 100, 0],
        'darkgrey' => [169, 169, 169],
        'darkkhaki' => [189, 183, 107],
        'darkmagenta' => [139, 0, 139],
        'darkolivegreen' => [85, 107, 47],
        'darkorange' => [255, 140, 0],
        'darkorchid' => [153, 50, 204],
        'darkred' => [139, 0, 0],
        'darksalmon' => [233, 150, 122],
        'darkseagreen' => [143, 188, 143],
        'darkslateblue' => [72, 61, 139],
        'darkslategray' => [47, 79, 79],
        'darkslategrey' => [47, 79, 79],
        'darkturquoise' => [0, 206, 209],
        'darkviolet' => [148, 0, 211],
        'deeppink' => [255, 20, 147],
        'deepskyblue' => [0, 191, 255],
        'dimgray' => [105, 105, 105],
        'dimgrey' => [105, 105, 105],
        'dodgerblue' => [30, 144, 255],
        'firebrick' => [178, 34, 34],
        'floralwhite' => [255, 250, 240],
        'forestgreen' => [34, 139, 34],
        'fuchsia' => [255, 0, 255],
        'gainsboro' => [220, 220, 220],
        'ghostwhite' => [248, 248, 255],
        'gold' => [255, 215, 0],
        'goldenrod' => [218, 165, 32],
        'gray' => [128, 128, 128],
        'grey' => [128, 128, 128],
        'green' => [0, 128, 0],
        'greenyellow' => [173, 255, 47],
        'honeydew' => [240, 255, 240],
        'hotpink' => [255, 105, 180],
        'indianred' => [205, 92, 92],
        'indigo' => [75, 0, 130],
        'ivory' => [255, 255, 240],
        'khaki' => [240, 230, 140],
        'lavender' => [230, 230, 250],
        'lavenderblush' => [255, 240, 245],
        'lawngreen' => [124, 252, 0],
        'lemonchiffon' => [255, 250, 205],
        'lightblue' => [173, 216, 230],
        'lightcoral' => [240, 128, 128],
        'lightcyan' => [224, 255, 255],
        'lightgoldenrodyellow' => [250, 250, 210],
        'lightgray' => [211, 211, 211],
        'lightgreen' => [144, 238, 144],
        'lightgrey' => [211, 211, 211],
        'lightpink' => [255, 182, 193],
        'lightsalmon' => [255, 160, 122],
        'lightseagreen' => [32, 178, 170],
        'lightskyblue' => [135, 206, 250],
        'lightslategray' => [119, 136, 153],
        'lightslategrey' => [119, 136, 153],
        'lightsteelblue' => [176, 196, 222],
        'lightyellow' => [255, 255, 224],
        'lime' => [0, 255, 0],
        'limegreen' => [50, 205, 50],
        'linen' => [250, 240, 230],
        'magenta' => [255, 0, 255],
        'maroon' => [128, 0, 0],
        'mediumaquamarine' => [102, 205, 170],
        'mediumblue' => [0, 0, 205],
        'mediumorchid' => [186, 85, 211],
        'mediumpurple' => [147, 112, 219],
        'mediumseagreen' => [60, 179, 113],
        'mediumslateblue' => [123, 104, 238],
        'mediumspringgreen' => [0, 250, 154],
        'mediumturquoise' => [72, 209, 204],
        'mediumvioletred' => [199, 21, 133],
        'midnightblue' => [25, 25, 112],
        'mintcream' => [245, 255, 250],
        'mistyrose' => [255, 228, 225],
        'moccasin' => [255, 228, 181],
        'navajowhite' => [255, 222, 173],
        'navy' => [0, 0, 128],
        'oldlace' => [253, 245, 230],
        'olive' => [128, 128, 0],
        'olivedrab' => [107, 142, 35],
        'orange' => [255, 165, 0],
        'orangered' => [255, 69, 0],
        'orchid' => [218, 112, 214],
        'palegoldenrod' => [238, 232, 170],
        'palegreen' => [152, 251, 152],
        'paleturquoise' => [175, 238, 238],
        'palevioletred' => [219, 112, 147],
        'papayawhip' => [255, 239, 213],
        'peachpuff' => [255, 218, 185],
        'peru' => [205, 133, 63],
        'pink' => [255, 192, 203],
        'plum' => [221, 160, 221],
        'powderblue' => [176, 224, 230],
        'purple' => [128, 0, 128],
        'red' => [255, 0, 0],
        'rosybrown' => [188, 143, 143],
        'royalblue' => [65, 105, 225],
        'saddlebrown' => [139, 69, 19],
        'salmon' => [250, 128, 114],
        'sandybrown' => [244, 164, 96],
        'seagreen' => [46, 139, 87],
        'seashell' => [255, 245, 238],
        'sienna' => [160, 82, 45],
        'silver' => [192, 192, 192],
        'skyblue' => [135, 206, 235],
        'slateblue' => [106, 90, 205],
        'slategray' => [112, 128, 144],
        'slategrey' => [112, 128, 144],
        'snow' => [255, 250, 250],
        'springgreen' => [0, 255, 127],
        'steelblue' => [70, 130, 180],
        'tan' => [210, 180, 140],
        'teal' => [0, 128, 128],
        'thistle' => [216, 191, 216],
        'tomato' => [255, 99, 71],
        'turquoise' => [64, 224, 208],
        'violet' => [238, 130, 238],
        'wheat' => [245, 222, 179],
        'white' => [255, 255, 255],
        'whitesmoke' => [245, 245, 245],
        'yellow' => [255, 255, 0],
        'yellowgreen' => [154, 205, 50]
    ];

    /**
     * constructor
     * parse the svg with simplexml
     * @param string $svg SVG as string
     */
    public function __construct($svg)
    {
        if ($this->_debug) {
            $this->_log = new Log('log.dat');
        }
        $this->_svgXML = simplexml_load_string($svg);
    }

    /**
     * Construct with a file
     * @param string path to the file
     * @return SVGTOIMAGE instance of this class
     */
    public static function load($file)
    {
        $svg = file_get_contents($file);
        return new SVGTOIMAGE($svg);
    }

    /**
     * Construct with a string
     * @param string <svg>...</svg>
     * @return SVGTOIMAGE instance of this class
     */
    public static function parse($xml)
    {
        return new SVGTOIMAGE($xml);
    }

    /**
     * Destroy the GD Image when finished
     */
    public function __destruct()
    {
        imagedestroy($this->_image);
    }

    /**
     * setter - option : show the description from the svg into the image if present
     * @param boolean
     */
    public function setShowDesc($showDesc = true)
    {
        if (is_bool($showDesc)) {
            //if($this->_debug) $this->_log->message('Passage de showDesc en '.$showDesc);
            $this->_showDesc = $showDesc;
        } else if ($this->_debug) {
            $this->_log->error('Erreur dans la fonction showDesc, doit recevoir booléen, a reçu : ' . $showDesc);
        }
    }

    /**
     * setter - option : origin of the final image from the svg (default : 0)
     * @param int
     */
    public function setX($x)
    {
        if (is_int($x)) {
            //if($this->_debug) $this->_log->message('Passage de x en '.$x);
            $this->_x = $x;
        } elseif ($this->_debug) {
            $this->_log->error('Erreur dans la fonction setX, doit recevoir int, a reçu : ' . $x);
        }
    }

    /**
     * setter - option : origin of the final image from the svg (default : 0)
     * @param int
     */
    public function setY($y)
    {
        if (is_int($y)) {
            //if($this->_debug) $this->_log->message('Passage de y en '.$y);
            $this->_y = $y;
        } elseif ($this->_debug) {
            $this->_log->error('Erreur dans la fonction setY, doit recevoir int, a reçu : ' . $y);
        }
    }

    /**
     * setter - option : width of the final image (default : svg width)
     * @param int
     */
    public function setWidth($width)
    {
        if (is_int($width)) {
            //if($this->_debug) $this->_log->message('Passage de width en '.$width);
            $this->_width = $width;
        } elseif ($this->_debug) {
            $this->_log->error('Erreur dans la fonction setWidth, doit recevoir int, a reçu : ' . $width);
        }
    }

    /**
     * setter - option : height of the final image (default : svg height)
     * @param int
     */
    public function setHeight($height)
    {
        if (is_int($height)) {
            //if($this->_debug) $this->_log->message('Passage de height en '.$height);
            $this->_height = $height;
        } elseif ($this->_debug) {
            $this->_log->error('Erreur dans la fonction setHeight, doit recevoir int, a reçu : ' . $height);
        }
    }

    /**
     * @param string $size size of picture or element
     * @return int "real" size of element. To eliminate SVG with centimeters
     */
    private function _getSizeType($size)
    {
        $size = rtrim($size);
        $unit = substr($size, -2, 2);
        $value = (int)substr($size, 0, -2);
        switch ($unit) {
            case 'cm':
                return $value * 30; // approximatively
                break;
            case 'in':
                return $value * 12; // approximatively
                break;
            case 'px':
            case 'pt':
                return $value;
            default:
                return (int) $size;
        }
    }

    /**
     * @return int final image width
     */
    private function _getImageWidth()
    {
        return isset($this->_width) ? $this->_width : $this->_getSizeType($this->_svgXML->attributes()->width);
    }

    /**
     * @return int final image height
     */
    private function _getImageHeight()
    {
        return isset($this->_height) ? $this->_height : $this->_getSizeType($this->_svgXML->attributes()->height);
    }

    /**
     * @param string Color code (ie: #CCC , #FE4323, etc...)
     * @return array with R | G | B
     */
    private function _parseColor($colorCode)
    {
        if ($colorCode instanceof SimpleXMLElement) {
            $colorCode = (string) $colorCode;
        }
        if (is_string($colorCode) && strlen($colorCode) === 7) {
            return [
                base_convert(substr($colorCode, 1, 2), 16, 10),
                base_convert(substr($colorCode, 3, 2), 16, 10),
                base_convert(substr($colorCode, 5, 2), 16, 10),
            ];
        }
        if (is_string($colorCode) && strlen($colorCode) === 4) {
            return [
                base_convert($colorCode[1] . $colorCode[1], 16, 10),
                base_convert($colorCode[2] . $colorCode[2], 16, 10),
                base_convert($colorCode[3] . $colorCode[3], 16, 10),
            ];
        }
        if (is_array($colorCode) && count($colorCode) === 3) {
            return $colorCode;
        }
        if ($this->_debug) {
            $this->_log->error('Couleur mal indiquée ' . Log::decode($colorCode));
        }
        return [0, 0, 0]; // !#FFF || !#FFFFFF || !array(255,255,255) then black
    }

    /**
     * Allocate color to the final image thanks to _parseColor (check if the color isn't spelled directly 'black')
     * @param string color code
     * @return false|int|void on the image
     */
    private function _allocateColor($color)
    {
        if ($color !== '') {
            if (array_key_exists(strtolower($color), $this->colors)) {
                $arrayColor = $this->_parseColor($this->colors[strtolower($color)]);
            } else {
                $arrayColor = $this->_parseColor($color);
            }
            return imagecolorallocate($this->_image, $arrayColor[0], $arrayColor[1], $arrayColor[2]);
        }

        return null;
    }

    /**
     * return an array to use with imagesetstyle
     * @param int $full
     * @param int $empty
     * @param $color
     * @return array
     */
    private function _getDashedStroke($full, $empty, $color)
    {
        $tiret = [];
        for ($i = 0; $i < $full; $i++) {
            $tiret[] = $color;
        }
        for ($i = 0; $i < $empty; $i++) {
            $tiret[] = IMG_COLOR_TRANSPARENT;
        }
        //if($this->_debug) $this->_log->message('nouveaux tirets : '.Log::decode($tiret));
        return $tiret;
    }

    /**
     * @param $paramName
     * @return mixed|null
     */
    private function _getParam($paramName)
    {
        $currentOptions = $this->_getAllParams();
        return isset($currentOptions[$paramName]) ? $currentOptions[$paramName] : null;
    }

    /**
     * @return array
     */
    private function _getAllParams()
    {
        $newarr = [];
        foreach ($this->_currentOptions as $array) {
            $newarr = array_merge($newarr, $array);
        }
        return $newarr;
    }

    /**
     * @param SimpleXMLElement $element
     * @return array options
     */
    private function _getParams($element)
    {
        $options = $this->_getAllParams();
        foreach ($element->attributes() as $name => $value) {
            switch ($name) {
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
                case 'd':
                case 'points':
                    $options['path'] = (string)$value;
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
                case 'transform': // transform="matrix(1.006896,0,0,1.006896,0.3043,-0.708342)"
                    $transform = preg_split('/[()]/', $value);
                    if (count($transform) === 3) {
                        $typeTransform = $transform[0];
                        switch ($typeTransform) {
                            case 'translate':
                                list($options['originX'], $options['originY']) = explode(',', $transform[1]);
                                break;
                            case 'rotate':
                                $options['rotate'] = $transform[1];
                                break;
                            case 'scale':
                                $options['scale'] = $transform[1];
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case 'style' :
                    $allStyle = preg_split('/[;:]/', $value);
                    $i = 0;
                    while ($i < count($allStyle)) {
                        if ($allStyle[$i] === 'display' && $allStyle[$i + 1] === 'none') {
                            // display:none? Stop looking for info
                            return null;
                        }
                        if ($allStyle[$i] === 'fill') {
                            $options['fill'] = $allStyle[$i + 1];
                        }
                        if ($allStyle[$i] === 'stroke') {
                            $options['stroke'] = $allStyle[$i + 1];
                        }
                        if ($allStyle[$i] === 'stroke-width') {
                            $options['strokeWidth'] = $allStyle[$i + 1];
                        }
                        $i += 2;
                    }
                    break;
                default:
                    $options[$name] = $value;
                    break;
            }
        }
        return $options;
    }

    /**
     * add the given image from svg to the final image
     * @param simpleXMLElement
     * @return bool
     */
    private function _parseImage($imageNode)
    {
        $x = 0;
        $y = 0;
        $width = 0;
        $height = 0;
        $href = '';
        $transform = '';
        $r = 0;
        extract($this->_getParams($imageNode), EXTR_OVERWRITE);
        //case translate
        if ($this->_getParam('originX') !== null) {
            $x += $this->_getParam('originX');
        }
        if ($this->_getParam('originY') !== null) {
            $y += $this->_getParam('originY');
        }
        //end translate
        if ($transform !== '') {
            $transforms = preg_split('/[()]/', $transform);
            foreach ($transforms as $i => $iValue) {
                // rotation
                if ($iValue === 'rotate') {
                    $rotinfo = $transforms[$i + 1];
                    $rotinfo = explode(' ', $rotinfo);
                    $r = $rotinfo[0];
                }
            }
        }
        if ($width === 0 || $height === 0 || $href === '') {
            return null;
        }

        $imageTypeArray = explode('.', $href);
        $lastElementFromImageType = count($imageTypeArray);
        $imageType = $imageTypeArray[$lastElementFromImageType - 1];
        if ($imageType === 'jpg' || $imageType === 'jpeg') {
            $newImage = imagecreatefromjpeg((string)$href);
        } elseif ($imageType === 'png') {
            $newImage = imagecreatefrompng((string)$href);
        } elseif ($imageType === 'gif') {
            $newImage = imagecreatefromgif((string)$href);
        } else {
            return null;
        }
        if (false === $newImage) {
            return null;
        }

        imagealphablending($newImage, true);

        //rotating the image if needed
        if ($r !== 0) {
            if ($this->_debug) {
                if ($newImage = imagerotate($newImage, -(float)$r, -1)) {
                    $this->_log->message('Rotating image');
                } else {
                    $this->_log->error('Rotating image');
                }
            } else {
                $newImage = imagerotate($newImage, -(float)$r, -1);
            }
            if (false === $newImage) {
                return null;
            }
            $blue = imagecolorallocate($newImage, $this->transparentColor[0], $this->transparentColor[1], $this->transparentColor[2]);
            imagecolortransparent($newImage, $blue);
        }
        $newWidth = imagesx($newImage);
        $newHeight = imagesy($newImage);

        return imagecopy($this->_image, $newImage, ($newWidth === $width) ? $x : $x - ($newWidth - $width) / 2, ($newHeight === $height) ? $y : $y - ($newHeight - $height) / 2, 0, 0, imagesx($newImage), imagesy($newImage)); // Thanks Raphael & GD for saying things wrong.
    }

    /**
     * small function to find int into a string - works like java parseint
     * @param string containing numbers
     * @return int
     */
    private function _parseInt($string)
    {
        if (preg_match('/[-]?(\d+)/', $string, $array)) {
            return $array[0];
        }

        return 0;
    }

    /**
     * add a line to the final image
     * @param $x1 int position of segment
     * @param $y1 int
     * @param $x2 int
     * @param $y2 int
     * @param $color
     * @return bool
     */
    private function _drawLine($x1, $y1, $x2, $y2, $color)
    {
        if (!imageline($this->_image, $x1, $y1, $x2, $y2, $color)) {
            if ($this->_debug) {
                $this->_log->error('Chemin erroné : ' . $x1 . ' - ' . $y1 . ' - ' . $x2 . ' - ' . $y2);
            }
            return false;
        }

        if ($this->_debug) {
            $this->_log->message('Chemin : ' . $x1 . ' - ' . $y1 . ' - ' . $x2 . ' - ' . $y2);
        }

        return true;
    }

    /**
     * add a curve to the final image
     * @param $startX int position of start, controls and end points
     * @param $startY int
     * @param $control1X int
     * @param $control1Y int
     * @param $control2X int
     * @param $control2Y int
     * @param $endX int
     * @param $endY int
     * @param $color
     */
    private function _drawCurve($startX, $startY, $control1X, $control1Y, $control2X, $control2Y, $endX, $endY, $color)
    {
        $cx = 3 * ($control1X - $startX);
        $bx = 3 * ($control2X - $control1X) - $cx;
        $ax = $endX - $startX - $cx - $bx;

        $cy = 3 * ($control1Y - $startY);
        $by = 3 * ($control2Y - $control1Y) - $cy;
        $ay = $endY - $startY - $cy - $by;
        //if($this->_debug) $this->_log->message('ax : '.$ax.', ay : '.$ay);
        for ($t = 0; $t < 1; $t += .01) {
            $xt = $ax * $t * $t * $t + $bx * $t * $t + $cx * $t + $startX;
            $yt = $ay * $t * $t * $t + $by * $t * $t + $cy * $t + $startY;
            imagesetpixel($this->_image, $xt, $yt, $color);
        }
    }


    /*EXPERIMENTS*/

    /**
     * Calculate the coordinate of the Bezier curve at $t = 0..1
     *
     * @param $p1
     * @param $p2
     * @param $p3
     * @param $p4
     * @param $t
     * @return array
     */
    private function _Bezier_eval($p1, $p2, $p3, $p4, $t)
    {
        // lines between successive pairs of points (degree 1)
        $q1 = array((1 - $t) * $p1[0] + $t * $p2[0], (1 - $t) * $p1[1] + $t * $p2[1]);
        $q2 = array((1 - $t) * $p2[0] + $t * $p3[0], (1 - $t) * $p2[1] + $t * $p3[1]);
        $q3 = array((1 - $t) * $p3[0] + $t * $p4[0], (1 - $t) * $p3[1] + $t * $p4[1]);
        // curves between successive pairs of lines. (degree 2)
        $r1 = array((1 - $t) * $q1[0] + $t * $q2[0], (1 - $t) * $q1[1] + $t * $q2[1]);
        $r2 = array((1 - $t) * $q2[0] + $t * $q3[0], (1 - $t) * $q2[1] + $t * $q3[1]);
        // final curve between the two 2-degree curves. (degree 3)
        return array((1 - $t) * $r1[0] + $t * $r2[0], (1 - $t) * $r1[1] + $t * $r2[1]);
    }

    /**
     * Calculate the squared distance between two points
     *
     * @param $p1
     * @param $p2
     * @return float|int
     */
    private function _Point_distance2($p1, $p2)
    {
        $dx = $p2[0] - $p1[0];
        $dy = $p2[1] - $p1[1];
        return $dx * $dx + $dy * $dy;
    }

    /**
     * Convert the curve to a polyline
     *
     * @param $p1
     * @param $p2
     * @param $p3
     * @param $p4
     * @param $tolerance
     * @return array
     */
    private function _Bezier_convert($p1, $p2, $p3, $p4, $tolerance)
    {
        $t1 = 0.0;
        $prev = $p1;
        $t2 = 0.1;
        $tol2 = $tolerance * $tolerance;
        $result [] = $prev[0];
        $result [] = $prev[1];
        while ($t1 < 1.0) {
            if ($t2 > 1.0) {
                $t2 = 1.0;
            }
            $next = $this->_Bezier_eval($p1, $p2, $p3, $p4, $t2);
            $dist = $this->_Point_distance2($prev, $next);
            while ($dist > $tol2) {
                // Halve the distance until small enough
                $t2 = $t1 + ($t2 - $t1) * 0.5;
                $next = $this->_Bezier_eval($p1, $p2, $p3, $p4, $t2);
                $dist = $this->_Point_distance2($prev, $next);
            }
            // the image*polygon functions expect a flattened array of coordiantes
            $result [] = $next[0];
            $result [] = $next[1];
            $t1 = $t2;
            $prev = $next;
            $t2 = $t1 + 0.1;
        }
        return $result;
    }

    /**
     * Draw a Bezier curve on an image
     *
     * @param $p1
     * @param $p2
     * @param $p3
     * @param $p4
     * @param $color
     * @return array
     */
    private function _Bezier_drawfilled($p1, $p2, $p3, $p4, $color)
    {
        return $this->_Bezier_convert($p1, $p2, $p3, $p4, 0.1);
    }

    /*END OF EXPERIMENT*/

    /**
     * @param $polygon
     * @param string $stroke
     * @param string $fill
     */
    private function _drawPolygon($polygon, $stroke = '', $fill = '')
    {
        //if($this->_debug) $this->_log->message('_drawPolygon : fill : '.$fill.' stroke:'.$stroke);
        if ($fill !== '' && count($polygon) >= 6) {
            //if($this->_debug) $this->_log->message('polygon rempli : '.$fill);
            imagefilledpolygon($this->_image, $polygon, count($polygon) / 2, $fill);
            if ($stroke !== '') {
                imagepolygon($this->_image, $polygon, count($polygon) / 2, $stroke);
            }
        } elseif (count($polygon) >= 6) {
            //if($this->_debug) $this->_log->message('polygon non rempli : '.$stroke);
            imagepolygon($this->_image, $polygon, count($polygon) / 2, $stroke);
            //imagepolygon($this->_image, $polygon, count($polygon)/2, IMG_COLOR_STYLED);
        } elseif (count($polygon) === 4) {
            //if($this->_debug) $this->_log->message('ligne via polygon : '.$stroke);
            $this->_drawLine($polygon[0], $polygon[1], $polygon[2], $polygon[3], $stroke);
        }
    }


    /**
     * add path/lineS/polyline whatever you name it.
     * @param simpleXMLElement
     * @return null
     */
    private function _parsePath($pathNode)
    {
        $path = '';
        $strokeWidth = 1;
        $fill = '';
        $stroke = '';
        $strokeDasharray = '';

        extract($this->_getParams($pathNode), EXTR_OVERWRITE);

        if (stripos($path, 'm') !== 0 && !is_numeric($path[0])) {
            if ($this->_debug) {
                $this->_log->error('Mauvais path rencontré : ' . $path);
            }
            return null;
        }

        $thickness = imagesetthickness($this->_image, $this->_parseInt($strokeWidth));
        if ($this->_debug && !$thickness) {
            $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
        } else {
            $this->_log->message('épaisseur du trait à : ' . $this->_parseInt($strokeWidth));
        }

        $colorStroke = $stroke !== '' ? $this->_allocateColor($stroke) : ($fill === '' ? $this->_allocateColor('black') : '');
        $colorFill = $fill !== '' ? $this->_allocateColor($fill) : '';

        if ($this->_debug) {
            $this->_log->message('colors ! fill:' . $colorFill . 'stroke:' . $colorStroke);
        }

        if ($strokeDasharray !== '') {
            $strokeDasharray = explode(',', $strokeDasharray);
            imagesetstyle($this->_image, $this->_getDashedStroke($strokeDasharray[0], $strokeDasharray[1], $colorStroke));
        } else {
            imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));
        }

        $lastOpe = '';

        $pathArray = preg_split('/[ ,]/', $path);

        // Si le path est de format 'm 100 100 l 100 100 z' il faut recoller les morceaux
        if (array_key_exists(strtolower($pathArray[0]), $this->pathType)) {
            $j = 0;
            do {
                if (array_key_exists(strtolower($pathArray[$j]), $this->pathType)) {
                    $pathArray[$j] .= $pathArray[$j + 1];
                    $pathArray[$j + 1] = '~';
                    $j++;
                    $newNb = count($pathArray);
                    for ($k = $j; $k <= $newNb; $k++) {
                        $pathArray[$k] = $pathArray[$k + 1];
                    }
                } else {
                    if ($pathArray[$j] === '' || $pathArray[$j] === null) {
                        unset($pathArray[$j]);
                    }
                    $j++;
                }
            } while (isset($pathArray[$j]));
            //if($this->_debug) $this->_log->message('Path reconstruit ! '.implode(', ',$pathArray));
        }

        $nbArray = count($pathArray);
        $polyPoints = [];
        $i = 0;
        $lastX = 0;
        $lastY = 0;
        $lastMX = 0;
        $lastMY = 0;
        while ($i < $nbArray) {
            // Changement de départ
            if (stripos($pathArray[$i], 'm') === 0) {
                if (isset($pathArray[$i - 1])) {
                    $this->_drawPolygon($polyPoints, $colorStroke, $colorFill);
                    $polyPoints = [];
                }
                $lastX = $this->_parseInt($pathArray[$i]);
                $lastMX = $this->_parseInt($pathArray[$i]);
                $lastY = $this->_parseInt($pathArray[$i + 1]);
                $lastMY = $this->_parseInt($pathArray[$i + 1]);
                $lastOpe = 'm';
                $i += 2;
                // Ligne
            } elseif (stripos($pathArray[$i], 'l') === 0 || (is_numeric($pathArray[$i]) && strtolower($lastOpe) === 'l')) {
//                if (stripos($pathArray[$i], 'l') === 0) {
                    $newX = $this->_parseInt($pathArray[$i]);
                    $newY = $this->_parseInt($pathArray[$i + 1]);
//                } else {
//                    $newX = $lastX + $this->_parseInt($pathArray[$i]);
//                    $newY = $lastY + $this->_parseInt($pathArray[$i + 1]);
//                }
                $polyPoints = array_merge($polyPoints, [$lastX, $lastY, $newX, $newY]);
                //$this->_drawLine($lastX , $lastY , $newX , $newY , IMG_COLOR_STYLED);
                $lastOpe = 'l';
                $lastX = $newX;
                $lastY = $newY;
                $i += 2;
                // Ligne horizontale
            } elseif (stripos($pathArray[$i], 'h') === 0 || (is_numeric($pathArray[$i]) && (strtolower($lastOpe) === 'h'))) {
                if (strpos($pathArray[$i], 'H') === 0) {
                    $newX = $this->_parseInt($pathArray[$i]);
                } else {
                    $newX = $lastX + $this->_parseInt($pathArray[$i]);
                }
                //$this->_drawLine($lastX , $lastY , $newX , $lastY , IMG_COLOR_STYLED);
                $polyPoints = array_merge($polyPoints, array($lastX, $lastY, $newX, $newY));
                $lastOpe = 'h';
                $lastX = $newX;
                $i++;
                // Ligne verticale
            } elseif (stripos($pathArray[$i], 'v') === 0 || (is_numeric($pathArray[$i]) && strtolower($lastOpe) === 'v')) {
                if (strpos($pathArray[$i], 'V') === 0) {
                    $newY = $this->_parseInt($pathArray[$i]);
                } else {
                    $newY = $lastY + $this->_parseInt($pathArray[$i]);
                }
                if (!isset($newX)) {
                    $newX = $lastX;
                }
                //$this->_drawLine($lastX , $lastY , $lastX , $newY , IMG_COLOR_STYLED);
                $polyPoints = array_merge($polyPoints, [$lastX, $lastY, $newX, $newY]);
                $lastY = $newY;
                $lastOpe = 'v';
                $i++;
                // Courbe
            } elseif (stripos($pathArray[$i], 'c') === 0 || (is_numeric($pathArray[$i]) && strtolower($lastOpe) === 'c')) {
                /*SPECIF !!! http://www.w3.org/TR/SVG/paths.html*/
                if (strpos($pathArray[$i], 'C') === 0) {
                    $control1x = $this->_parseInt($pathArray[$i]);
                    $control1y = $this->_parseInt($pathArray[$i + 1]);
                    $control2x = $this->_parseInt($pathArray[$i + 2]);
                    $control2y = $this->_parseInt($pathArray[$i + 3]);
                    $newX = $this->_parseInt($pathArray[$i + 4]);
                    $newY = $this->_parseInt($pathArray[$i + 5]);
                } else {
                    $control1x = $lastX + $this->_parseInt($pathArray[$i]);
                    $control1y = $lastY + $this->_parseInt($pathArray[$i + 1]);
                    $control2x = $lastX + $this->_parseInt($pathArray[$i + 2]);
                    $control2y = $lastY + $this->_parseInt($pathArray[$i + 3]);
                    $newX = $lastX + $this->_parseInt($pathArray[$i + 4]);
                    $newY = $lastY + $this->_parseInt($pathArray[$i + 5]);
                }

                $polyPoints = array_merge($polyPoints, $this->_Bezier_drawfilled(array($lastX, $lastY), array($control1x, $control1y), array($control2x, $control2y), array($newX, $newY), $colorFill));

                $lastX = $newX;
                $lastY = $newY;
                $lastOpe = 'c';
                $i += 6;
                // Dernière ligne droite
            } elseif (stripos($pathArray[$i], 'z') === 0 || (is_numeric(substr($pathArray[$i], 0, 1)) && strtolower($lastOpe) === 'z')) {
                if ($lastOpe === 'z' && $this->_debug) {
                    $this->_log->error('2 bouclages dans une boucle');
                }
                $polyPoints = array_merge($polyPoints, array($lastX, $lastY, $lastMX, $lastMY));
                $lastMX = $lastX;
                $lastMY = $lastY;
                //$this->_drawLine($lastX , $lastY , $lastMX , $lastMY , IMG_COLOR_STYLED);
                $lastOpe = 'z';
                $i++;
                // Polyline
            } else {
                $lastX = $this->_parseInt($pathArray[$i]);
//                $lastX = $this->_parseInt($pathArray[$i + 2]);
//                $lastY = $this->_parseInt($pathArray[$i + 3]);
                $lastY = $this->_parseInt($pathArray[$i + 1]);
                $lastOpe = 'l'; // s'il n'a aucune lettre, c'est une polyline, donc des... lignes.
                $i += 2;
            }
            //if($this->_debug) $this->_log->message('counter :'.$i);
        }

        $this->_drawPolygon($polyPoints, $colorStroke, $colorFill);

        imagecolordeallocate($this->_image, $colorStroke);
        if ($colorFill !== '') {
            imagecolordeallocate($this->_image, $colorFill);
        }
        imagesetthickness($this->_image, 1);
        imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));

        return null;
    }

    /**
     * add a circle in the final image
     * @param SimpleXMLElement
     * @return
     */
    private function _parseCircle($circleNode)
    {
        $x = 0;
        $y = 0;
        $r = 0;
        $strokeWidth = 1;
        $fill = '';
        $stroke = '';
        extract($this->_getParams($circleNode), EXTR_OVERWRITE);
        if ($this->_getParam('originX') !== null) {
            $x += $this->_getParam('originX');
        }
        if ($this->_getParam('originY') !== null) {
            $y += $this->_getParam('originY');
        }
        if ($r === 0) {
            return;
        }
        if ($this->_debug) {
            $this->_log->message('Cercle - x : ' . $x . ' - y : ' . $y . ' - rayon : ' . $r . '-' . ' - épaisseur : ' . $strokeWidth);
        }

        $thickness = imagesetthickness($this->_image, (int)$strokeWidth);
        if ($this->_debug && !$thickness) {
            $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
        }

        $colorStroke = $this->_allocateColor((string)$stroke);
        $colorFill = $this->_allocateColor((string)$fill);

        if ($fill !== '' || ($fill === '' && $stroke === '')) {
            imagefilledarc($this->_image, $x, $y, $r * 2, $r * 2, 0, 359.9, $colorFill, IMG_ARC_PIE);
            //imageellipse ($this->_image , $x , $y , $r*2 , $r*2, $colorStroke );
        }
        if ($stroke !== '') {
            imagearc($this->_image, $x, $y, $r * 2, $r * 2, 0, 359.9, $colorStroke);
        }
        imagecolordeallocate($this->_image, $colorStroke);
        imagecolordeallocate($this->_image, $colorFill);
        imagesetthickness($this->_image, 1);

        return null;
    }

    /**
     * add text in the final image <text fill="#000000" x="541" y="258" transform="rotate(-0, 541, 258)" font-size="10" font-family="SansSerif" font-style="normal" font-weight="normal">0</text>
     * @param SimpleXMLElement
     * @return
     */
    private function _parseText($textNode)
    {
        $x = 0;
        $y = 0;
        $r = 0;
        $strokeWidth = 1;
        $fill = '';
        $fontSize = 10;
        $fontFamily = 'SansSerif';
        $fontStyle = 'normal';
        $fontWeight = 'normal';

        extract($this->_getParams($textNode), EXTR_OVERWRITE);

        //case translation
        if ($this->_getParam('originX') !== null) {
            $x += $this->_getParam('originX');
        }
        if ($this->_getParam('originY') !== null) {
            $y += $this->_getParam('originY');
        }
        //end translation
        //case rotation
        if ($this->_getParam('rotate') !== null) {
            $r = $this->_getParam('rotate');
        }
        //end rotation
        //case scale
        if ($this->_getParam('scale') !== null) {
            $fontSize *= ($this->_getParam('scale') - 1);
        }
        //end scale

        if ($textNode === '') {
            return;
        }
        $colorStroke = $this->_allocateColor((string)$fill);

        $fontfile = './fonts/arial.ttf';
        if (is_readable('./fonts/' . strtolower($fontFamily) . '.ttf')) {
            $fontfile = './fonts/' . strtolower($fontFamily) . '.ttf';
        }

        if ($this->_debug) {
            $this->_log->message('text ' . rtrim($textNode) . ' avec typo :' . $fontfile . ' de taille ' . $fontSize);
        }
        if ($this->_debug) {
            $this->_log->message('text avec rotation : ' . $r);
        }
        //imagestring ( $this->_image , 2 , $x , $y , rtrim($textNode) , $fill );
//        imagefttext($this->_image, (double)$fontSize, $r, $x, $y, $colorStroke, $fontfile, rtrim($textNode));
        imagettftext($this->_image, (double)$fontSize, $r, $x, $y, $colorStroke, $fontfile, rtrim($textNode));
        imagecolordeallocate($this->_image, $colorStroke);

        return null;
    }

    /*
     * add a rectangle to the final image
     * @param simpleXMLElement
     * @return a nice rectangle !
     */
    private function _parseRectangle($rectNode)
    {
        $x = 0;
        $y = 0;
        $width = 0;
        $height = 0;
        $r = 0;
        $fill = '';
        $stroke = '';
        $strokeWidth = 1;
        $strokeDasharray = '';
        extract($this->_getParams($rectNode), EXTR_OVERWRITE);
        //case translate
        if ($this->_getParam('originX') !== null) {
            $x += $this->_getParam('originX');
        }
        if ($this->_getParam('originY') !== null) {
            $y += $this->_getParam('originY');
        }
        //end translate
        if ($width === 0 || $height === 0) {
            return;
        }
        $colorStroke = $this->_allocateColor((string)$stroke);
        $colorFill = $this->_allocateColor((string)$fill);
        $thickness = imagesetthickness($this->_image, (int)$strokeWidth);
        if ($strokeDasharray !== '') {
            $strokeDasharray = explode(',', $strokeDasharray);
            imagesetstyle($this->_image, $this->_getDashedStroke($strokeDasharray[0], $strokeDasharray[1], $colorStroke));
        } else {
            imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));
        }

        if ($this->_debug && !$thickness) {
            $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
        }

        if ($fill !== '' || ($fill === '' && $stroke === '')) {
            imagefilledrectangle($this->_image, $x, $y, $x + $width, $y + $height, $colorFill);
            imagerectangle($this->_image, $x, $y, $x + $width, $y + $height, IMG_COLOR_STYLED);
        }
        if ($stroke !== '') {
            imagerectangle($this->_image, $x, $y, $x + $width, $y + $height, IMG_COLOR_STYLED);
        }
        imagecolordeallocate($this->_image, $colorStroke);
        imagecolordeallocate($this->_image, $colorFill);
        imagesetthickness($this->_image, 1);
        imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));
    }

    /**
     * @param $lineNode
     */
    private function _parseLine($lineNode)
    {
        $x1 = 0;
        $y1 = 0;
        $x2 = 0;
        $y2 = 0;
        $stroke = '';
        $strokeWidth = 1;
        $strokeDasharray = '';
        extract($this->_getParams($lineNode), EXTR_OVERWRITE);
        //case translate
        if ($this->_getParam('originX') !== null) {
            $x1 += $this->_getParam('originX');
            $x2 += $this->_getParam('originX');
        }
        if ($this->_getParam('originY') !== null) {
            $y1 += $this->_getParam('originY');
            $y2 += $this->_getParam('originY');
        }
        //end translate
        //case rotation
        if ($this->_getParam('rotate') !== null) {
            // TODO
        }
        //end rotation
        //case scale
        if ($this->_getParam('scale') !== null) {
            $xrapport = ($x2 - $x1) * ($this->_getParam('scale') - 1);
            $x2 += $xrapport;
            $yrapport = ($y2 - $y1) * ($this->_getParam('scale') - 1);
            $y2 += $yrapport;
            if ($this->_debug) {
                $this->_log->message('scale by ' . Log::decode($this->_getParam('scale')) . ':' . Log::decode($xrapport) . ' - ' . Log::decode($yrapport));
            }
        }
        //end scale
        $colorStroke = $this->_allocateColor((string)$stroke);
        imagesetthickness($this->_image, (int)$strokeWidth);
        if ($strokeDasharray !== '') {
            $strokeDasharray = explode(',', $strokeDasharray);
            imagesetstyle($this->_image, $this->_getDashedStroke($strokeDasharray[0], $strokeDasharray[1], $colorStroke));
        } else {
            imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));
        }

        //if($this->_debug && !$thickness) $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
        $this->_drawLine($x1, $y1, $x2, $y2, $colorStroke);
        imagecolordeallocate($this->_image, $colorStroke);
        imagesetthickness($this->_image, 1);
        imagesetstyle($this->_image, $this->_getDashedStroke(10, 0, $colorStroke));
    }

    /**
     * add a polygon in the final image
     * @param simpleXMLElement
     * @return po-po-po-polygon !
     */
    private function _parsePolygon($polyNode)
    {
        $path = '';
        $fill = '';
        $stroke = '';
        $strokeWidth = 1;
        extract($this->_getParams($polyNode), EXTR_OVERWRITE);
        if ($path === '') {
            return;
        }
        $pointArray = preg_split('/[ ,]/', $path);
        $colorStroke = $this->_allocateColor((string)$stroke);
        $colorFill = $this->_allocateColor((string)$fill);
        $thickness = imagesetthickness($this->_image, (int)$strokeWidth);
        if ($this->_debug && !$thickness) {
            $this->_log->error('Erreur dans la mise en place de l\'épaisseur du trait');
        }

        if ($fill !== '') {
            imagefilledpolygon($this->_image, $pointArray, count($pointArray) / 2, $colorFill);
        }
        if ($stroke !== '') {
            imagepolygon($this->_image, $pointArray, count($pointArray) / 2, $colorStroke);
        }
        imagecolordeallocate($this->_image, $colorStroke);
        imagecolordeallocate($this->_image, $colorFill);
        imagesetthickness($this->_image, 1);
        return null;
    }

    /**
     * add the description text in the final image
     * @param string the description
     * @return boolean
     */
    private function _parseDescription($desc)
    {
        if ($this->_debug) $this->_log->message('Ajout de la description : ' . $desc);
        return imagestring($this->_image, 2, 10, $this->_getImageHeight() - 20, $desc, imagecolorallocate($this->_image, 255, 255, 255));
    }

    /**
     * @param SimpleXMLIterator|SimpleXMLElement $groupNode
     * get group attributes to pass it to children
     * parse children
     */
    private function _parseGroup($groupNode)
    {
        $this->_currentOptions[] = $this->_getParams($groupNode);
        foreach ($groupNode->children() as $element) {
            $this->_chooseParse($element);
        }
        unset($this->_currentOptions[count($this->_currentOptions) - 1]);
    }

    /**
     * select what to parse
     * @param simpleXMLElement $element
     */
    private function _chooseParse($element)
    {
        if ($element->getName() === 'image') {
            $this->_parseImage($element);
        }
        if ($element->getName() === 'circle') {
            $this->_parseCircle($element);
        }
        if ($element->getName() === 'rect') {
            $this->_parseRectangle($element);
        }
        if ($element->getName() === 'path') {
            $this->_parsePath($element);
        }
        if ($element->getName() === 'polygon') {
            $this->_parsePolygon($element);
        }
        if ($element->getName() === 'polyline') {
            $this->_parsePath($element);
        }
        if ($element->getName() === 'g') {
            $this->_parseGroup($element);
        }
        if ($element->getName() === 'text') {
            $this->_parseText($element);
        }
        if ($element->getName() === 'line') {
            $this->_parseLine($element);
        }
        //if($element->getName() == 'defs')
        //	$this->_parseDefs($element);
        //if($element->getName() == 'title')
        //	$this->_parseTitle($element);
        if ($this->_showDesc && $element->getName() === 'desc') {
            $this->_desc = $element;
        }
    }

    /**
     * parse everything, main function
     * @param string format of the ouput 'png' 'gif' jpg'
     * @param string path where you want to save the file (with the final name), null will just show the image but not saved on server
     * @return bool
     */
    public function toImage($format = 'png', $path = null)
    {
        $writeDesc = null;
        $this->_image = imagecreatetruecolor($this->_getImageWidth(), $this->_getImageHeight());
        imagefilledrectangle($this->_image, 0, 0, $this->_getImageWidth(), $this->_getImageHeight(), $this->_allocateColor('white'));
        imagealphablending($this->_image, true);
        //imageantialias($this->_image, true); // On ne peut pas gérer l'épaisseur des traits si l'antialiasing est activé... lol ?
        foreach ($this->_svgXML->children() as $element) {
            $this->_chooseParse($element);
            $this->_currentOptions = array();
        }
        if ($this->_showDesc && $this->_desc !== null) {
            $this->_parseDescription($this->_desc);
        }
        //imagefilter ( $this->_image , IMG_FILTER_SMOOTH, 6);
        switch ($format) {
            case 'gif' :
                header('Content-type: ' . image_type_to_mime_type(IMAGETYPE_GIF));
                return imagegif($this->_image, $path);
            case 'jpg':
                header('Content-type: ' . image_type_to_mime_type(IMAGETYPE_JPEG));
                return imagejpeg($this->_image, $path);
            case 'png' :
            default :
                header('Content-type: ' . image_type_to_mime_type(IMAGETYPE_PNG));
                return imagepng($this->_image, $path);
        }
    }
}
