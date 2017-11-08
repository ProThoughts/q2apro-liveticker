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

	class q2apro_liveticker_widget {
		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}
		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				//case 'main':
				case 'side':
					$allow=true;
					break;
				//case 'full':					
				//	break;
			}
			
			return $allow;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			// do not show button on liveticker site 
			if($request!='liveticker') {
				// liveticker blue button
				$themeobject->output('<div class="liveBox">');
				$themeobject->output('<a class="livetikBtn" title="'.qa_lang_html('q2apro_liveticker_lang/widget_btn_tooltip').'" href="'.qa_opt('site_url').'liveticker/"><span>Liveticker</span></a>');
				
				// do only show the following events
				$eventsToShow = array('q_post', 'a_post', 'c_post', 'a_select');
				
				// query last 3 events
				$queryRecentEvents = qa_db_query_sub("SELECT datetime,ipaddress,handle,event,params 
											FROM `^eventlog`
											WHERE `event`='q_post' OR `event`='a_post' OR `event`='c_post' OR `event`='a_select'
											ORDER BY datetime DESC
											LIMIT 10
											"); // returns 3, check q2apro_liveticker_getAllEvents() for break

				$recentEvents = '';
				$recentEvents = q2apro_liveticker_getAllEvents($queryRecentEvents, $eventsToShow, true); // true is to return events in widget html
				$themeobject->output('
					<div class="liveBox-events">' . $recentEvents . '
					</div>
				');

				$themeobject->output('</div>'); // close .liveBox

				$themeobject->output('<style type="text/css">
					a.livetikBtn span { 
						color:#FFF;
						font-size:12px;
					}
					a.livetikBtn { 
						position: relative; 
						overflow: visible; 
						display: inline-block; 
						padding: 5px 40px !important;	
						text-decoration:none !important;
						border: 1px solid #3072b3 !important;
						border-bottom-color: #2a65a0 !important;
						margin: 4px 0 2px 0;
						text-decoration: none !important;
						text-shadow:none !important;
						font-size:12px;
						color:#FFF !important;
						white-space: nowrap; 
						cursor: pointer; 
						outline: none; 
						background: #3C8DDE !important;
						background: -webkit-gradient(linear, 0 0, 0 100%, from(#599bdc), to(#3072b3)) !important;
						background: -moz-linear-gradient(#599bdc, #3072b3) !important;
						background: -o-linear-gradient(#599bdc, #3072b3) !important;
						background: linear-gradient(#599bdc, #3072b3) !important;
						-webkit-background-clip: padding;
						-moz-background-clip: padding;
						-o-background-clip: padding-box; 
						-webkit-border-radius: 0.2em; 
						-moz-border-radius: 0.2em; 
						border-radius: 0.2em; 
						zoom: 1; 
						*display: inline; 
						-moz-transition: none !important;
						-webkit-transition: none !important;
						transition: none !important;
					}
					a.livetikBtn:hover {
						background: #3C8DDE;
						background: -webkit-gradient(linear, 0 0, 0 100%, from(#599bdc), to(#3072b3));
						background: -moz-linear-gradient(#599bFc, #3072D3);
						background: -o-linear-gradient(#599bdc, #3072b3);
						background: linear-gradient(#599bdc, #3072b3);
						box-shadow: 0px 0px 5px #007eff;
						-moz-box-shadow: 0px 0px 5px #007eff;
						-webkit-box-shadow: 0px 0px 5px #007eff;
					}
					.liveBox {
						margin:0 0 20px 5px;
					}
					.liveBox-link {font-size:14px;color:#121212;margin-bottom:5px; }
					.liveBox-events { margin:10px 0 0 2px; font-size:10px; } 
					.liveBox-events a { display:block; color:#253540; text-decoration:none; margin-bottom:5px; }
				</style>'
				);
				
			}
			
		}

	}

/*
	Omit PHP closing tag to help avoid accidental output
*/