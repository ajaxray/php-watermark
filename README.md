# PHPWatermark

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cf8fe138-7232-4390-a3c6-f9e509221353/small.png)](https://insight.sensiolabs.com/projects/cf8fe138-7232-4390-a3c6-f9e509221353)
[![Latest Stable Version](https://poser.pugx.org/ajaxray/php-watermark/v/stable)](https://packagist.org/packages/ajaxray/php-watermark)
[![Build Status](https://travis-ci.org/ajaxray/php-watermark.svg?branch=master)](https://travis-ci.org/ajaxray/php-watermark)
[![Coverage Status](https://coveralls.io/repos/github/ajaxray/php-watermark/badge.svg?branch=master)](https://coveralls.io/github/ajaxray/php-watermark?branch=master)
[![Total Downloads](https://poser.pugx.org/ajaxray/php-watermark/downloads)](https://packagist.org/packages/ajaxray/php-watermark)
[![License](https://poser.pugx.org/ajaxray/php-watermark/license)](https://packagist.org/packages/ajaxray/php-watermark)


Add text or image Watermark on image and PDF using PHP and [ImageMagick][1].

### Pre-requisite
 
 - PHP (version 5.6 or higher)
 - [ImageMagick][1]
 - [ghostscript][2] (only for PDF watermarking)

_PHP [ImageMagick][3] extension is **not required**._
 
### Installation
 
 Add as a dependency with composer
 
 ```bash
 $ composer require ajaxray/php-watermark
 ```
 
 Or download latest version as a [Zip file](https://github.com/ajaxray/php-watermark/archive/master.zip).
 
 ### How to use?
 
 ```php
  <?php
     // Initiate with source image or pdf
     $watermark = new Watermark('/path/to/source.jpg');
     
     // Customize some options (See list of supported options below)
     $watermark->setFontSize(48)
        ->setRotate(30)
        ->setOpacity(.4);
     
     // Watermark with Text
     $watermark->withText('ajaxray.com', 'path/to/output.jpg');
     
     // Watermark with Image
     $watermark->withImage('path/to/logo.png', 'path/to/output.jpg');
  ```
If output file name is skipped for `Watermark::withImage()` and `Watermark::withText()` function, the source file will be overridden. 
 
 
 ### Customization options
 
 The table below shows customization options and their support matrix.
 Listed functions should be called on an object of `Ajaxray\PHPWatermark\Watermark`.
 Checkmark column titles means the following - 
  
- Txt-Img: Watermarking with text on Image ([sample][4], [sample-tiled][5])
- Img-Img: Watermarking with Image on Image ([sample][6])
- Txt-PDF: Watermarking with text on PDF ([sample][7])
- Img-PDF: Watermarking with Image on PDF ([sample][8])

&#8987; = coming soon!

 | Function | Value | Txt-Img | Img-Img | Txt-PDF | Img-PDF |
 |---|---|:---:|:---:|:---:|:---:|
 |`setFont('Arial')` | string; Font Name | &#9989; |   | &#9989; |   |
 |`setFontSize(36)` | int; Font size | &#9989; |   | &#9989; |   |
 |`setOpacity(.4)` | float; between 0 (opaque) to 1 (transparent) | &#9989; | &#9989; | &#9989; | &#9989; |
 |`setRotate(245)` | int; between 0 to 360 | &#9989; |   | &#9989; |   |
 |`setPosition($position)` | int; One of `Watermark::POSITION_*` constants | &#9989; | &#9989; | &#9989; | &#9989; |
 |`setOffset(50, 100)` | int, int; X and Y offset relative to position | &#9989; | &#9989; | &#9989; | &#9989; |
 |`setStyle($style)` | int; One of `Watermark::STYLE_*` constants | &#8987; | &#9989; | &#8987; | &#8987; |
 |`setTiled()` | boolean; (default `true`) | &#9989; | &#9989; | &#8987; | &#8987;  |
 |`setTileSize(200, 150)` | int, int; Width and Height of each tile | &#9989; |   | &#8987; |   |

 
 BTW, all the samples linked above are the results of [these examples][9].
 You may generate them yourself just by running example scripts from command line - 
  
```bash
$ php vendor/ajaxray/php-watermark/examples/example_img.php
$ php vendor/ajaxray/php-watermark/examples/example_pdf.php
```
Then you should get the result files in `vendor/ajaxray/php-watermark/examples/img` 
and `vendor/ajaxray/php-watermark/examples/pdf` directories.
 
### Something unexpected? Debug! 🐞🔫 

If anything unexpected happened, try to debug the issue.

1. First step is to check if PHP is configured to display errors. Alternatively you may add these lines at the top of your script. 
    ```php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ```
2. A common reason of not getting expected result is mistakes in filepaths. You may try logging / printing source and destination file paths. 
3. Check the permission of the parent directory of destination path. Destination directory indicates -
    - The file path mentioned in the second argument of `Watermark::withText()` and `Watermark::withImage()` methods. 
    - Parent of the source file itself if no separate destination mentioned in above methods.
4. There is `Watermark::setDebug()` method which will make `Watermark` object to return **imagemagick** command instead of executing it. 
Then, you may run the output manually to check if there is any error in underlying `imagemagick` commands. 


#### Notes:

* To see the list of supported font names in your system, run `convert -list font` on command prompt
* Remember to set appropriate output file extension (e,g, .pdf for pdf files)
* If possible, use absolute path for files to avoid various mistakes.
* `STYLE_IMG_*` constants are for Image watermarks and `Watermark::STYLE_TEXT_*` are for text.
* Default text style (`Watermark::STYLE_TEXT_BEVEL`) is expected to be visible on any background. 
Use other text styles only on selective backgrounds.
* UnitTest are executed and all green against **PHP 5.6** and **PHP 7.1** using **PHPUnit 5.7.5**
* I'v tested all intended functionality with **ImageMagick 7.0.4-6 Q16 x86_64** and **GPL Ghostscript 9.20** installed.  
 

#### Important Update for PDF watermarking:

When using the imagemagick + ghostscript extraction and joining pdf pages, it has a few drawbacks including file-size issue. Many developers were asking for a solution about the file size and PDF quality since releasing of this library.
So, I've created a command line tool for PDF watermarking that will work without converting pages into images. As a result, you'll get better PDF quality and dramatically smaller file size.

https://github.com/ajaxray/markpdf

Please note that, it's not a PHP library. So you've to use it using [exec][10], [shell_exec][11] or [Symfony Process Component][12].

---

> "This is the Book about which there is no doubt, a guidance for those conscious of Allah" - [Al-Quran](http://quran.com)

[1]: http://www.imagemagick.org "ImageMagick Command line tool"
[2]: https://www.ghostscript.com/ "GhostScript"
[3]: http://php.net/manual/en/book.imagick.php "PHP ImageMagick Extension"
[4]: https://www.dropbox.com/s/itff1ot0h4lj1o3/watermark_text_on_img.jpg?dl=0 "Text Watermarking on Image"
[5]: https://www.dropbox.com/s/8xvr1xwlm76jiom/watermark_text_tiles_on_img.jpg?dl=0 "Tiled Text Watermarking on Image"
[6]: https://www.dropbox.com/s/k2ghbaaif1vxnws/watermark_img_on_img.jpg?dl=0 "Image Watermarking on Image"
[7]: https://www.dropbox.com/s/aorp9aoggynn3pt/watermark_text_on_pdf.pdf?dl=0 "Text Watermarking on PDF"
[8]: https://www.dropbox.com/s/myn2is2nx3xtm3v/watermark_img_on_pdf.pdf?dl=0 "Image Watermarking on PDF"
[9]: https://github.com/ajaxray/php-watermark/tree/master/examples "Example scripts"
[10]: http://php.net/manual/en/function.exec.php
[11]: http://php.net/manual/en/function.shell-exec.php
[12]: https://symfony.com/doc/current/components/process.html
