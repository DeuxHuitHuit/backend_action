<?php
	/*
	Copyright: Deux Huit Huit 2014
	License: MIT, see the LICENCE file
	http://deuxhuithuit.mit-license.org/
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	require_once(EXTENSIONS . '/backend_action/fields/field.backend_action.php');

	/**
	 *
	 * Block user agent Decorator/Extension
	 * @author nicolasbrassard
	 *
	 */
	class extension_backend_action extends Extension {
		
		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Backend Action';
		
		const SETTING_GROUP = 'backend-action';

		/**
		 * private variable for holding the errors encountered when saving
		 * @var array
		 */
		protected $errors = array();

		/**
		 *
		 * Symphony utility function that permits to
		 * implement the Observer/Observable pattern.
		 * We register here delegate that will be fired by Symphony
		 */
		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendToHead'
				),
				array(
					'page' => '/publish/',
					'delegate' => 'CustomActions',
					'callback' => '__customActionTable'
				),
				array(
					'page' => '/publish/edit/',
					'delegate' => 'EntryPreRender',
					'callback' => '__customActionEdit'
				),
			); 
		}
		
		protected function __customAction(&$entry) {
			if (isset($_POST['action']['backend-action'])) {
				
				$field_id = intval($_POST['action']['backend-action']);
				$field = FieldManager::fetch($field_id);
				
				//var_dump($entry);die;
				
				if ($field != null) {
					
					//var_dump($items, $entry, $field);die;
					
					$sucess = false;
					$sucess = @$field->executeBackendAction($entry);
					
					if ($sucess === true) {
						$entry->setData($field_id, array(
							'executed' => 'yes',
							'last_execution' => DateTimeObj::format()
						));
						$entry->commit();
					}
				}
			}
		}
		
		public function __customActionTable($context) {
			$items = $context['checked'];
			
			//var_dump($items);
			
			$entry = EntryManager::fetch($items[0]);
			
			//var_dump($entry);die;
			
			if (is_array($entry) && !empty($entry)) {
				$this->__customAction($entry[0]);
			}
		}
		
		public function __customActionEdit($context) {
			//var_dump($context['entry']);die;
			
			$this->__customAction($context['entry']);
		}
		
		
		/**
		 *
		 * Appends file references into the head, if needed
		 * @param array $context
		 */
		public function appendToHead(Array $context) {
			// store the callback array locally
			$c = Administration::instance()->getPageCallback();
			
			// publish page
			if($c['driver'] == 'publish'){
				Administration::instance()->Page->addScriptToHead(
					URL . '/extensions/backend_action/assets/publish.backend_action.js',
					time(),
					false
				);
				Administration::instance()->Page->addStylesheetToHead(
					URL . '/extensions/backend_action/assets/publish.backend_action.css',
					'screen',
					time() + 1,
					false
				);
			}
		}
		
		/**
		 *
		 * Delegate fired when the extension is install
		 */
		public function install() {
			return FieldBackend_Action::createFieldTable();
		}
		
		/**
		 *
		 * Delegate fired when the extension is updated (when version changes)
		 * @param string $previousVersion
		 */
		public function update($previousVersion) {
			return true;
		}

		/**
		 *
		 * Delegate fired when the extension is uninstall
		 * Cleans settings and Database
		 */
		public function uninstall() {
			return FieldBackend_Action::deleteFieldTable();
		}
		
	}