<?php
include 'SvgToImage.php';
/*
<image x="184" y="286" width="10" height="10" preserveAspectRatio="none" href="imageurl4.png" style="cursor: pointer; opacity: 1; display: none; " opacity="1"></image>
<image x="204" y="286" width="10" height="10" preserveAspectRatio="none" href="imageurl5.png" style="cursor: pointer; opacity: 1; display: none; " opacity="1"></image>
<path d="M153 334
C153 334 151 334 151 334
C151 339 153 344 156 344
C164 344 171 339 171 334
C171 322 164 314 156 314
C142 314 131 322 131 334
C131 350 142 364 156 364
C175 364 191 350 191 334
C191 311 175 294 156 294
C131 294 111 311 111 334
C111 361 131 384 156 384
C186 384 211 361 211 334
C211 300 186 274 156 274"
style="fill:white;stroke:red;stroke-width:2"/>
*/
$svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="600" height="512">
<desc>Created with Raphael</desc>
<defs></defs>
<image x="0" y="0" width="300" height="512" preserveAspectRatio="none" href="https://labs.shikiryu.com/experimental-cut/images/pieces/fond.jpg"></image>
<rect x="168" y="275" width="52" height="70" r="0" rx="0" ry="0" fill="none" stroke="#FFF" stroke-width="3" stroke-dasharray="8,3" transform="rotate(21.91207728 194 310)" style="opacity: 1;" opacity="1"></rect>
<circle cx="50" cy="50" r="50" fill="turquoise" stroke="#000"></circle>
<circle cx="100" cy="50" r="40" stroke="#000" stroke-width="2" fill="none"/>
<image x="170" y="277" width="48" height="66" preserveAspectRatio="none" href="https://labs.shikiryu.com/experimental-cut/images/pieces/1.png" style="cursor: move; opacity: 1; " r="90" opacity="1" transform="rotate(21.91207728 194 310)"></image>
<path d="M50 50 V150 H150 L200 50 Z" stroke="red" stroke-width="3" stroke-dasharray="2,2" />
<polygon points="60,150 160,60 260,150 210,250 110,250"  stroke="red" stroke-width="3"/>

<polyline stroke="gray" stroke-width="5" 
    points="80,250 80,280 60,280 60,310 80,310 80,340 40,340 40,370 80,370 80,400 20,400 20,430 80,430" />
</svg>';

//$svgtoimage = SVGTOIMAGE::parse($svg);
//$svgtoimage = new SVGTOIMAGE($svg);
//$svgtoimage = SVGTOIMAGE::load('france.svg');
$svgtoimage = SVGTOIMAGE::load('Exemple_histogramme.svg');
//$svgtoimage = SVGTOIMAGE::load('basic.svg');
$svgtoimage->setShowDesc();
//$svgtoimage->setWidth(300);
//$svgtoimage->setHeight(512);
//header('Content-type: image/png');
echo $svgtoimage->toImage();