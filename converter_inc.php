<?php
// Adapted from http://www.military-media.co.uk/import-scripts-converting-vbcms-articles-to-wordpress to include attachment importing and update for MySqli
// Source Database Details - vBulletin
$DBHost = 'localhost';
$DBUser = 'DATABASE USERNAME HERE';
$DBPassword = 'YOUR DATABASE PASSWORD';
$DBName = 'YOUR DATABASE NAME HERE';
$TablePrefix =''; // Leave empty if tables have cms prefix only.
$AttachmentsURL="../public_html/attachments/"; //URL where vBulletin attachments are stored. End with /

function SaveAttachment($image,$filename)
{
    global $pathImg;
    $img_file = @file_get_contents($image);
        if($img_file){
        $image_path = parse_url($image);
        $img_path_parts = pathinfo($image_path['path']);
        $img_ext = $img_path_parts['extension'];
        $filex = $pathImg."/".$filename;
        $fh = fopen($filex, 'w');
        $save=fputs($fh, $img_file);
        fclose($fh);
            if($save){
                return $filename;
            }
        }
}

function bbcode_to_html($bbtext){
       	    $bbextended = array(
	                        "/\[h=(.*?)\](.*?)\[\/h\]/i" => "<h$1>$2</h$1>",
	                        "/\[h=(.*?)\]/i" => "<h4>",
				"/\[\/h\]/i" => "</h4>",
				"/\[font=(.*?)\]/i" => "<span style=\"font-family: $1\">",
				"/\[\/font\]/i" => "</span>",
				"/\[size=(.*?)\]/i" => "<font size=\"$1\">",
				"/\[\/size\]/i" => "</font>",
				"/\[url](.*?)\[\/url\]/i" => "<a href=$1>$1</a>",
				"/\[url=(.*?)\]/i" => "<a href=$1>",
				"/\[\/url\]/i" => "</a>",
				"/\[HR\]\[\/HR\]/i" => "<hr />",
        			"/\[email=(.*?)\](.*?)\[\/email\]/i" => "<a href=\"mailto:$1\">$2</a>",
        			"/\[mail=(.*?)\](.*?)\[\/mail\]/i" => "<a href=\"mailto:$1\">$2</a>",
        			"/\[img\]([^[]*)\[\/img\]/i" => "<img src=\"$1\" alt=\" \" />",
        			"/\[image\]([^[]*)\[\/image\]/i" => "<img src=\"$1\" alt=\" \" />",
        			"/\[image_left\]([^[]*)\[\/image_left\]/i" => "<img src=\"$1\" alt=\" \" class=\"img_left\" />",
        			"/\[image_right\]([^[]*)\[\/image_right\]/i" => "<img src=\"$1\" alt=\" \" class=\"img_right\" />",
				"/\[attach=(.*?)\](.*?)\[\/attach\]/i" => "<img src=\"https://www.YOURDOMAINHERE.com/attachment.php?attachmentid=$2\" alt=\" \" />",
        			"/\[attach\](.*?)\[\/attach\]/i" => "<img src=\"https://www.YOURDOMAINHERE.com/attachment.php?attachmentid=$1\" alt=\" \" />",
        			"/\[color=(.*?)\]/i" => "<span style=\"color:$1\" class=\"colored\">",
				"/\[\/color\]/i" => "</span>",
        			"/\[table=(.*?)\]/i" => "<table>",
				"/\[\/table\]/i" => "</table>",
				"/\[tr\]/i" => "<tr>",
				"/\[\/tr\]/i" => "</tr>",
				"/\[td\]/i" => "<td>",
				"/\[\/td\]/i" => "</td>",
				"/\[td(.*?)\]/i" => "<td>",
				"/\[QUOTE\]/i" => "<blockquote>",
				"/\[\/QUOTE\]/i" => "</blockquote>",
				"/\[QUOTE=(.*?)\]/i" => "<blockquote>",
				"/\[\/QUOTE\]/i" => "</blockquote>",
				"/\[quote=(.*?)\]/i" => "<blockquote>",
				"/\[\/quote\]/i" => "</blockquote>",
				"/\[INDENT\]/i" => "",
				"/\[\/INDENT\]/i" => "",
				"/<\/span><a/i" => "</span> <a",
				"/\[video=youtube;(.*?)\](.*?)\[\/video\]/i" => "<iframe width=\"560\" height=\"315\" src=\"//www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>",
				"/â€[[:cntrl:]]/" => '”'
        );
        foreach($bbextended as $match=>$replacement){
                $bbtext = preg_replace($match, $replacement, $bbtext);
        }
 
        $bbtags = array(
                        '[prbreak][/prbreak]' => '<!--more-->',
			'[*=center]' => '',
                        '[QUOTE=;][/QUOTE]' => '',
                        '[heading1]' => '<h1>','[/heading1]' => '</h1>',
                        '[heading2]' => '<h2>','[/heading2]' => '</h2>',
                        '[heading3]' => '<h3>','[/heading3]' => '</h3>',
                        '<h1 class="sep_bg">' => '<h1>','</h1>' => '</h1>',
                        '<h2 class="sep_bg">' => '<h2>','</h2>' => '</h2>',
                        '<h3 class="sep_bg">' => '<h3>','</h3>' => '</h3>',
                        '[paragraph]' => '<p>','[/paragraph]' => '</p>',
                        '[para]' => '<p>','[/para]' => '</p>',
                        '[p]' => '<p>','[/p]' => '</p>',
                        '[left]' => '<span style="text-align:left;">','[/left]' => '</span>',
                        '[right]' => '<span style="text-align:right;">','[/right]' => '</span>',
                        '[center]' => '<div style="text-align:center;">','[/center]' => '</div>',
                        '[justify]' => '<span style="text-align:justify;">','[/justify]' => '</span>',
                        '[bold]' => '<span style="font-weight:bold;">','[/bold]' => '</span>',
                        '[italic]' => '<span style="font-weight:bold;">','[/italic]' => '</span>',
                        '[underline]' => '<span style="text-decoration:underline;">','[/underline]' => '</span>',
                        '[b]' => '<span style="font-weight:bold;">','[/b]' => '</span>',
                        '[i]' => '<span style="font-style:italic;">','[/i]' => '</span>',
                        '[u]' => '<span style="text-decoration:underline;">','[/u]' => '</span>',
                        '[newline]' => '<br>',
                        '[nl]' => '<br>',
                        '[unordered_list]' => '<ul>','[/unordered_list]' => '</ul>',
                        '[list]' => '<ul>','[/list]' => '</ul>',
                        '[ul]' => '<ul>','[/ul]' => '</ul>',
                        '[ordered_list]' => '<ol>','[/ordered_list]' => '</ol>',
                        '[ol]' => '<ol>','[/ol]' => '</ol>',
                        '[*]' => '<li>','[/*]' => '</li>',
        );
 
        $bbtext = str_ireplace(array_keys($bbtags), array_values($bbtags), $bbtext);
        return $bbtext;
}

function bbcode_to_attach($bbtext){
        $bbextended = array(
        );
        foreach($bbextended as $match=>$replacement){
                $bbtext = preg_replace($match, $replacement, $bbtext);
        }
        return $bbtext;
}
?>
