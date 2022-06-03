<?php
/*
Plugin Name: Custom URL Shortener
Plugin URI: https://github.com/ironfeet/custom-url-shortener
Description: Add a short URL link to your posts or pages easily.
Author: Jie Wang
Version: 0.3.4
Author URI: https://ironfeet.me
*/

$arr_us = Array("adf.ly","bit.ly","is.gd","sn.im","tinyurl.com","liurl.cn","duanurl.com");
$arr_us_api = Array("http://adf.ly/api.php?key=cc863b61c7f0c4695afa0054fa95c00a&uid=10155&url=","http://bit.ly/api?url=","http://is.gd/api.php?longurl=","http://sn.im/site/snip?r=simple&link=","http://tinyurl.com/api-create.php?url=","http://liurl.cn/api-create.php?url=","http://duanurl.com/duanurl/api-create.php?url=");

// inline_uslink
function inline_uslink($content='') 
{
	global $arr_us,$arr_us_api;
	$options = get_option("inline_uslink");
	if ( strpos($content, '[cus]') ) 
	{
		$permalink=get_permalink();

		if(array_search($options['cus'], $arr_us)===FALSE)
		{
			$link=$options['api'];
			$link.=$permalink;
		}
		else
		{
			$link=$arr_us_api[array_search($options['cus'], $arr_us)];
			$link.=$permalink;
		}
		
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$link = curl_exec($ch);
		$link='<a href="'.$link.'" >'.$options['linktext'].'</a>';

		$content = str_replace('[cus]', $link, $content);
	}
	return $content;
}

function custom_url_shorter() 
{
	global $arr_us,$arr_us_api;
	$options = get_option("inline_uslink");

	$permalink=get_permalink();
	if(array_search($options['cus'], $arr_us)===FALSE)
	{
		$link=$options['api'];
		$link.=$permalink;
	}
	else
	{
		$link=$arr_us_api[array_search($options['cus'], $arr_us)];
		$link.=$permalink;
	}
	
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$link = curl_exec($ch);
	$link='<a href="'.$link.'" >'.$options['linktext'].'</a>';
	echo $link;
}

// cus options
function cus_control() 
{
	global $arr_us, $arr_us_api;
	$options = get_option("inline_uslink");  
	if (!is_array( $options )) 
	{
		$options = array(
			'linktext' => 'Short URL',
			'cus' => 'adf.ly'
		);
	}
	if($_POST['sent'] == 'Y')
	{
		$options['linktext'] = strip_tags(stripslashes($_POST['cus-link-text']));
		$options['cus'] = strip_tags(stripslashes($_POST['group1']));
		$options['api'] = $_POST["cus-api"];
		update_option("inline_uslink", $options);
	} 
?>
<div class="wrap">
<?php    
	echo "<h2>" . __( 'Custom URL shorter', '' ) . "</h2>"; 
?>
<?php    
	echo "<h4>" . __( 'Settings', 'settings_h4' ) . "</h4>"; 
?>    
	<form name="cus_form" method="post" action="<?php $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="sent" value="Y">
    		<table class="form-table">
			
<?php
	for ($i=0;$i<count($arr_us);$i++) 
	{
		$us_id = $arr_us[$i];
?>
			<tr>
				<td>		
					<label><?php echo $arr_us[$i]?></label>

				</td>
				<td>
					<small>(<?php echo $arr_us_api[$i]?>xxx.yyy.zzz)</small>
				</td>
				<td>
					<input class="radio" type="radio" id="<?php echo $us_id?>" name="group1" value="<?php echo $us_id?>" <?php echo (($us_id==$options['cus'])?' checked=1':''); ?>/>
				</td>
			</tr>
<?php
	}
?>
			<tr>
				<td>		
					<label>Other&nbsp;URL&nbsp;shorter</label>
				</td>
				<td>
					<small>(<input type="input" id="cus-api" name="cus-api" value="<?php echo $options['api']; ?>">xxx.yyy.zzz)</small>
				</td>
				<td>
					<input class="radio" type="radio" id="<?php echo $us_id?>" name="group1" value="other" <?php echo ((array_search($options['cus'], $arr_us)===FALSE)?' checked=1':''); ?>/>
				</td>
			</tr>
			<tr>
				<td>		
					<label for="cus-<?php echo $us_id?>">Link&nbsp;text</label>
				</td>
				<td colspan="2">
				<small><input type="input" id="cus-link-text" name="cus-link-text" value="<?php echo $options['linktext']; ?>"></small>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="color:#ff0000;">		
					Tips:<br />
					1. The API of adf.ly can only be gained after your registration. The API address in this plugin is generated from my adf.ly account. You can use my API address or register your own API address as you wish. :) <br />
					2. Create a page temaplate with function custom_url_shorter();.<br />
					3. Publish a page or a post with html [cus].
				</td>
			</tr>
		</table>
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Options', 'oscimp_trdom' ) ?>" />
		</p>
	</form>
</div> 
<?php
}

function cus_admin_actions() 
{
	add_options_page("Custom-URL-Shorter", "Custom URL Shorter", 1, "Custom-URL-Shorter", "cus_control");
}

add_action('admin_menu', 'cus_admin_actions');
add_filter('the_content', 'inline_uslink');
add_filter ('the_excerpt', 'inline_uslink');
?>
