<?php

	/*
	Copyight: Deux Huit Huit 2013
	License: MIT, http://deuxhuithuit.mit-license.org
	*/

	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	require_once(TOOLKIT . '/class.field.php');

	/**
	 *
	 * Field class that will represent the meta data about link creation
	 * @author Deux Huit Huit
	 *
	 */
	class FieldBackend_Action extends Field {

		/**
		 *
		 * Name of the field table
		 * @var string
		 */
		const FIELD_TBL_NAME = 'tbl_fields_backend_action';

		/**
		 *
		 * Constructor for the Field object
		 * @param mixed $parent
		 */
		public function __construct(){
			// call the parent constructor
			parent::__construct();
			// set the name of the field
			$this->_name = __('Backend Action');
			// permits to make it required
			$this->_required = false;
			// permits the make it show in the table columns
			$this->_showcolumn = true;
			// set as not required by default
			$this->set('required', 'no');
		}

		public function isSortable(){
			return false;
		}

		public function canFilter(){
			return false;
		}

		public function canImport(){
			return false;
		}

		public function canPrePopulate(){
			return false;
		}
		
		public function allowDatasourceOutputGrouping(){
			return false;
		}
		
		public function requiresSQLGrouping(){
			return false;
		}

		public function allowDatasourceParamOutput(){
			return false;
		}

		/* ********** INPUT AND FIELD *********** */


		/**
		 *
		 * Validates input
		 * Called before <code>processRawFieldData</code>
		 * @param $data
		 * @param $message
		 * @param $entry_id
		 */
		public function checkPostFieldData($data, &$message, $entry_id = null){
			// Always valid!
			$message = NULL;
			return self::__OK__;
		}

		/**
		 *
		 * Process entries data before saving into database.
		 *
		 * @param array $data
		 * @param int $status
		 * @param boolean $simulate
		 * @param int $entry_id
		 *
		 * @return Array - data to be inserted into DB
		 */
		public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			//var_dump($data);die;
			
			return array(
				'executed' => $data['executed'] == 'yes' ? 'yes' : 'no',
				'last_execution' => $data['last_execution']
			);
		}

		/**
		 * This function permits parsing different field settings values
		 *
		 * @param array $settings
		 *	the data array to initialize if necessary.
		 */
		public function setFromPOST(Array $settings = array()) {
			
			// call the default behavior
			parent::setFromPOST($settings);
			
			// declare a new setting array
			$new_settings = array();
			
			// always display in table mode
			$new_settings['show_column'] = $settings['show_column'];
			
			// set new settings
			$new_settings['script_path'] = $settings['script_path'];
			$new_settings['allow_multiple'] = $settings['allow_multiple'] == 'yes' ? 'yes' : 'no';
			$new_settings['action_name'] = empty($settings['action_name']) ? null : $settings['action_name'];
			
			// save it into the array
			$this->setArray($new_settings);
		}
		
		/**
		 *
		 * Save field settings into the field's table
		 */
		public function commit() {

			// if the default implementation works...
			if(!parent::commit()) return FALSE;

			$id = $this->get('id');

			// exit if there is no id
			if($id == false) return FALSE;

			// declare an array contains the field's settings
			$settings = array();

			// the field id
			$settings['field_id'] = $id;

			// the php script path
			$settings['script_path'] = $this->get('script_path');
			$settings['allow_multiple'] = $this->get('allow_multiple');
			$settings['action_name'] = $this->get('action_name');

			// officialy save it
			return FieldManager::saveSettings( $id, $settings);
		}
		
		protected function fixScriptPath($script_path) {
			if (strlen($script_path) > 0 && $script_path[0] != '/') {
				$script_path = '/' . $script_path;
			}
			return $script_path;
		}
		
		/**
		 *
		 * Validates the field settings before saving it into the field's table
		 */
		public function checkFields(Array &$errors, $checkForDuplicates) {
			parent::checkFields($errors, $checkForDuplicates);
			
			$settings = $this->get();
			$script_path = $this->fixScriptPath($settings['script_path']);
			$full_path = WORKSPACE . $script_path;
			
			if (empty($script_path)) {
				$errors['script_path'] = __('Script path cannot be null.');
				
			} else if (pathinfo($script_path, PATHINFO_EXTENSION) != 'php') {
				$errors['script_path'] = __('Script path must be a php file.');
				
			} else if (!file_exists($full_path)) {
				$errors['script_path'] = __("File '%s' does not exists.", array($full_path));
			}
			
			return (!empty($errors) ? self::__ERROR__ : self::__OK__);
		}
		
		/* ******* DATA SOURCE ******* */
		
		/**
		 * Appends data into the XML tree of a Data Source
		 * @param $wrapper
		 * @param $data
		 */
		public function appendFormattedElement(&$wrapper, $data) {
			// NOTHING
		}
		
		/* ********* UI *********** */
		
		protected function createButton($data) {
			$btn_text = $this->get('action_name');
			if (empty($btn_text)) {
				$btn_text = $this->get('label');
			}
			
			$btn = new XMLElement('button', $btn_text, array(
						'type' => 'submit',
						'name' => 'action[backend-action]',
						'value' => $this->get('id'), // field id
						'class' => 'backend-action'
					));
			
			return $btn;
		}
		
		protected function createHiddenField($key, $value) {
			$input = Widget::Input("backend-action[$key]", $value, 'hidden', array('class' => 'backend-action'));
			
			return $input;
		}
		
		protected function shouldDisplayActionButton(&$data) {
			return ($data != null && $data['executed'] != 'yes') || $this->get('allow_multiple') == 'yes';
		}
		
		/**
		 *
		 * Builds the UI for the publish page
		 * @param XMLElement $wrapper
		 * @param mixed $data
		 * @param mixed $flagWithError
		 * @param string $fieldnamePrefix
		 * @param string $fieldnamePostfix
		 * @param int $entry_id
		 */
		public function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL, $entry_id = null) {
			var_dump($data);//, $this->get());die;
			
			if ($data == null || !isset($data['executed'])) {
				$wrapper->setValue(__('Please save the entry before trying any actions.'));
				return;
			}
			
			$shouldDisplay = $this->shouldDisplayActionButton($data);
			
			// Global wrapper
			$field_wrapper = new XMLElement('div');
			
			// Label
			$label = Widget::Label($this->get('label'));
			$field_wrapper->appendChild($label);
			
			// input form
			$executed = Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').'][executed]'.$fieldnamePostfix, 'yes', 'checkbox');
			if ($data['executed'] == 'yes') {
				$executed->setAttribute('checked', 'checked');
			}
			$last_execution = Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').'][last_execution]'.$fieldnamePostfix, $data['last_execution'], 'hidden');
			
			// Already executed
			$executed_wrapper = Widget::Label($executed->generate() . __('Executed'));
			$field_wrapper->appendChild($executed_wrapper);
			$field_wrapper->appendChild($last_execution);
			
			if ($shouldDisplay) {
				$action = new XMLElement('div', __('Execute') . ':');
				$action->appendChild($this->createButton($data));
				$field_wrapper->appendChild($action);
			} else {
				$execution_time = new XMLElement('div', __('Already executed on %s', array(DateTimeObj::format($data['last_execution'], 'Y-m-d H:i'))));
				$field_wrapper->appendChild($execution_time);
			}
			
			
			// error management, with global wrapper
			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($field_wrapper, $flagWithError));
			} else {
				$wrapper->appendChild($field_wrapper);
			}
		}
		
		
		/**
		 *
		 * Builds the UI for the field's settings when creating/editing a section
		 * @param XMLElement $wrapper
		 * @param array $errors
		 */
		public function displaySettingsPanel(&$wrapper, $errors=NULL){
			
			/* first line, label and such */
			parent::displaySettingsPanel($wrapper, $errors);
			
			/* second line */
			$script_wrap = new XMLElement('div', NULL, array('class' => 'backend_action two columns'));
			$script_wrap->appendChild( $this->createInput('Enter the php script path <i>Relative to workspace</i>', 'script_path', $errors) );
			$script_wrap->appendChild( $this->createInput('Action name<i>Defaults to the field name</i>', 'action_name', $errors) );
			$wrapper->appendChild($script_wrap);
			
			/* third line */
			$chk_wrap = new XMLElement('div', NULL, array('class' => 'backend_action two columns'));
			$this->appendShowColumnCheckbox($chk_wrap);
			$chk_wrap->appendChild( $this->createInput('Allow multiple executions', 'allow_multiple', $errors, 'checkbox') );
			$wrapper->appendChild($chk_wrap);
		}
		
		private function createInput($text, $key, $errors=NULL, $type='text') {
			$order = $this->get('sortorder');
			$lbl = new XMLElement('label', __($text), array('class' => 'column'));
			$input = new XMLElement('input', NULL, array(
					'type' => $type,
					'value' => $this->get($key),
					'name' => "fields[$order][$key]"
			));
			$input->setSelfClosingTag(true);
		
			if ($type == 'checkbox') {
				$input->setAttribute('value', 'yes');
				if ($this->get($key) == 'yes') {
					$input->setAttribute('checked','checked');
				}
				$lbl->setValue( $input->generate() . __($text) );
			} else {
				$lbl->prependChild($input);
			}
		
			if (isset($errors[$key])) {
				$lbl = Widget::wrapFormElementWithError($lbl, $errors[$key]);
			}
		
			return $lbl;
		}
		
		
		/**
		 *
		 * Build the UI for the table view
		 * @param Array $data
		 * @param XMLElement $link
		 * @return string - the html of the link
		 */
		public function prepareTableValue($data, XMLElement $link=NULL, $entry_id = null){
			
			if ($this->shouldDisplayActionButton($data)) {
				$link = $this->createButton($data);
			
				$wrapper = new XMLElement('div', null, array('class' => 'backend-action'));
				$wrapper->appendChild($link);
				
				return $wrapper->generate();
			} else {
				if (!$link) {
					$link = new XMLElement('span');
				}
				$link->setValue(__('Already executed.'));
				return $link->generate();
			}
		}
		
		
		/**
		 *
		 * This function allows Fields to cleanup any additional things before it is removed
		 * from the section.
		 * @return boolean
		 */
		public function tearDown() {
			return false;
		}
		
		
		/* ********* ACTIONS ************* */
		
		public function executeBackendAction($entry) {
			$success = false;
			$field = $this;
			try {
				include WORKSPACE . $this->fixScriptPath($this->get('script_path'));
			} catch (Exception $ex) {
				Administration::instance()->Page->pageAlert(__('Backend Action: Error ') . $ex->getMessage(), Alert::ERROR);
			}
			
			return $success;
		}
		
		/* ********* SQL Data Definition ************* */
		
		/**
		 *
		 * Creates table needed for entries of invidual fields
		 */
		public function createTable() {
			$id = $this->get('id');
			
			return Symphony::Database()->query("
				CREATE TABLE `tbl_entries_data_$id` (
					`id` int(11) 		unsigned NOT NULL auto_increment,
					`entry_id` 			int(11) unsigned NOT NULL,
					`executed`			enum('yes','no') NOT NULL DEFAULT 'no',
					`last_execution`	datetime,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`)
				)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}

		/**
		 * Creates the table needed for the settings of the field
		 */
		public static function createFieldTable() {
			
			$tbl = self::FIELD_TBL_NAME;
			
			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `$tbl` (
					`id` 				int(11) unsigned NOT NULL auto_increment,
					`field_id` 			int(11) unsigned NOT NULL,
					`script_path`		varchar(1024) NOT NULL,
					`allow_multiple`	enum('yes','no') NOT NULL DEFAULT 'yes',
					`action_name`		varchar(255), 
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}


		/**
		 * Drops the table needed for the settings of the field
		 */
		public static function deleteFieldTable() {
			$tbl = self::FIELD_TBL_NAME;

			return Symphony::Database()->query("
				DROP TABLE IF EXISTS `$tbl`
			");
		}

	}