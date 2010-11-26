<? 
include 'svgtoimage.php';
/*
<image x="184" y="286" width="10" height="10" preserveAspectRatio="none" href="imageurl4.png" style="cursor: pointer; opacity: 1; display: none; " opacity="1"></image>
<image x="204" y="286" width="10" height="10" preserveAspectRatio="none" href="imageurl5.png" style="cursor: pointer; opacity: 1; display: none; " opacity="1"></image>
*/
$svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="600" height="512">
<desc>Created with Raphael</desc>
<defs></defs>
<image x="0" y="0" width="300" height="512" preserveAspectRatio="none" href="http://labs.shikiryu.com/experimental-cut/images/pieces/fond.jpg"></image>
<rect x="168" y="275" width="52" height="70" r="0" rx="0" ry="0" fill="none" stroke="#FFF" stroke-width="3" stroke-dasharray="8,3" transform="rotate(21.91207728 194 310)" style="opacity: 1;" opacity="1"></rect>
<circle cx="50" cy="50" r="50" fill="turquoise" stroke="#000"></circle>
<circle cx="100" cy="50" r="40" stroke="#000" stroke-width="2" fill="none"/>
<image x="170" y="277" width="48" height="66" preserveAspectRatio="none" href="http://labs.shikiryu.com/experimental-cut/images/pieces/1.png" style="cursor: move; opacity: 1; " r="90" opacity="1" transform="rotate(21.91207728 194 310)"></image>
<path d="M250,150 L150,350 350,350 Z" stroke="red" stroke-width="10" />
</svg>';

$svgtoimage = SVGTOIMAGE::parse($svg);
//$svgtoimage = new SVGTOIMAGE($svg);
//$svgtoimage = SVGTOIMAGE::load('basic.svg');
$svgtoimage->setShowDesc();
$svgtoimage->setWidth(300);
$svgtoimage->setHeight(512);
header('Content-type: image/png');
echo $svgtoimage->toImage();