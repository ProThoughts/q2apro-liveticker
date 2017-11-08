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

	class q2apro_liveticker_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		// for display in admin interface under admin/pages
		function suggest_requests() 
		{
			return array(
				array(
					'title' => 'Liveticker', // title of page
					'request' => 'liveticker', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='liveticker') {
				return true;
			}

			return false;
		}

		function process_request($request) {

			if(qa_opt('q2apro_liveticker_enabled')!=1) {
				$qa_content=qa_content_prepare();
				$qa_content['error'] = '<div>'.qa_lang_html('q2apro_liveticker_lang/plugin_disabled').'</div>';
				return $qa_content;
			}
			// return if permission level is not sufficient
			if(qa_user_permit_error('q2apro_liveticker_permission')) {
				$qa_content=qa_content_prepare();
				$qa_content['error'] = qa_lang_html('q2apro_liveticker_lang/access_forbidden');
				return $qa_content;
			}

			// AJAX post: we received post data, so it should be the ajax call from the liveticker page
			$transferString = qa_post_text('ajax'); // holds '1' for initial or lastevent's datetime as unixtime
			// if not set check if we got a widget ajax request
			$ajaxWidget = false;
			if(is_null($transferString)) {
				$transferString = qa_post_text('ajaxWid');
				$ajaxWidget = true;
			}

			// do only show the following events
			$eventsToShow = array('q_post', 'a_post', 'c_post', 'a_select', 'u_register'); // 'search', 'u_confirmed', 'badge_awarded ', 'q_vote_up', 'a_vote_up', 'u_favorite', 'u_login'

			// we have the ajax call, return only new events to ticker if exist
			if( $transferString !== null ) {

				$transferString = qa_sanitize_html($transferString);
				
				// allow only numbers (security)
				$transferString = preg_replace('/[^0-9]/','', $transferString); 
				
				// query to get the new events newer than transfered lastevent timestamp
				$queryRecentEvents = qa_db_query_sub("SELECT datetime, ipaddress, handle, event, params 
											FROM `^eventlog`
											WHERE UNIX_TIMESTAMP(datetime) > #
											AND event IN ('q_post', 'a_post', 'c_post', 'a_select', 'u_register')
											ORDER BY datetime DESC
											LIMIT 10
											", $transferString);
				
				$listAllEvents = '';
				$listAllEvents .= q2apro_liveticker_getAllEvents($queryRecentEvents, $eventsToShow, $ajaxWidget);

				header('Content-Type: text/plain; charset=utf-8');
				echo $listAllEvents;
				return;
			
			} // end POST data
			
			else {
				// DEFAULT page call: Display liveticker page with last events

				// set countdown variable
				$countDownSet = 30; 
				
				// start content
				$qa_content = qa_content_prepare();

				// set CSS class in body tag
				qa_set_template('liveticker-page');

				// page title
				$qa_content['title'] = qa_lang_html('q2apro_liveticker_lang/page_title'); 
				
				// start output
				$qa_content['custom'] = '<div id="livtik"><p class="ls_hint">'.qa_lang_html('q2apro_liveticker_lang/intro-p1').'<br />'.qa_lang_html('q2apro_liveticker_lang/intro-p2').'</p>'; 
				
				$qa_content['custom'] .= '<div id="countdown" title="'.qa_lang_html('q2apro_liveticker_lang/bar-hover-tip').'"><img id="loaderImage" src="'.$this->urltoroot.'loader.gif" alt="Ajax Indicator" /></div>'; 
				
				$qa_content['custom'] .= '<p class="qa-gray-button" id="hideRecentEvents" title="'.qa_lang_html('q2apro_liveticker_lang/button-hover-tip').'">'.qa_lang_html('q2apro_liveticker_lang/btn-empty-list').'</p> <p class="qa-gray-button" id="showFilter">'.qa_lang_html('q2apro_liveticker_lang/btn-filter-ticker').'</p>';
				
				/* toggles to switch on-off q-a-ba-c-items */
				$qa_content['custom'] .= '<div id="toggler">
												<span class="checkbox">
													<input type="checkbox" id="cbQuestions" checked>
													<label data-on="'.qa_lang_html('q2apro_liveticker_lang/questions').'" data-off="'.qa_lang_html('q2apro_liveticker_lang/questions').'"></label>
												</span>
												<span class="checkbox">
													<input type="checkbox" id="cbAnswers" checked>
													<label data-on="'.qa_lang_html('q2apro_liveticker_lang/answers').'" data-off="'.qa_lang_html('q2apro_liveticker_lang/answers').'"></label>
												</span>
												<span class="checkbox">
													<input type="checkbox" id="cbBestanswers" checked>
													<label data-on="'.qa_lang_html('q2apro_liveticker_lang/bestanswers').'" data-off="'.qa_lang_html('q2apro_liveticker_lang/bestanswers').'"></label>
												</span>
												<span class="checkbox">
													<input type="checkbox" id="cbComments" checked>
													<label data-on="'.qa_lang_html('q2apro_liveticker_lang/comments').'" data-off="'.qa_lang_html('q2apro_liveticker_lang/comments').'"></label>
												</span>
											</div>';
				
				/* output into theme */
				$qa_content['custom'] .= '<div class="listevents">';
				$qa_content['custom'] .= '<div id="responsecontainer">';
				
				// LOAD ALL Events on inital page load
				
				// query last 20 events
				$queryRecentEvents = qa_db_query_sub("SELECT datetime,ipaddress,handle,event,params 
											FROM `^eventlog`
											WHERE `event`='q_post' OR `event`='a_post' OR `event`='c_post' OR `event`='a_select' OR `event`='u_register'
											ORDER BY datetime DESC
											LIMIT 20
											");
				
				$listAllEvents = '<table class="bordered">';
				$listAllEvents .= q2apro_liveticker_getAllEvents($queryRecentEvents, $eventsToShow);
				$listAllEvents .= "</table>";

				$qa_content['custom'] .= $listAllEvents;
				$qa_content['custom'] .= '</div> <div style="display:none;" id="responseEvents"></div> </div>';
				$qa_content['custom'] .= '</div> <!-- end livtik -->';
				
				// cutetime plugin to convert timestamp to human readable format
				$qa_content['custom'] .= '<script src="'.$this->urltoroot.'cuteTime.min.js" type="text/javascript"></script>';
				
				// determine cutetime language strings for JS
				$cuteTimeLang = $this->q2apro_getCuteTimeLang( qa_opt('site_language') );

				// ajax loading
				$qa_content['custom'] .= "<script type='text/javascript'>
				$(document).ready(function(){ 
					var refreshTime = ".$countDownSet.";
					var refreshTimer;
					var timerC = refreshTime;
					doRefreshCountDown();
					$('#countdown').css('width', (30+refreshTime*10)+'px');
					// cutetime language strings
					".$cuteTimeLang."
					// convert timestamps to readable date format					
					$('.timestamp').cuteTime(cutetimeLang);
					
					var startEventsVisible = true;
					var startEventsCount = $('.bordered tr:visible').length;
					var pagetitle = 'Liveticker - ".qa_opt('site_title')."';
					
					var refreshId = setInterval( function() {
						// start ajax loading, show loader
						$('#loaderImage').fadeIn();
						$('#countdownTime').html('');
						// get id of latest item 
						var lastevent = $('table.bordered tr td:first').attr('class').substring(2);
						
						// create new tr and add table row						
						$('#responseEvents').load('".qa_self_html()."', {ajax:lastevent}, function() {
							// loading successful, reset countdown
							$('#loaderImage').fadeOut();
							timerC = refreshTime;
							// check for toggles to hide incoming events
							if( !$('#cbQuestions').attr('checked') ) { $('tr.question').hide(); }
							if( !$('#cbAnswers').attr('checked') ) { $('tr.answer').hide(); }
							if( !$('#cbBestanswers').attr('checked') ) { $('tr.bestanswer').hide(); }
							if( !$('#cbComments').attr('checked') ) { $('tr.comment').hide(); }
							// copy new events from #responseEvents as first row of table
							$('table.bordered tr:first').before( $('#responseEvents').html() );
							
							// empty placeholder
							$('#responseEvents').empty();
							// update all timestamps and convert new ones to readable date format
							$('.timestamp').cuteTime(cutetimeLang);
							// write number of events to page title
							var rowCount = $('.bordered tr:visible').length;
							if(startEventsVisible) { rowCount -= startEventsCount; }
							$('title').text('('+rowCount+') '+ pagetitle);
						})
					}, (1+refreshTime)*1000); // time for next refresh
					$.ajaxSetup({ cache: false });

					$('#countdown').click( function() {
						clearInterval(refreshTimer);
						$(this).fadeOut();
						$('#showFilter').css('top', '0px');
						$('#hideRecentEvents').css('top', '0px');
					});
					
					$('#hideRecentEvents').click( function() {
						$('table.bordered tr').hide();
						startEventsVisible = false;
						$('title').text('(0) '+ pagetitle);
					});
					
					function doRefreshCountDown() {
						clearInterval(refreshTimer);
						timerC = refreshTime;
						refreshTimer = setInterval( function() {
							if(timerC<1){
								// $('#countdownTime').html('');
							}
							else {
								// $('#countdownTime').html(timerC);
								var newWidth = 30+timerC*10;
								// $('#countdown').css('width', newWidth+'px');
								$('#countdown').animate({
									width: newWidth+'px'
								}, 250); // speed
								timerC--;
							}
						}, 1000);
					}
					
					// keylistener for pros :)
					$(document).keyup(function(e) { 
						if (e.keyCode == 69) { $('#hideRecentEvents').click(); } // key e
						else if (e.keyCode == 81) { $('#cbQuestions').click(); } // key q
						else if (e.keyCode == 65) { $('#cbAnswers').click(); } // key a
						else if (e.keyCode == 67) { $('#cbComments').click(); } // key c
						else if (e.keyCode == 66) { $('#cbBestanswers').click(); } // key b
						//else if (e.keyCode == 84) { $('#showFilter').click(); } // key t
					});
					
					// hide ticker-loadbar if set in options
					".(qa_opt('q2apro_liveticker_bar')==1 ? "" : "$('#countdown').trigger('click');").
					"
					
				}); // end ready
				
				String.prototype.repeat = function( num ) {
					return new Array( num + 1 ).join( this );
				}

				$('#showFilter').click( function() {
					$('#toggler').toggle();
				});
				$('#cbQuestions').click( function() {
					$('tr.question').toggle();
				});
				$('#cbAnswers').click( function() {
					$('tr.answer').toggle();
				});
				$('#cbBestanswers').click( function() {
					$('tr.bestanswer').toggle();
				});
				$('#cbComments').click( function() {
					$('tr.comment').toggle();
				});
				

				</script>";

				$qa_content['custom'] .= '<style type="text/css">
				/* liveticker page */
				#livtik {
					position:relative;
					min-height:500px;
					font-size:12px;
				}
				#livtik #showFilter, #livtik #hideRecentEvents {
					cursor:pointer;
					position:absolute;
					right:0px;
					top:20px;
					overflow: visible; 
					display: inline-block; 
					text-decoration:none !important;
					font-size:11px;
					letter-spacing:150%;
					border:1px solid #AAA;
					color:#444 !important;
					outline: none; 
					padding: 5px 10px; 
					background: #CCC;
					background: -moz-linear-gradient(top, rgba(244,244,244,1) 0%, rgba(200, 200, 200,1) 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(100%,rgba(200, 200, 200,1)));
					background: -webkit-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: -o-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: -ms-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: linear-gradient(to bottom, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#e0e0e0", endColorstr="#c9c9c9",GradientType=0 );
					-webkit-background-clip: padding;
					-moz-background-clip: padding;
					-o-background-clip: padding-box; 
					-webkit-border-radius: 0.2em; 
					-moz-border-radius: 0.2em; 
					border-radius: 0.2em; 
				}
				#livtik #hideRecentEvents {
					right:100px;
				}
				#livtik .listevents {
					margin-top:25px;
				}
				#livtik #toggler, #livtik #loaderImage {
					display:none;
				}
				#livtik #countdown {
					color:#EEE;	
					font: 12px/1.5 Arial,Tahoma,sans-serif;
					letter-spacing:-3px;
					width:105px;
					height:19px;
					text-align:center;
					background-color:#444;
					letter-spacing:-1px;
					background-image: linear-gradient(bottom, #3A3A3A 50%, #444444 50%);
					background-image: -o-linear-gradient(bottom, #3A3A3A 50%, #444444 50%);
					background-image: -moz-linear-gradient(bottom, #3A3A3A 50%, #444444 50%);
					background-image: -webkit-linear-gradient(bottom, #3A3A3A 50%, #444444 50%);
					background-image: -ms-linear-gradient(bottom, #3A3A3A 50%, #444444 50%);
					background-image: -webkit-gradient( linear, left bottom, left top, color-stop(0.5, #3A3A3A), color-stop(0.5, #444444) );
					box-shadow:1px 1px 1px rgba(4, 4, 4, 0.35);
					-webkit-border-radius:7px;
					-moz-border-radius:7px;
					border-radius:7px;
					cursor:pointer;
					margin-bottom:18px;
				}

				#livtik table {
					*border-collapse: collapse; /* IE7 and lower */
					border-spacing: 0;
					width:100%;
				}
				#livtik .bordered {
					border: solid #ccc 1px;
					-moz-border-radius: 6px;
					-webkit-border-radius: 6px;
					border-radius: 6px;
					-webkit-box-shadow: 0 1px 1px #ccc; 
					-moz-box-shadow: 0 1px 1px #ccc; 
					box-shadow: 0 1px 1px #ccc;
				}
				#livtik .bordered td, .bordered th {
					border-left: 1px solid #ccc;
					border-top: 1px solid #ccc;
					padding: 9px;
					text-align: left;
					font-size:12px;
				}
				#livtik .bordered td:first-child, .bordered th:first-child {
					border-left: none;
				}
				#livtik .bordered tr:first-child {
					-moz-border-radius: 6px 0 0 0;
					-webkit-border-radius: 6px 0 0 0;
					border-radius: 6px 0 0 0;
				}
				#livtik .bordered tr:last-child {
					-moz-border-radius: 0 6px 0 0;
					-webkit-border-radius: 0 6px 0 0;
					border-radius: 0 6px 0 0;
				}
				#livtik .bordered tr:only-child{
					-moz-border-radius: 6px 6px 0 0;
					-webkit-border-radius: 6px 6px 0 0;
					border-radius: 6px 6px 0 0;
				}
				#livtik .bordered tr:last-child td:first-child {
					-moz-border-radius: 0 0 0 6px;
					-webkit-border-radius: 0 0 0 6px;
					border-radius: 0 0 0 6px;
				}
				#livtik .bordered tr:last-child td:last-child {
					-moz-border-radius: 0 0 6px 0;
					-webkit-border-radius: 0 0 6px 0;
					border-radius: 0 0 6px 0;
				}
				#livtik .bordered tr.question {
					background: rgb(180,255,99);
					background: -moz-linear-gradient(top, rgba(232,255,216,1) 0%, rgba(180,255,99,1) 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(232,255,216,1)), color-stop(100%,rgba(180,255,99,1)));
					background: -webkit-linear-gradient(top, rgba(232,255,216,1) 0%,rgba(180,255,99,1) 100%);
					background: -o-linear-gradient(top, rgba(232,255,216,1) 0%,rgba(180,255,99,1) 100%);
					background: -ms-linear-gradient(top, rgba(232,255,216,1) 0%,rgba(180,255,99,1) 100%);
					background: linear-gradient(to bottom, rgba(232,255,216,1) 0%,rgba(180,255,99,1) 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#e8ffd8", endColorstr="#b4df5b",GradientType=0 );
				}
				#livtik .bordered tr.bestanswer {
					background: #FF8;
					background: -moz-linear-gradient(top, #fefcea 0%, #f1da36 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fefcea), color-stop(100%,#f1da36));
					background: -webkit-linear-gradient(top, #fefcea 0%,#f1da36 100%);
					background: -o-linear-gradient(top, #fefcea 0%,#f1da36 100%);
					background: -ms-linear-gradient(top, #fefcea 0%,#f1da36 100%);
					background: linear-gradient(to bottom, #fefcea 0%,#f1da36 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#fefcea", endColorstr="#f1da36",GradientType=0 );
				}
				#livtik .bordered tr.answer {
					background: rgb(176,232,252);
					background-image: linear-gradient(bottom, rgb(176,232,252) 13%, rgb(191,241,255) 33%);
					background-image: -o-linear-gradient(bottom, rgb(176,232,252) 13%, rgb(191,241,255) 33%);
					background-image: -moz-linear-gradient(bottom, rgb(176,232,252) 13%, rgb(191,241,255) 33%);
					background-image: -webkit-linear-gradient(bottom, rgb(176,232,252) 13%, rgb(191,241,255) 33%);
					background-image: -ms-linear-gradient(bottom, rgb(176,232,252) 13%, rgb(191,241,255) 33%);
					background-image: -webkit-gradient(linear,left bottom,left top,color-stop(0.13, rgb(176,232,252)),color-stop(0.33, rgb(191,241,255)));
				}
				#livtik .bordered tr.comment {
					background: rgb(239,239,239);
					background: -moz-linear-gradient(top, rgba(250,250,250,1) 0%, rgba(239,239,239,1) 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(100%,rgba(239,239,239,1)));
					background: -webkit-linear-gradient(top, rgba(250,250,250,1) 0%,rgba(239,239,239,1) 100%);
					background: -o-linear-gradient(top, rgba(250,250,250,1) 0%,rgba(239,239,239,1) 100%);
					background: -ms-linear-gradient(top, rgba(250,250,250,1) 0%,rgba(239,239,239,1) 100%);
					background: linear-gradient(to bottom, rgba(250,250,250,1) 0%,rgba(239,239,239,1) 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#e0e0e0", endColorstr="#c9c9c9",GradientType=0 );
				}
				#livtik .bordered tr.uregistered {
					background: rgb(180,200,180);
					background: -moz-linear-gradient(top,  rgba(250,250,250,1) 0%, rgba(180,200,180,1) 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(100%,rgba(180,200,180,1)));
					background: -webkit-linear-gradient(top,  rgba(250,250,250,1) 0%,rgba(180,200,180,1) 100%);
					background: -o-linear-gradient(top,  rgba(250,250,250,1) 0%,rgba(180,200,180,1) 100%);
					background: -ms-linear-gradient(top,  rgba(250,250,250,1) 0%,rgba(180,200,180,1) 100%);
					background: linear-gradient(to bottom,  rgba(250,250,250,1) 0%,rgba(180,200,180,1) 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#e0e0e0", endColorstr="#c9c9c9",GradientType=0 );
				}
				#livtik table a {
					color:#000; 
				}
				#livtik .checkbox {
					display:inline-block;
					position:relative;
					text-align:left;
					width:105px;
					height:24px;
					background-color:#DDD;
					overflow:hidden;
					-webkit-box-shadow:inset 0 1px 1px #343,0 1px 0 rgba(255,255,255,0.1);
					-moz-box-shadow:inset 0 1px 1px #343,0 1px 0 rgba(255,255,255,0.1);
					box-shadow:inset 0 1px 1px #343,0 1px 0 rgba(255,255,255,0.1);
					-webkit-border-radius:6px;
					-moz-border-radius:6px;
					border-radius:6px;
				}
				#livtik .checkbox input {
					display:block;
					position:absolute;
					top:0;
					right:0;
					bottom:0;
					left:0;
					width:100%;
					height:100%;
					margin:0;
					cursor:pointer;
					opacity:0;
					filter:alpha(opacity=0);
					z-index:2;
				}
				#livtik .checkbox label {
					background-color:#999;
					-webkit-box-shadow:0 0 0 1px rgba(0,0,0,0.1),0 1px 2px rgba(0,0,0,0.4);
					-moz-box-shadow:0 0 0 1px rgba(0,0,0,0.1),0 1px 2px rgba(0,0,0,0.4);
					box-shadow:0 0 0 1px rgba(0,0,0,0.1),0 1px 2px rgba(0,0,0,0.4);
					-webkit-border-radius:5px;
					-moz-border-radius:5px;
					border-radius:5px;
					display:inline-block;
					width:85px;
					text-align:center;
					font:bold 11px/22px sans-serif;
					color:#DDD;
					-webkit-transition:margin-left 0.2s ease-in-out;
					-moz-transition:margin-left 0.2s ease-in-out;
					-ms-transition:margin-left 0.2s ease-in-out;
					-o-transition:margin-left 0.2s ease-in-out;
					transition:margin-left 0.2s ease-in-out;
					margin:1px;
				}
				#livtik .checkbox label:before {
					content:attr(data-off);
				}
				#livtik .checkbox input:checked + label {
					margin-left:19px;
					color:#333;
					background: #CCC;
					background: -moz-linear-gradient(top, rgba(244,244,244,1) 0%, rgba(200, 200, 200,1) 100%);
					background: -webkit-gradient(linear, left top, left bottom, color-stop(100%,rgba(200, 200, 200,1)));
					background: -webkit-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: -o-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: -ms-linear-gradient(top, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					background: linear-gradient(to bottom, rgba(244,244,244,1) 0%,rgba(200, 200, 200,1) 100%);
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#e0e0e0", endColorstr="#c9c9c9",GradientType=0 );
				}
				#livtik .checkbox input:checked + label:before {
					content:attr(data-on);
				}
				</style>';
				
				return $qa_content;

			} // end default
		} // process_request
		
		/* CUSTOM FUNCTION */
		function q2apro_getCuteTimeLang($lang) {
			if($lang=='fr') {
				return "var cutetimeLang = {
				time_ranges: [
				{bound: Number.NEGATIVE_INFINITY,	
				cuteness: 'le futur!',	unit_size: 0},
				{bound: 0,
				cuteness: 'maintenant',	unit_size: 0},
				{bound: 20 * 1000,
				cuteness: 'il y a quelques secondes',	unit_size: 0},
				{bound: 60 * 1000,
				cuteness: 'il y a une minute',	unit_size: 0},
				{bound: 60 * 1000 * 2,
				cuteness: 'il y a %CT% minutes',	unit_size: 60 * 1000},
				{bound: 60 * 1000 * 60,
				cuteness: 'il y a une heure',	unit_size: 0},
				{bound: 60 * 1000 * 60 * 2,
				cuteness: 'il y a %CT% heures',	unit_size: 60 * 1000 * 60},
				{bound: 60 * 1000 * 60 * 24,
				cuteness: 'hier',	unit_size: 0},
				{bound: 60 * 1000 * 60 * 24 * 2,
				cuteness: 'il y a %CT% jours',	unit_size: 60 * 1000 * 60 * 24},
				{bound: 60 * 1000 * 60 * 24 * 30,	
				cuteness: 'le mois dernier',	unit_size: 0},
				{bound: 60 * 1000 * 60 * 24 * 30 * 2,
				cuteness: 'il y a %CT% mois',	unit_size: 60 * 1000 * 60 * 24 * 30},
				{bound: 60 * 1000 * 60 * 24 * 30 * 12,
				cuteness: 'l\'ann&eacute;e derni&egrave;re',	unit_size: 0},
				{bound: 60 * 1000 * 60 * 24 * 30 * 12 * 2,
				cuteness: 'il y a %CT% ans',	unit_size: 60 * 1000 * 60 * 24 * 30 * 12},
				{bound: Number.POSITIVE_INFINITY,
				cuteness: 'il y a tr&egrave;s longtemps',	unit_size: 0}
				]
				};
				";
			}
			else if($lang=='de') {
				return "var cutetimeLang = {
				time_ranges: [
					{bound: Number.NEGATIVE_INFINITY,
					cuteness: 'in der Zukunft', unit_size: 0},
					{bound: 0,
					cuteness: 'in diesem Augenblick', unit_size: 0},
					{bound: 20 * 1000,
					cuteness: 'vor ein paar Sekunden', unit_size: 0},
					// cuteness: 'vor %CT% Sekunden', unit_size: 1*1000},
					{bound: 60 * 1000,
					cuteness: 'vor einer Minute', unit_size: 0},
					{bound: 60 * 1000 * 2,
					cuteness: 'vor %CT% Minuten', unit_size: 60 * 1000},
					{bound: 60 * 1000 * 60,
					cuteness: 'vor einer Stunde', unit_size: 0},
					{bound: 60 * 1000 * 60 * 2,
					cuteness: 'vor %CT% Stunden', unit_size: 60 * 1000 * 60},
					{bound: 60 * 1000 * 60 * 24,
					cuteness: 'Gestern', unit_size: 0},
					{bound: 60 * 1000 * 60 * 24 * 2,
					cuteness: 'vor %CT% Tagen', unit_size: 60 * 1000 * 60 * 24},
					{bound: 60 * 1000 * 60 * 24 * 30,
					cuteness: 'last month', unit_size: 0},
					{bound: 60 * 1000 * 60 * 24 * 30 * 2,
					cuteness: 'vor %CT% Monaten', unit_size: 60 * 1000 * 60 * 24 * 30},
					{bound: 60 * 1000 * 60 * 24 * 30 * 12,
					cuteness: 'letztes Jahr', unit_size: 0},
					{bound: 60 * 1000 * 60 * 24 * 30 * 12 * 2,
					cuteness: 'vor %CT% Jahren', unit_size: 60 * 1000 * 60 * 24 * 30 * 12},
					{bound: Number.POSITIVE_INFINITY,
					cuteness: 'vor unbestimmter Zeit', unit_size: 0}
				]
				};
				";
			}
			else {
				// default english
				return 'var cutetimeLang = {}';
			}
		} // end function q2apro_getCuteTimeLang()
		
	} // end class

/*
	Omit PHP closing tag to help avoid accidental output
*/