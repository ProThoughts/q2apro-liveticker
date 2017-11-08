<?php
	
/*
	Plugin Name: Liveticker
	Plugin URI: http://www.q2apro.com/plugins/liveticker
	Plugin Description: The heartbeat of your forum. Displays the newest incoming events on a page and within a widget.
	Plugin Version: 1.0
	Plugin Date: 2014-02-16
	Plugin Author: q2apro.com
	Plugin Author URI: http://www.q2apro.com
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: http://www.q2apro.com/pluginupdate?id=1
	
	Licence: Copyright © q2apro.com - All rights reserved
	
*/
	
	return array(
		// default
		'enable_plugin' => 'Enable Liveticker Plugin',
		'minimum_level' => 'Level to access the page:',
		'plugin_disabled' => 'Plugin has been disabled.',
		'access_forbidden' => 'Access forbidden.',
		'plugin_page_url' => 'Open page in forum:',
		'contact' => 'For questions please visit ^1q2apro.com^2',
		'tickerbar_enabled' => 'Show Liveticker Progress Bar', // extra admin option
		'add_widget' => 'Add the Liveticker widget to all pages', // extra admin option
		'add_pagelink' => 'Add a Liveticker link to the main navigation', // extra admin option
		
		// plugin
		'page_title' => 'Liveticker',
		'intro-p1' => 'Here you see live when new answers, comments and questions are posted in the forum.',
		'intro-p2' => 'Each 30 seconds we check for new events.',
		'bar-hover-tip' => 'Hide the progress bar? Liveticker continues to update.',
		'btn-empty-list' => 'Empty list',
		'btn-filter-ticker' => 'Filter the ticker',
		'button-hover-tip' => 'Empty the list so that you can see immediately when new events appear.',
		'questions' => 'Questions',
		'answers' => 'Answers',
		'comments' => 'Comments',
		'bestanswers' => 'Best answers',
		'anonymous' => 'guest',
		'ev_q' => 'New Question',
		'ev_a' => 'Answer',
		'ev_c' => 'Comment',
		'ev_b' => 'Best answer',
		'ev_r' => 'Registered',
		'ev_q_abbr' => 'Q', // for widget
		'ev_a_abbr' => 'A',
		'ev_c_abbr' => 'C',
		'ev_b_abbr' => 'B',
		'ev_r_abbr' => 'R',
		'widget_btn_tooltip' => 'Follow all questions and answers live!',
		'from' => 'from',
	);
	

/*
	Omit PHP closing tag to help avoid accidental output
*/