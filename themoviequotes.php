<?php
/*
Plugin Name: The Movie Quotes
Description: Widget that shows memorable movie quotes taken from TheMovieQuotes.com website.
Author: TMQ
Version: 1.4
Author URI: http://www.themoviequotes.com
Plugin URI: http://www.themoviequotes.com/tools/wordpress
*/

################################################################################
################################################################################
################################################################################
#	THE MOVIE QUOTES SETTINGS
#	FEEL FREE TO EDIT THIS SETTINGS
################################################################################
$tmq_cache_path = ABSPATH . 'wp-content/cache/';	//	Cache path, default: wp-content/cache/
$tmq_cache_file = 'tmq_cache';	//	Cache file, default: tmq_cache



################################################################################
################################################################################
################################################################################
#	THE MOVIE QUOTES CORE
#	DO NOT EDIT ANYTHING BELOW THIS LINE!!!
################################################################################

require_once(ABSPATH . WPINC . '/rss.php');
if (!defined('MAGPIE_FETCH_TIME_OUT'))
{
	define('MAGPIE_FETCH_TIME_OUT', 2);	// 2 second timeout
}
if (!defined('MAGPIE_USE_GZIP'))
{
	define('MAGPIE_USE_GZIP', true);
}

function tmq_save_data($data)
{
	global $tmq_cache_path, $tmq_cache_file;

	if (!$fp = @fopen($tmq_cache_path . $tmq_cache_file, 'w'))
	{
        echo 'Cannot open file ('.$tmq_cache_path . $tmq_cache_file.') Check folder permissions!';
        return '';
    }
    if (@fwrite($fp, $data) === FALSE)
	{
        echo 'Cannot write to file ('.$tmq_cache_path . $tmq_cache_file.') Check folder permissions!';
        return '';
    }
    if (!@fclose($fp))
	{
        echo 'Cannot close file ('.$tmq_cache_path . $tmq_cache_file.') Check folder permissions!';
        return '';
    }
}

function tmq_read_cache($widgetData)
{
	global $tmq_cache_path, $tmq_cache_file;

	$data = '';

	if (!$data = @file_get_contents($tmq_cache_path . $tmq_cache_file))
	{
		echo 'Cannot read file ('.$tmq_cache_path . $tmq_cache_file.') Check folder permissions!';
        return '';
	}

	$tmqRSS = new MagpieRSS($data);

	// if RSS parsed successfully
	if ($tmqRSS)
	{
		$outputLines = '';
		//	Loop over items, and add some extra html :)
		foreach($tmqRSS->items AS $value)
		{
			$breakLineAddon = '';
			if ($widgetData['charquote'] == 'yes')
			{
				$breakLineAddon = '<br />';
			}

			if ($widgetData['mtpos'] == 'no')
			{
				$outputLines .= '<a href="'.$value['link'].'" title="'.$value['title'].'">'.$value['title'].'</a><br />';
				$outputLines .= str_replace(':</b>', ':</b>'.$breakLineAddon, $value['description']);
			}
			else
			{
				$outputLines .= str_replace(':</b>', ':</b>'.$breakLineAddon, $value['description']);
				$outputLines .= '<a href="'.$value['link'].'" title="'.$value['title'].'">'.$value['title'].'</a>';
			}
		}

		return $outputLines;
	}
	else
	{
		$errormsg = 'Failed to parse RSS file.';

		if ($tmqRSS)
		{
			$errormsg .= ' (' . $tmqRSS->ERROR . ')';
		}

		return false;
	}
}

function tmq_fetch_data($widgetData)
{
	global $wp_version;

	//	Set user specified data
	if (isset($widgetData['category']) && $widgetData['category'] == '1')
	{
		$tmqType = 'latest';
	}
	else if (isset($widgetData['category']) && $widgetData['category'] == '2')
	{
		$tmqType = 'random';
	}
	else if (isset($widgetData['category']) && $widgetData['category'] == '3')
	{
		$tmqType = 'top';
	}
	else
	{
		$tmqType = 'latest';
	}

	$tmqNumber = isset($widgetData['items']) ? $widgetData['items'] : '1';
	//	1.2 - Added Lines per Quote option
	$tmqLines = isset($widgetData['lines']) ? $widgetData['lines'] : '0';

	if ($wp_version >= '2.7')
	{
		$client = wp_remote_get('http://www.themoviequotes.com/widgets/wordpress.xml?type='.$tmqType.'&n='.$tmqNumber.'&l='.$tmqLines);
	}
	else
	{
		//	Fetch data
		$client = new Snoopy();
		$client->agent = MAGPIE_USER_AGENT;
		$client->read_timeout = MAGPIE_FETCH_TIME_OUT;
		$client->use_gzip = MAGPIE_USE_GZIP;

		@$client->fetch('http://www.themoviequotes.com/widgets/wordpress.xml?type='.$tmqType.'&n='.$tmqNumber.'&l='.$tmqLines);
	}

	return $client;
}

