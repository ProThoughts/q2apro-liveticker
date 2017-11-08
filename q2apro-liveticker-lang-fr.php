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
		'enable_plugin' => 'Activer le plugin Liveticker',
		'minimum_level' => 'Niveau pour accéder à cette page :',
		'plugin_disabled' => 'Le plugin a été désactivé.',
		'access_forbidden' => 'Accès interdit.',
		'plugin_page_url' => 'Ouvrir la page plugin dans le forum:',
		'contact' => 'Si vous avez des questions, visitez  ^1q2apro.com^2.',
		'tickerbar_enabled' => 'Montrer la barre de progression de Liveticker.', // extra admin option
		'add_widget' => 'Ajouter le widget Liveticker à toutes les pages', // extra admin option
		'add_pagelink' => 'Ajouter un lien Liveticker à la navigation principale', // extra admin option
		
		// plugin
		'page_title' => 'Liveticker',
		'intro-p1' => 'Ici, vous voyez en direct quand de nouvelles réponses, commentaires et questions sont affichés dans le forum.',
		'intro-p2' => 'Toutes les 30 secondes, nous vérifions les nouveaux événements.',
		'bar-hover-tip' => 'Masquer la barre de progression? Liveticker continue à mettre à jour.',
		'btn-empty-list' => 'Vider la liste',
		'btn-filter-ticker' => 'Filtrer le ticker',
		'button-hover-tip' => 'Vider la liste afin que vous puissiez voir immédiatement si de nouveaux événements apparaissent.',
		'questions' => 'Questions',
		'answers' => 'Réponses',
		'comments' => 'Commentaires',
		'bestanswers' => 'Meill. rép.',
		'anonymous' => 'anonyme',
		'ev_q' => 'Question',
		'ev_a' => 'Réponse',
		'ev_c' => 'Commentaire',
		'ev_b' => 'Meilleure réponse',
		'ev_r' => 'Inscrit',
		'ev_q_abbr' => 'Q', // for widget
		'ev_a_abbr' => 'R',
		'ev_c_abbr' => 'C',
		'ev_b_abbr' => 'M',
		'ev_r_abbr' => 'I',
		'widget_btn_tooltip' => 'Suivez toutes les questions et réponses en direct!',
		'from' => 'de',
	);
	

/*
	Omit PHP closing tag to help avoid accidental output
*/