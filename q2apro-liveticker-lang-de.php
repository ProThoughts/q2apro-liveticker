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
		'enable_plugin' => 'Liveticker Plugin aktivieren',
		'minimum_level' => 'Auf Seite zugreifen können:',
		'plugin_disabled' => 'Dieses Plugin wurde deaktiviert.',
		'access_forbidden' => 'Zugriff nicht erlaubt.',
		'plugin_page_url' => 'Seite im Forum öffnen:',
		'contact' => 'Bei Fragen bitte ^1q2apro.com^2 besuchen.',
		'tickerbar_enabled' => 'Liveticker Ladebalken anzeigen', // extra admin option
		'add_widget' => 'Auf allen Seiten das Liveticker-Widget hinzufügen', // extra admin option
		'add_pagelink' => 'Der Hauptnavigation einen Link zum Liveticker hinzufügen', // extra admin option
		
		// plugin
		'page_title' => 'Liveticker',
		'intro-p1' => 'Hier siehst du live, wenn neue Antworten, Kommentare und Fragen eingehen.',
		'intro-p2' => 'Alle 30 Sekunden wird automatisch auf Neuigkeiten geprüft.',
		'bar-hover-tip' => 'Anzeige ausblenden? Liveticker aktualisiert sich weiterhin.',
		'btn-empty-list' => 'Liste leeren',
		'btn-filter-ticker' => 'Ticker filtern',
		'button-hover-tip' => 'Leere die Liste, damit du später sofort erkennst, welche Ereignisse neu sind.',
		'questions' => 'Fragen',
		'answers' => 'Antworten',
		'comments' => 'Kommentare',
		'bestanswers' => 'Beste Antw.',
		'anonymous' => 'Anonym',
		'ev_q' => 'Neue Frage',
		'ev_a' => 'Antwort',
		'ev_c' => 'Kommentar',
		'ev_b' => 'Beste Antwort',
		'ev_r' => 'Registriert',
		'ev_q_abbr' => 'F', // for widget
		'ev_a_abbr' => 'A',
		'ev_c_abbr' => 'K',
		'ev_b_abbr' => 'B',
		'ev_r_abbr' => 'R',
		'widget_btn_tooltip' => 'Folge allen Fragen und Antworten live!',
		'from' => 'von',
	);
	

/*
	Omit PHP closing tag to help avoid accidental output
*/