function tmq_display($widgetData)
{
	global $tmq_cache_path, $tmq_cache_file, $wp_version;

	$htmlOutput = '';

	//	First let's check if cache file exist
	if (file_exists($tmq_cache_path . $tmq_cache_file) && filesize($tmq_cache_path . $tmq_cache_file) > 0)
	{
		if ($widgetData['cachetime'] > 0)
		{
			$tmqCacheTime = $widgetData['cachetime'];
		}
		else
		{
			$tmqCacheTime = 300;
		}

		//	File does exist, so let's check if its expired
		if ((time() - $tmqCacheTime) > filemtime($tmq_cache_path . $tmq_cache_file))
		{
			//	Since cache has expired, let's fetch new data
			$htmlOutput = tmq_fetch_data($widgetData);

			if ($wp_version >= '2.7')
			{
				if ($htmlOutput['response']['code'] == 200)
				{
					//	Before output, let's save new data to cache
					tmq_save_data($htmlOutput['body']);
				}
			}
			else
			{
				if ($htmlOutput->status == '200')
				{
					//	Before output, let's save new data to cache
					tmq_save_data($htmlOutput->results);
				}
			}

			return tmq_read_cache($widgetData);
		}

		return tmq_read_cache($widgetData);
	}
	else
	{
		//	No file found, someone deleted it or first time widget usage :)
		//	Let's create new file with fresh content ;)
		$htmlOutput = tmq_fetch_data($widgetData);

		if ($wp_version >= '2.7')
		{
			if ($htmlOutput['response']['code'] == 200)
			{
				//	Before output, let's save new data to cache
				tmq_save_data($htmlOutput['body']);
			}
		}
		else
		{
			if ($htmlOutput->status == '200')
			{
				//	Before output, let's save new data to cache
				tmq_save_data($htmlOutput->results);
			}
		}

		return tmq_read_cache($widgetData);
	}
}

function widget_themoviequotes($args, $widget_args = 1)
{
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_themoviequotes');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];

	echo $before_widget;

	if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

	//	Display quote(s)
	echo tmq_display($options[$number]);

	echo $after_widget;
}

