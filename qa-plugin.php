<?php

/*
	Plugin Name: Liveticker
	Plugin URI: http://www.q2apro.com/plugins/liveticker
	Plugin Description: The heartbeat of your forum. Displays the newest incoming events on a page and within a widget.
	Plugin Version: 1.0
	Plugin Date: 2014-02-17
	Plugin Author: q2apro.com
	Plugin Author URI: http://www.q2apro.com
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: http://www.q2apro.com/pluginupdate?id=1
	
	Licence: Copyright © q2apro.com - All rights reserved
	
*/

if(!defined('QA_VERSION')) {
	header('Location: ../../');
	exit;
}

// liveticker page
qa_register_plugin_module('page', 'q2apro-liveticker-page.php', 'q2apro_liveticker_page', 'Liveticker Page');

// liveticker widget
qa_register_plugin_module('widget', 'q2apro-liveticker-widget.php', 'q2apro_liveticker_widget', 'Liveticker Widget');

// language file
qa_register_plugin_phrases('q2apro-liveticker-lang-*.php', 'q2apro_liveticker_lang');

// admin module
qa_register_plugin_module('module', 'q2apro-liveticker-admin.php', 'q2apro_liveticker_admin', 'Liveticker Plugin'); // also enables eventlog



// custom function to get all events and new events
function q2apro_liveticker_getAllEvents($queryRecentEvents, $eventsToShow, $isWidget=false) {

	$listAllEvents = ''; // function scope
	$countEvents = 0;

	while ( ($row = qa_db_read_one_assoc($queryRecentEvents,true)) !== null ) {
		if(in_array($row['event'], $eventsToShow)) {
		
			// question title
			$qTitle = '';
			
			// workaround: convert tab jumps to & to be able to use query function
			// memo: don't use ' but only " for str_replace (will not work otherwise!)
			$toURL = str_replace("\t","&",$row['params']); // we get e.g. parentid=4523&parent=array(65)&postid=4524&answer=array(40)
			parse_str($toURL, $data);  // parse URL to associative array $data
			// now we can access the following variables in array $data if they exist in toURL
			
			$linkToPost = '-';
			$contentPreview = '';
			
			// find out type, if Q set link directly, if A or C do query to get correct link
			$postid = (isset($data['postid'])) ? $data['postid'] : null;
			if($postid !== null) {
				$postData = qa_db_read_one_assoc( qa_db_query_sub('SELECT type,parentid,content,format,title FROM `^posts` WHERE `postid` = #', $postid), true );
				$postType = $postData['type'];
				if($postType=='A') {
					$getQtitle = qa_db_read_one_assoc( qa_db_query_sub('SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1', $postData['parentid']), true);
					$qTitle = (isset($getQtitle['title'])) ? $getQtitle['title'] : '';
					// get correct public URL
					$activity_url = qa_path_html(qa_q_request($postData['parentid'], $qTitle), null, null, null, null);
					$linkToPost = $activity_url.'?show='.$postid.'#a'.$postid;
				}
				else if($postType=='C') {
					// get question link from answer
					$getQlink = qa_db_read_one_assoc( qa_db_query_sub('SELECT parentid,type FROM `^posts` WHERE `postid` = # LIMIT 1', $postData['parentid']), true);
					$linkToQuestion = $getQlink['parentid'];
					
					// max preview length for content in title
					$maxprevLength = 250;
					
					if($getQlink['type']=='A') {
						// comment on answer
						$getQtitle = qa_db_read_one_assoc( qa_db_query_sub('SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1', $getQlink['parentid']), true);
						$qTitle = (isset($getQtitle['title'])) ? $getQtitle['title'] : '';
						// get correct public URL
						$activity_url = qa_path_html(qa_q_request($linkToQuestion, $qTitle), null, null, null, null);
					}
					else {
						// default: comment on question
						$getQtitle = qa_db_read_one_assoc( qa_db_query_sub('SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1', $postData['parentid']), true);
						$qTitle = (isset($getQtitle['title'])) ? $getQtitle['title'] : '';
						// get correct public URL
						$activity_url = qa_path_html(qa_q_request($postData['parentid'], $qTitle), null, null, null, null);
					}
					// set correct link to post
					$linkToPost = $activity_url.'?show='.$postid.'#c'.$postid;
					// content preview in title
					if(isset($postData['content'])) {
						$contentPreview = qa_viewer_text($postData['content'], $postData['format']);
						$contentPreview = qa_html( qa_shorten_string_line($contentPreview, $maxprevLength) ); // substr($content,0,70)
					} 
				}
				// if question is hidden currently, do not show frontend!
				else if($postType=='Q_HIDDEN') {
					$qTitle = '';
				}
				else {
					// question has correct postid to link
					$getQtitle = qa_db_read_one_assoc( qa_db_query_sub('SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1', $postid), true);
					$qTitle = (isset($getQtitle['title'])) ? $getQtitle['title'] : '';
					// get correct public URL
					$activity_url = qa_path_html(qa_q_request($postid, $qTitle), null, null, null, null);
					$linkToPost = $activity_url;
				}
			}
			
			$nameAnonym = (isset($data['name']) && $data['name']!='') ? $data['name'] : qa_lang('q2apro_liveticker_lang/anonymous');
			$username = (is_null($row['handle'])) ? $nameAnonym : htmlspecialchars($row['handle']); // anonymous or username
			$usernameLink = (is_null($row['handle'])) ? $nameAnonym : '<a target="_blank" class="qa-user-link" style="font-weight:normal;" href="/user/'.$row['handle'].'">'.htmlspecialchars($row['handle']).'</a>';
			
			// set event name and css class
			$eventName = '';
			$eventNameShort = '';
			if($row['event']=="q_post") {
				$eventName = qa_lang('q2apro_liveticker_lang/ev_q');
				$eventNameShort = qa_lang('q2apro_liveticker_lang/ev_q_abbr');
				$cssClass = "question";
			}
			else if($row['event']=="a_post") {
				$eventName = qa_lang('q2apro_liveticker_lang/ev_a');
				$eventNameShort = qa_lang('q2apro_liveticker_lang/ev_a_abbr');
				$cssClass = "answer";
			}
			else if($row['event']=="c_post") {
				$eventName = qa_lang('q2apro_liveticker_lang/ev_c');
				$eventNameShort = qa_lang('q2apro_liveticker_lang/ev_c_abbr');
				$cssClass = "comment";
			}
			else if($row['event']=="a_select") {
				$eventName = qa_lang('q2apro_liveticker_lang/ev_b');
				$eventNameShort = qa_lang('q2apro_liveticker_lang/ev_b_abbr');
				$cssClass = "bestanswer";
			}
			else if($row['event']=="u_register") {
				$eventName = qa_lang('q2apro_liveticker_lang/ev_r');
				$eventNameShort = qa_lang('q2apro_liveticker_lang/ev_r_abbr');
				$cssClass = "uregistered";
			}
			
			// display date as before x time, jquery does the job of converting the datetime to human readable format
			$timeCode = $row['datetime'];
			// convert to ISO8601 date format
			$timeCode = date('c', strtotime($timeCode));
			
			// if question title is empty, question got possibly deleted, do not show frontend, u_register has no qu.title pass through
			if($qTitle=='' && $row['event']!='u_register') {
				continue;
			}
			
			// we have a new member
			if($row['event']=='u_register') {
				// link to user profile
				$linkToPost = '/user/'.$username;
			}

			if(!$isWidget) {
				// liveticker page
				$listAllEvents .= '<tr class="'.$cssClass.'">
					<td class="ev'.strtotime($row['datetime']).'" style="width:85px;"><a target="_blank" href="'.$linkToPost.'">'.$eventName.'</a></td> <td style="width:104px;"><span class="timestamp">'.$timeCode.'</span></td> <td>'.$usernameLink.'</td> <td><a target="_blank" title="'.$contentPreview.'" href="'.$linkToPost.'">'.htmlspecialchars($qTitle).'</a></td> 
					</tr>';
			}
			else {
				// widget output, e.g. <a href="#" title="Answers from q2apro">17:23h A: How can I ...</a>
				$evTime = substr($row['datetime'],11,5); // 17:23
				$qTitleShort = mb_substr($qTitle,0,21,'utf-8'); // shorten question title, needs UTF-8 substring as 2-byte-char could be cut
				// mb_substr could be replaced by qa_substr()
				
				$listAllEvents .= '<a class="ev'.strtotime($row['datetime']).'" href="'.$linkToPost.'" title="'.$eventName.' '.qa_lang('q2apro_liveticker_lang/from').' '.$username.': '.htmlspecialchars($qTitle).'">'.$evTime.' '.$eventNameShort.': '.htmlspecialchars($qTitleShort).'&hellip;</a>';
				
				$countEvents++;
				if($countEvents>=3) {
					break;
				}
			}
		}
	}
	return $listAllEvents;
} // end custom function
		
		
/*
	Omit PHP closing tag to help avoid accidental output
*/