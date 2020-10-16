<?php
/**
 * Plugin Name:     Uploads bar chart
 * Plugin URI: 		https://github.com/wppompey/uploads-bar-chart
 * Description:     Displays disk usage for wp-content uploads folders
 * Version:         0.0.0
 * Author:          AndrewLeonard, bobbingwide

 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     uploads-bar-chart
 *
 * @package         uploads-bar-chart
 */

/* start of code added 18/06/2020 by Andrew Leonard to create a child theme*/
function mychildtheme_enqueue_styles() {
	$parent_style = 'parent-style';
	//wp_enqueue_style( 'chart-style', 'https://www.andrew-leonard.co.uk/Chart/Tooltip.css' );
	wp_enqueue_style( 'uploads-bar-chart-tooltip-style', plugin_dir_url( __FILE__) . 'Chart/Tooltip.css' );
	wp_enqueue_style( 'uploads-bar-chart-extra-style', plugin_dir_url( __FILE__) . 'Chart/Extra.css' );
	wp_enqueue_style( 'chartist-style', 'https://cdnjs.cloudflare.com/ajax/libs/chartist/0.10.1/chartist.min.css' );
}
add_action( 'wp_enqueue_scripts', 'mychildtheme_enqueue_styles' );
function mychildtheme_enqueue_scripts(){
	wp_enqueue_script('chartist-script', 'https://cdnjs.cloudflare.com/ajax/libs/chartist/0.10.1/chartist.min.js' );
	wp_enqueue_script('chartist-tooltip-script', 'https://unpkg.com/chartist-plugin-tooltips@0.0.17/dist/chartist-plugin-tooltip.js' );
	wp_enqueue_script('chartist-legend-script', 'https://cdnjs.cloudflare.com/ajax/libs/chartist-plugin-legend/0.6.1/chartist-plugin-legend.min.js' );
}
add_action( 'wp_enqueue_scripts', 'mychildtheme_enqueue_scripts' );
/* end of code added 18/06/2020 by Andrew Leonard to create a child theme

.ct-label.ct-horizontal.ct-end {
	font-size: 10px !important;
   color: black;
   white-space:nowrap;
   writing-mode:vertical-rl;
   transform:  translateY(20%);
 }

svg.ct-chart-line, svg.ct-chart-line g.ct-labels, svg.ct-chart-line g.ct-labels span.ct-label {
	overflow: visible;
}
*/



function upload_stats( $height = '500px')
{
#print_r (wp_upload_dir());
	$array=wp_upload_dir();
	$root= $array['basedir'];
	$dirs = scandir($root);
	$labels=null;
	$series=null;
	$i=0;
	foreach($dirs as $dir1)
	{
		if (is_numeric($dir1))
		{
			##       echo $dir1.'<br>';
			$dirs = scandir($root.'/'.$dir1);
			foreach($dirs as $dir2)
			{
				if (is_numeric($dir2))
				{
					#               echo $dir2.'<br>';
					$dirs = scandir($root.'/'.$dir1.'/'.$dir2);
					$mmyyyy=$dir2.'/'.$dir1;
					$dirsize=0;
					foreach($dirs as $dir3)
					{
						if ($dir3!='.' && $dir3!='..')
						{
							$filename=$root.'/'.$dir1.'/'.$dir2.'/'.$dir3;
#                        echo $filename.'<br>';
							#                       echo filesize($filename);
							#                      echo '<br>';
							$dirsize = $dirsize+(filesize($filename)/1024/1024);
						}
					}
					$data[$i]=$mmyyyy.','.number_format($dirsize, 2);
					$i++;
#                 echo $year.','.number_format($dirsize, 2);;
#                 echo '<br>';
				}
			}
		}
	}
#print_r ($data);
	$Date="";
	$null="";
	$tick="'";
	$comma=",";
	$series1="";
		$bit1="{meta: ";
	$bit2=",value: ";
	$bit3="}";
	$newline="<br>\n";
	$label="MBs";
	foreach ($data as $line_num => $line)
	{
		if  (substr($line,0,4)<>"Date")
		{
			if  (substr($line,6,4)<>",,,,")
			{
				$line=str_replace("'","",$line);
				$line=str_replace('"',"",$line);
				$pieces = explode(",", $line);
				if ($Date=="") {$Date= $tick.substr($pieces[0],0,3).substr($pieces[0],5,2).$tick;}
				else {$Date.= $comma.$tick.substr($pieces[0],0,3).substr($pieces[0],5,2).$tick;}
				if ($series1=="") {$series1= $bit1.$tick.$pieces[0].$tick.$bit2.$pieces[1].$bit3;}
				else {$series1.= $comma.$bit1.$tick.$pieces[0].$tick.$bit2.$pieces[1].$bit3;}
			}
		}
	}

	$Data = uploads_bar_chart_javascript( $Date, $series1, $height) ;

	//$Data="var chart=new Chartist.Bar('.ct-chart',{labels:[".$Date."],series:[[".$series1."]]},{fullWidth:true,width:'100%',height:'700px',
	//chartPadding:{right:50,left:50},plugins:[Chartist.plugins.tooltip(),Chartist.plugins.legend({legendNames:['MBs'],})]});";
	//echo "<h1 style=\"text-align:center;\">Upload File Statistics</h1>";
	//echo "<div class=\"ct-chart\"></div>";
	//echo "<script>";
	//echo $Data;
	//echo "</script>";
	return $Data;
}

function upload_stats_shortcode( $atts, $content, $tag ) {
	$html = "";
	$height = isset( $atts['height']) ? $atts['height'] : "350px";

	$html .= uploads_bar_chart_chart( $height );
	return $html;
}


function uploads_bar_chart_chart( $height ) {

	//$html =count( $lines );
	$html = "<div class=\"ct-chart\"></div>";
	$script = upload_stats( $height );
	$html .= uploads_bar_chart_chart_inline_script(  $script );
	return $html;
}

function uploads_bar_chart_chart_inline_script( $script  ) {
	$html = '<script type="text/javascript">';
	$html .= $script;
	$html .= '</script>';
	return $html;
}




function uploads_bar_chart_javascript( $Date, $series1, $height = '500px') {

	$Data = "var chart=new Chartist.Bar('.ct-chart',{labels:[";
	$Data .= $Date;
	$Data .=  '],';
	$Data .= 'series:[[';
	$Data .= $series1;
	$Data .= ']]},';
	$Data .= "{fullWidth:true,width:'100%',height:'";
	$Data .= $height;
	$Data .= "',chartPadding:{right:0,left:0},";
	$Data .= "plugins:[Chartist.plugins.tooltip(),Chartist.plugins.legend({legendNames:['MBs'],})]});";
	return $Data;
}
add_shortcode( 'upload_stats', 'upload_stats_shortcode');