function widget_themoviequotes_control($widget_args)
{
	global $wp_registered_widgets, $tmq_cache_path, $tmq_cache_file;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_themoviequotes');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) )
	{
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id )
		{
			if ( 'widget_themoviequotes' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) )
			{
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-themoviequotes'] as $widget_number => $widget_value )
		{
			$title = isset($widget_value['title']) ? trim(stripslashes($widget_value['title'])) : '';
			$category = isset($widget_value['category']) ? $widget_value['category'] : '1';
			$items = isset($widget_value['items']) ? $widget_value['items'] : '1';
			$lines = isset($widget_value['lines']) ? $widget_value['lines'] : '0';
			$charquote = isset($widget_value['charquote']) ? $widget_value['charquote'] : 'yes';
			$cachetime = isset($widget_value['cachetime']) ? $widget_value['cachetime'] : '300';
			$mtpos = isset($widget_value['mtpos']) ? $widget_value['mtpos'] : 'no';
			$options[$widget_number] = compact( 'title', 'category', 'items', 'lines', 'charquote', 'cachetime', 'mtpos' );
		}

		//	Check if cache file exist, if so delete the file
		if ( file_exists($tmq_cache_path . $tmq_cache_file) )
		{
			@unlink($tmq_cache_path . $tmq_cache_file);
		}

		update_option('widget_themoviequotes', $options);
		$updated = true;
	}

	if ( -1 == $number )
	{
		$title = 'Movie Quotes';
		$category = '2';
		$items = '1';
		$lines = '0';
		$number = '%i%';
		$charquote = 'yes';
		$cachetime = '300';
		$mtpos = 'no';
	}
	else
	{
		$title = attribute_escape($options[$number]['title']);
		$category = attribute_escape($options[$number]['category']);
		$items = attribute_escape($options[$number]['items']);
		$lines = attribute_escape($options[$number]['lines']);
		$charquote = attribute_escape($options[$number]['charquote']);
		$cachetime = attribute_escape($options[$number]['cachetime']);
		$mtpos = attribute_escape($options[$number]['mtpos']);
	}
?>
		<p>
			<label for="themoviequotes-title-<?php echo $number; ?>">
				<?php _e( 'Title' ); ?>
				<input class="widefat" id="themoviequotes-title-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="themoviequotes-category-<?php echo $number; ?>"><?php _e('Select category'); ?>
				<select id="themoviequotes-category-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][category]">
					<option value="1"<?php if ($category == '1') echo ' selected="selected"'; ?>>Latest Quote(s)</option>
					<option value="2"<?php if ($category == '2') echo ' selected="selected"'; ?>>Random Quote(s)</option>
					<option value="3"<?php if ($category == '3') echo ' selected="selected"'; ?>>Top Rated Quote(s)</option>
				</select>
			</label>
		</p>
		<p>
			<label for="themoviequotes-items-<?php echo $number; ?>"><?php _e('How many quotes would you like to display?'); ?>
				<select id="themoviequotes-items-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][items]">
					<?php
						for ( $i = 1; $i <= 5; ++$i )
							echo "<option value='$i' " . ( $items == $i ? "selected='selected'" : '' ) . ">$i</option>";
					?>
				</select>
			</label>
		</p>
		<p>
			<label for="themoviequotes-lines-<?php echo $number; ?>"><?php _e('Lines per quote?'); ?>
				<select id="themoviequotes-lines-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][lines]">
					<option value="0"<?php if ($lines == '0') echo ' selected="selected"'; ?>>No Limit</option>
					<option value="1"<?php if ($lines == '1') echo ' selected="selected"'; ?>>1</option>
					<option value="2"<?php if ($lines == '2') echo ' selected="selected"'; ?>>2 or less</option>
					<option value="3"<?php if ($lines == '3') echo ' selected="selected"'; ?>>3 or less</option>
					<option value="4"<?php if ($lines == '4') echo ' selected="selected"'; ?>>4 or less</option>
					<option value="5"<?php if ($lines == '5') echo ' selected="selected"'; ?>>5 or less</option>
					<option value="123"<?php if ($lines == '123') echo ' selected="selected"'; ?>>5 or more</option>
				</select>
			</label>
		</p>
		<p>
			<label for="themoviequotes-mtpos-<?php echo $number; ?>"><?php _e('Put movie title below quote?'); ?>
				<select id="themoviequotes-mtpos-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][mtpos]">
					<option value="yes"<?php if ($mtpos == 'yes') echo ' selected="selected"'; ?>>Yes</option>
					<option value="no"<?php if ($mtpos == 'no') echo ' selected="selected"'; ?>>No</option>
				</select>
			</label>
		</p>
		<p>
			<label for="themoviequotes-charquote-<?php echo $number; ?>"><?php _e('Put quote into new line?'); ?>
				<select id="themoviequotes-charquote-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][charquote]">
					<option value="yes"<?php if ($charquote == 'yes') echo ' selected="selected"'; ?>>Yes</option>
					<option value="no"<?php if ($charquote == 'no') echo ' selected="selected"'; ?>>No</option>
				</select>
			</label>
		</p>
		<p>
			<label for="themoviequotes-cachetime-<?php echo $number; ?>">
				<?php _e( 'Cache time in seconds' ); ?>
				<input class="widefat" id="themoviequotes-cachetime-<?php echo $number; ?>" name="widget-themoviequotes[<?php echo $number; ?>][cachetime]" type="text" value="<?php echo $cachetime; ?>" />
			</label>
		</p>
		<p>
			<input type="hidden" id="themoviequotes-submit-<?php echo $number; ?>" name="themoviequotes-submit-<?php echo $number; ?>" value="1" />
		</p>
<?php
}

function widget_themoviequotes_register()
{

	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('widget_themoviequotes') )
		$options = array();
	$widget_ops = array('classname' => 'widget_themoviequotes', 'description' => __('Memorable movie quotes from TheMovieQuotes.com'));
	$control_ops = array('width' => 460, 'height' => 350, 'id_base' => 'themoviequotes');
	$name = __('Movie Quotes');

	$id = false;
	foreach ( array_keys($options) as $o )
	{
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) || !isset($options[$o]['category']) || !isset($options[$o]['items']) || !isset($options[$o]['lines']) || !isset($options[$o]['charquote']) || !isset($options[$o]['cachetime']) || !isset($options[$o]['mtpos']))
			continue;
		$id = "themoviequotes-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'widget_themoviequotes', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_themoviequotes_control', $control_ops, array( 'number' => $o ));
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$id )
	{
		wp_register_sidebar_widget( 'themoviequotes-1', $name, 'widget_themoviequotes', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'themoviequotes-1', $name, 'widget_themoviequotes_control', $control_ops, array( 'number' => -1 ) );
	}

}

add_action( 'widgets_init', 'widget_themoviequotes_register' );

?>