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

	class q2apro_liveticker_admin {
		
		// initialize db-table 'eventlog' if it does not exist yet
		function init_queries($tableslc) {
		
			$tablename = qa_db_add_table_prefix('eventlog');
			
			// check if event logger has been initialized already (check for one of the options and existing table)
			require_once QA_INCLUDE_DIR.'qa-app-options.php';
			if(qa_opt('event_logger_to_database') && in_array($tablename, $tableslc)) {
				// options exist, but check if really enabled
				if(qa_opt('event_logger_to_database')=='' && qa_opt('event_logger_to_files')=='') {
					// enabled database logging
					qa_opt('event_logger_to_database', 1);
				}
			}
			else {
				// not enabled, let's enable the event logger
			
				// set option values for event logger
				qa_opt('event_logger_to_database', 1);
				qa_opt('event_logger_to_files', '');
				qa_opt('event_logger_directory', '');
				qa_opt('event_logger_hide_header', '');
			
				if (!in_array($tablename, $tableslc)) {
					require_once QA_INCLUDE_DIR.'qa-app-users.php';
					require_once QA_INCLUDE_DIR.'qa-db-maxima.php';

					return 'CREATE TABLE IF NOT EXISTS ^eventlog ('.
						'datetime DATETIME NOT NULL,'.
						'ipaddress VARCHAR (15) CHARACTER SET ascii,'.
						'userid '.qa_get_mysql_user_column_type().','.
						'handle VARCHAR('.QA_DB_MAX_HANDLE_LENGTH.'),'.
						'cookieid BIGINT UNSIGNED,'.
						'event VARCHAR (20) CHARACTER SET ascii NOT NULL,'.
						'params VARCHAR (800) NOT NULL,'.
						'KEY datetime (datetime),'.
						'KEY ipaddress (ipaddress),'.
						'KEY userid (userid),'.
						'KEY event (event)'.
					') ENGINE=MyISAM DEFAULT CHARSET=utf8';
				}
			}
			// memo: would be best to check if plugin is installed in qa-plugin/ folder or using plugin_exists()
			// however this functionality is with q2a v1.6.3 not available
			
		} // end init_queries

		// option's value is requested but the option has not yet been set
		function option_default($option) {
			switch($option) {
				case 'q2apro_liveticker_enabled':
					return 1; // true
				case 'q2apro_liveticker_permission':
					return QA_PERMIT_ALL; // default level to access the page
				case 'q2apro_liveticker_bar':
					return 1; // true
				case 'q2apro_liveticker_addwidget':
					return 0; // false
				case 'q2apro_liveticker_addpagelink':
					return 0; // false
				default:
					return null;				
			}
		}
			
		function allow_template($template) {
			return ($template!='admin');
		}       
			
		function admin_form(&$qa_content){                       

			// process the admin form if admin hit Save-Changes-button
			$ok = null;
			if (qa_clicked('q2apro_liveticker_save')) {
				qa_opt('q2apro_liveticker_enabled', (bool)qa_post_text('q2apro_liveticker_enabled')); // empty or 1
				qa_opt('q2apro_liveticker_permission', (int)qa_post_text('q2apro_liveticker_permission')); // level
				qa_opt('q2apro_liveticker_bar', (bool)qa_post_text('q2apro_liveticker_bar')); // empty or 1
				qa_opt('q2apro_liveticker_addwidget', (int)qa_post_text('q2apro_liveticker_addwidget')); // add widget
				qa_opt('q2apro_liveticker_addpagelink', (int)qa_post_text('q2apro_liveticker_addpagelink')); // add menu link
				$ok = qa_lang('admin/options_saved');
				
				require_once QA_INCLUDE_DIR.'qa-db-admin.php';
				
				// admin disabled liveticker plugin, we must remove widget and page link
				if(qa_opt('q2apro_liveticker_enabled')=='') {
					// remove widget
					$widgetId = qa_db_read_one_assoc(qa_db_query_sub('SELECT widgetid FROM ^widgets 
																			WHERE title=#', 'Liveticker Widget'),true);
					if(isset($widgetId)) {
						qa_db_widget_delete($widgetId);
					}
					// remove page link
					$pageId = qa_db_read_one_assoc(qa_db_query_sub('SELECT pageid FROM ^pages 
																			WHERE title=#', 'Liveticker'),true);
					if(isset($pageId)) {
						qa_db_page_delete($pageId);
					}
					// disable options
					qa_opt('q2apro_liveticker_addwidget', false);
					qa_opt('q2apro_liveticker_addpagelink', false);
				}
				else {
					// WIDGET: database action to add or remove the liveticker widget
					if(qa_opt('q2apro_liveticker_addwidget')==1) {
						$widgetPos = qa_db_read_one_assoc(qa_db_query_sub('SELECT position FROM ^widgets 
																				WHERE title=#', 'Liveticker Widget'),true);
						if(!isset($widgetPos)) {
							$intitle = 'Liveticker Widget'; // widget name
							$inposition = 'ST'; // widget position
							$intemplates = array();
							
							//$intemplates[]='all';
							//$intags=implode(',', $intemplates);
							$intags = 'question,qa,activity,questions,hot,unanswered,tags,categories,users,tag';
							//	Perform appropriate database action
							$widgetid=qa_db_widget_create($intitle, $intags);
							qa_db_widget_move($widgetid, substr($inposition, 0, 2), substr($inposition, 2));
						}
					}
					else if(qa_opt('q2apro_liveticker_addwidget')=='') {
						$widgetId = qa_db_read_one_assoc(qa_db_query_sub('SELECT widgetid FROM ^widgets 
																				WHERE title=#', 'Liveticker Widget'),true);
						if(isset($widgetId)) {
							qa_db_widget_delete($widgetId);
						}
					}

					// MENU LINK TO PAGE: database action to add or remove the liveticker link (main navigation)
					if(qa_opt('q2apro_liveticker_addpagelink')==1) {
						$pagePos = qa_db_read_one_assoc(qa_db_query_sub('SELECT position FROM ^pages 
																				WHERE title=#', 'Liveticker'),true);
						if(!isset($pagePos)) {
							$inname = 'Liveticker'; // widget name
							$inposition = 'M'; // widget position
							$intags = 'liveticker';
							$inheading = null;
							$incontent = null;
							$inpermit = QA_PERMIT_ALL;
							// qa_db_page_create($title, $flags, $tags, $heading, $content, $permit=null)
							$pageid = qa_db_page_create($inname, 1, $intags, $inheading, $incontent, $inpermit);
							qa_db_page_move($pageid, substr($inposition, 0, 1), substr($inposition, 1));
						}
					}
					else if(qa_opt('q2apro_liveticker_addpagelink')=='') {
						$pageId = qa_db_read_one_assoc(qa_db_query_sub('SELECT pageid FROM ^pages 
																				WHERE title=#', 'Liveticker'),true);
						if(isset($pageId)) {
							qa_db_page_delete($pageId);
						}
					}
				} // end database action Widget + Page link
			}
			
			// form fields to display frontend for admin
			$fields = array();
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_liveticker_lang/enable_plugin'),
				'tags' => 'NAME="q2apro_liveticker_enabled"',
				'value' => qa_opt('q2apro_liveticker_enabled'),
			);
			
			$view_permission = (int)qa_opt('q2apro_liveticker_permission');
			$permitoptions = qa_admin_permit_options(QA_PERMIT_ALL, QA_PERMIT_SUPERS, false, false);
			$pluginpageURL = qa_opt('site_url').'liveticker';
			$fields[] = array(
				'type' => 'static',
				'note' => qa_lang('q2apro_liveticker_lang/plugin_page_url').' <a target="_blank" href="'.$pluginpageURL.'">'.$pluginpageURL.'</a>',
			);
			$fields[] = array(
				'type' => 'select',
				'label' => qa_lang('q2apro_liveticker_lang/minimum_level'),
				'tags' => 'name="q2apro_liveticker_permission"',
				'options' => $permitoptions,
				'value' => $permitoptions[$view_permission],
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_liveticker_lang/tickerbar_enabled'),
				'tags' => 'NAME="q2apro_liveticker_bar"',
				'value' => qa_opt('q2apro_liveticker_bar'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_liveticker_lang/add_widget'),
				'tags' => 'NAME="q2apro_liveticker_addwidget"',
				'value' => qa_opt('q2apro_liveticker_addwidget'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_liveticker_lang/add_pagelink'),
				'tags' => 'NAME="q2apro_liveticker_addpagelink"',
				'value' => qa_opt('q2apro_liveticker_addpagelink'),
			);
			
			$fields[] = array(
				'type' => 'static',
				'note' => '<span style="font-size:75%;color:#789;">'.strtr( qa_lang('q2apro_liveticker_lang/contact'), array( 
							'^1' => '<a target="_blank" href="http://www.q2apro.com/plugins/liveticker">',
							'^2' => '</a>'
						  )).'</span>',
			);
			
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'fields' => $fields,
				'buttons' => array(
					array(
						'label' => qa_lang_html('main/save_button'),
						'tags' => 'name="q2apro_liveticker_save"',
					),
				),
			);
		}		
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/