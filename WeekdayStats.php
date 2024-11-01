<?php
/*
Plugin Name: WeekdayStats
Plugin URI: http://www.dustyant.com/posts/weekday-stats-plugin.html
Description: To display WeekdayStats, add <code>&lt;?php weekday_stats_posts(400, 200); ?&gt;</code> or <code>&lt;?php weekday_stats_comments(400, 200); ?&gt;</code> in your template where 400 and 200 are the width and height respectively.
Version: 0.1
Author: Pravin Paratey
Author URI: http://www.DustyAnt.com
*/
/*  Copyright 2007 Pravin Paratey (pravinp@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/

function weekday_stats_posts($width = 220, $height = 150)
{
	global $wpdb;
	
	// Get posts statistics
	$sql = "SELECT post_date from $wpdb->posts where post_type='post'";
	$results = $wpdb->get_results($sql);
	
	$post_stats = array(0,0,0,0,0,0,0);
	foreach($results as $result) {
		$post_stats[date('w', strtotime($result->post_date))]++;
	}
	
	weekday_stats($width, $height, $post_stats, "posts");
}

function weekday_stats_comments($width = 220, $height = 150)
{
	global $wpdb;
	
	// Get posts statistics
	$sql = "SELECT comment_date from $wpdb->comments";
	$results = $wpdb->get_results($sql);
	
	$post_stats = array(0,0,0,0,0,0,0);
	foreach($results as $result) {
		$post_stats[date('w', strtotime($result->comment_date))]++;
	}
	weekday_stats($width, $height, $post_stats, "comments");
}

function weekday_stats($width, $height, $post_stats, $graph)
{
	$output = '
<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/WeekdayStats/MochiKit/MochiKit.js"></script>
<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/WeekdayStats/PlotKit/Base.js"></script>
<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/WeekdayStats/PlotKit/Layout.js"></script>
<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/WeekdayStats/PlotKit/Canvas.js"></script>
<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/WeekdayStats/PlotKit/SweetCanvas.js"></script>
<script type="text/javascript">
	var timelineArray' . $graph . ' = new Array(7);
';
	
	for($i=0;$i<7;$i++)
	{
		$output .= 'timelineArray' . $graph . '[' . $i . '] = new Array(' . $i . ', ' . $post_stats[$i] . ');';
	}
	$output .= '
function drawGraph' . $graph . '() {
	var options = { 
	"xTicks": [{v:0, label:"Sun"},
		{v:1, label:"Mon"},
		{v:2, label:"Tue"},
		{v:3, label:"Wed"},
		{v:4, label:"Thu"},
		{v:5, label:"Fri"},
		{v:6, label:"Sat"}]
	};
    var layout = new PlotKit.Layout("bar", options);
    layout.addDataset("' . $graph . '", timelineArray' . $graph . ');
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("WeekdayStatsGraph' . $graph . '");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, {});
    plotter.render();
}
MochiKit.DOM.addLoadEvent(drawGraph' . $graph . ');
</script>
<div><canvas id="WeekdayStatsGraph' . $graph . '" width="' . $width . '" height="' . $height . '"></canvas></div>';

	echo $output;
}

// --------------------------------------------------------------------
// Widgetize!
// --------------------------------------------------------------------
function widget_weekday_stats() {

	function display_posts($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$title = $options['title'];

		echo $before_widget . $before_title . 'Post Stats' . $after_title;
		weekday_stats_posts();
		echo $after_widget;
	}
	
	function display_comments($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$title = $options['title'];

		echo $before_widget . $before_title . 'Comment Stats' . $after_title;
		weekday_stats_comments();
		echo $after_widget;
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('Weekday Stats - Posts', 'widgets'), 'display_posts');
	register_sidebar_widget(array('Weekday Stats - Commments', 'widgets'), 'display_comments');
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_weekday_stats');

?>