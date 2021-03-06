<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

class AHtml extends AController {
	protected $registry;
	protected $args = array();

	/**
	 * @param Registry $registry
	 * @param array $args
	 */
	public function __construct($registry, $args = array()) {
		$this->registry = $registry;
	}

	//#PR Build sub URL
	private function buildURL($rt, $params = '') {
		$suburl = '';
		//#PR Add admin path if we are in admin
		if (IS_ADMIN) {
			$suburl .= '&s=' . ADMIN_PATH;
		}
		//add template if present
		if (!empty($this->registry->get('request')->get[ 'sf' ])) {
			$suburl .= '&sf=' . $this->registry->get('request')->get[ 'sf' ];
		}

		$suburl = '?' . ($rt ? 'rt=' . $rt : '') . $params . $suburl;
		return $suburl;
	}

	//#PR Build non-secure URL
	public function getURL($rt, $params = '', $encode = '') {
		if (isset($this->registry->get('request')->server[ 'HTTPS' ]) && (($this->registry->get('request')->server[ 'HTTPS' ] == 'on') || ($this->registry->get('request')->server[ 'HTTPS' ] == '1'))) {
			$server = HTTPS_SERVER;
		} else {
			$server = HTTP_SERVER;
		}

		if ($this->registry->get('config')->get('storefront_template_debug') && isset($this->registry->get('request')->get[ 'tmpl_debug' ])) {
			$params .= '&tmpl_debug=' . $this->registry->get('request')->get[ 'tmpl_debug' ];
		}

		$url = $server . INDEX_FILE . $this->url_encode($this->buildURL($rt, $params), $encode);
		return $url;
	}

	//#PR Build secure URL with session token
	public function getSecureURL($rt, $params = '', $encode = '') {
		$suburl = $this->buildURL($rt, $params);
		//#PR Add session
		if (isset($this->session->data[ 'token' ]) && $this->session->data[ 'token' ]) {
			$suburl .= '&token=' . $this->session->data[ 'token' ];
		}

		if ($this->registry->get('config')->get('storefront_template_debug') && isset($this->registry->get('request')->get[ 'tmpl_debug' ])) {
			$suburl .= '&tmpl_debug=' . $this->registry->get('request')->get[ 'tmpl_debug' ];
		}

		$url = HTTPS_SERVER . INDEX_FILE . $this->url_encode($suburl, $encode);
		return $url;
	}

	//#PR Build non-secure SEO URL
	public function getSEOURL($rt, $params = '', $encode = '') {
		//#PR Generate SEO URL based on standard URL
		$this->loadModel('tool/seo_url');
		return $this->url_encode($this->model_tool_seo_url->rewrite($this->getURL($rt, $params)), $encode);
	}

	//#PR This builds URL to the catalog to be used in admin
	public function getCatalogURL($rt, $params = '', $encode = '') {
		$suburl = '?' . ($rt ? 'rt=' . $rt : '') . $params;
		$url = HTTP_SERVER . INDEX_FILE . $this->url_encode($suburl, $encode);
		return $url;
	}

	//#PR encode URLfor & to be &amp;
	public function url_encode($url, $encode = '') {
		if ($encode == '&encode') {
			return str_replace('&', '&amp;', $url);
		} else {
			return $url;
		}
	}

	/**
	 * remove get parameters from url.
	 *
	 * @param $url - url to process
	 * @param $vars string|array - single var or array of vars
	 * @return string - url without unwanted get parameters
	 */
	public function removeQueryVar($url, $vars) {
		list($url_part, $q_part) = explode('?', $url);
		parse_str($q_part, $q_vars);
		if (!is_array($vars)) {
			$vars = array( $vars );
		}
		foreach ($vars as $v)
			unset($q_vars[ $v ]);

		$new_qs = urldecode(http_build_query($q_vars));
		return $url_part . '?' . $new_qs;
	}


	/**
	 * create html code based on passed data
	 * @param  $data - array with element data
	 *  sample
	 *  $data = array(
	 *   'type' => 'input' //(hidden, textarea, selectbox, file...)
	 *   'name' => 'input name'
	 *   'value' => 'input value' // could be array for select
	 *   'style' => 'my-form'
	 *   'form' => 'form id' // needed for unique element ID     *
	 *  );
	 *
	 */
	public function buildElement($data) {
		$item = HtmlElementFactory::create($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildHidden($data) {
		$item = new HiddenHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildSubmit($data) {
		$item = new SubmitHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildInput($data) {
		$item = new InputHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildPassword($data) {
		$item = new PasswordHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildTextarea($data) {
		$item = new TextareaHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildSelectbox($data) {
		$item = new SelectboxHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildMultiselectbox($data) {
		$item = new MultiSelectboxHtmlElement($data);
		return $item->getHtml();
	}


	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildCheckbox($data) {
		$item = new CheckboxHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildCheckboxGroup($data) {
		$item = new CheckboxGroupHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildFile($data) {
		$item = new FileHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildRadio($data) {
		$item = new RadioHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildButton($data) {
		$item = new ButtonHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildForm($data) {
		$item = new FormHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildRating($data) {
		$item = new RatingHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildImage($data) {
		$item = new ImageHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildCaptcha($data) {
		$item = new CaptchaHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * same format as for buildElement, except unnecessarily 'type'
	 * @return string - html code
	 */
	public function buildPasswordset($data) {
		$item = new PasswordsetHtmlElement($data);
		return $item->getHtml();
	}

	/**
	 * @param  $data - array with element data
	 * id, name, title, value(array with id of rows(or elements) which will be selected after popup content load)
	 * content_url - url of popup content
	 * postvars - associative array with POST variables for ajax request from popup
	 *
	 * @return string - html code
	 */
	public function buildMultivalueList($data) {
		$item = new MultivalueListHtmlElement($data);
		return $item->getHtml();
	}

	public function buildMultivalue($data) {
		$item = new MultivalueHtmlElement($data);
		return $item->getHtml();
	}


	public function buildResourceImage($data) {
		$item = new ResourceImageHtmlElement($data);
		return $item->getHtml();
	}

	public function buildDate($data) {
		$item = new DateHtmlElement($data);
		return $item->getHtml();
	}

	public function buildEmail($data) {
		$item = new EmailHtmlElement($data);
		return $item->getHtml();
	}

	public function buildNumber($data) {
		$item = new NumberHtmlElement($data);
		return $item->getHtml();
	}

	public function buildPhone($data) {
		$item = new PhoneHtmlElement($data);
		return $item->getHtml();
	}

	public function buildIPaddress($data) {
		$item = new IPaddressHtmlElement($data);
		return $item->getHtml();
	}

	public function buildCountries($data) {
		$item = new CountriesHtmlElement($data);
		return $item->getHtml();
	}

	public function getContentLanguageSwitcher() {
		$registry = Registry::getInstance();
		$view = new AView(Registry::getInstance(), 0);
		$registry->get('load')->model('localisation/language');
		$results = $registry->get('model_localisation_language')->getLanguages();
		$template[ 'languages' ] = array();

		foreach ($results as $result) {
			if ($result[ 'status' ]) {
				$template[ 'languages' ][ ] = array(
					'name' => $result[ 'name' ],
					'code' => $result[ 'code' ],
					'image' => $result[ 'image' ]
				);
			}
		}
		if (sizeof($template[ 'languages' ]) > 1) {
			$template[ 'language_code' ] = $registry->get('session')->data[ 'content_language' ]; //selected in selectbox
			foreach ($registry->get('request')->get as $name => $value) {
				if ($name == 'content_language_code') continue;
				$template[ 'hiddens' ][ $name ] = $value;
			}
		} else {
			$template[ 'languages' ] = array();
		}
		$view->batchAssign($template);
		return $view->fetch('form/language_switcher.tpl');
	}

	public function getContentLanguageFlags() {
		$registry = Registry::getInstance();
		$view = new AView(Registry::getInstance(), 0);
		$registry->get('load')->model('localisation/language');
		$results = $registry->get('model_localisation_language')->getLanguages();
		$template[ 'languages' ] = array();

		foreach ($results as $result) {
			if ($result[ 'status' ]) {
				$template[ 'languages' ][ ] = array(
					'name' => $result[ 'name' ],
					'code' => $result[ 'code' ],
					'image' => $result[ 'image' ]
				);
			}
		}
		if (sizeof($template[ 'languages' ]) > 1) {
			$template[ 'language_code' ] = $registry->get('session')->data[ 'content_language' ]; //selected in selectbox
			foreach ($registry->get('request')->get as $name => $value) {
				if ($name == 'content_language_code') continue;
				$template[ 'hiddens' ][ $name ] = $value;
			}
		} else {
			$template[ 'languages' ] = array();
		}
		$view->batchAssign($template);
		return $view->fetch('form/language_flags.tpl');
	}


	/**
	 * @param  $html - text that might contain internal links #admin# or #storefront#
	 *           $mode  - 'href' create complete a tag or default just replace URL
	 * @return string - html code with parsed internal URLs
	 */
	public function convertLinks($html, $type = '') {

		$route_sections = array( "admin", "storefront" );
		foreach ($route_sections as $rt_type) {
			preg_match_all('/(#' . $rt_type . '#rt=){1}[a-z0-9\/_\-\?\&=\%]{1,255}(\b|\")/', $html, $matches, PREG_OFFSET_CAPTURE);
			if ($matches) {
				foreach ($matches[ 0 ] as $match) {
					$href = str_replace('?', '&', $match[ 0 ]);

					if ($rt_type == 'admin') {
						$new_href = str_replace('#admin#', $this->getSecureURL('') . '&', $href);
					} else {
						$new_href = str_replace('#storefront#', $this->getCatalogURL('') . '&', $href);
					}
					$new_href = str_replace('&amp;', '&', $new_href);
					$new_href = str_replace('&&', '&', $new_href);
					$new_href = str_replace('?&', '?', $new_href);
					$new_href = str_replace('&?', '&', $new_href);
					$new_href = str_replace('&', '&amp;', $new_href);


					switch ($type) {
						case 'message':
							$new_href = '<a href="' . $new_href . '" target="_blank">#link-text#</a>';
							break;
						default:
							break;
					}

					$html = str_replace($match[ 0 ], $new_href, $html);
				}
			}
		}

		return $html;
	}

}

class HtmlElementFactory {

	static private $available_elements = array(
		'I' => array(
			'type' => 'input',
			'method' => 'buildInput',
			'class' => 'InputHtmlElement'
		),
		'T' => array(
			'type' => 'textarea',
			'method' => 'buildTextarea',
			'class' => 'TextareaHtmlElement'
		),
		'S' => array(
			'type' => 'selectbox',
			'method' => 'buildSelectbox',
			'class' => 'SelectboxHtmlElement'
		),
		'M' => array(
			'type' => 'multiselectbox',
			'method' => 'buildMultiselectbox',
			'class' => 'MultiSelectboxHtmlElement'
		),
		'R' => array(
			'type' => 'radio',
			'method' => 'buildRadio',
			'class' => 'RadioHtmlElement'
		),
		'C' => array(
			'type' => 'checkbox',
			'method' => 'buildCheckbox',
			'class' => 'CheckboxHtmlElement'
		),
		'G' => array(
			'type' => 'checkboxgroup',
			'method' => 'buildCheckboxgroup',
			'class' => 'CheckboxgroupHtmlElement'
		),
		'U' => array(
			'type' => 'file',
			'method' => 'buildFile',
			'class' => 'FileHtmlElement'
		),
		'K' => array(
			'type' => 'captcha',
			'method' => 'buildCaptcha',
			'class' => 'CaptchaHtmlElement',
		),
		'H' => array(
			'type' => 'hidden',
			'method' => 'buildHidden',
			'class' => 'HiddenHtmlElement'
		),
		'P' => array(
			'type' => 'multivalue',
			'method' => 'buildMultivalue',
			'class' => 'MultivalueHtmlElement'
		),
		'L' => array(
			'type' => 'multivaluelist',
			'method' => 'buildMultivalueList',
			'class' => 'MultivalueListHtmlElement'
		),
		'D' => array(
			'type' => 'date',
			'method' => 'buildDateInput',
			'class' => 'DateInputHtmlElement'
		),
		'E' => array(
			'type' => 'email',
			'method' => 'buildEmail',
			'class' => 'EmailHtmlElement'
		),
		'N' => array(
			'type' => 'number',
			'method' => 'buildNumber',
			'class' => 'NumberHtmlElement'
		),
		'F' => array(
			'type' => 'phone',
			'method' => 'buildPhone',
			'class' => 'PhoneHtmlElement'
		),
		'A' => array(
			'type' => 'IPaddress',
			'method' => 'buildIPaddress',
			'class' => 'IPaddressHtmlElement'
		),
		'O' => array(
			'type' => 'countries',
			'method' => 'buildCountries',
			'class' => 'CountriesHtmlElement'
		),
		'Z' => array(
			'type' => 'zones',
			'method' => 'buildZones',
			'class' => 'ZonesHtmlElement'
		),

	);

	static private $elements_with_options = array(
		'S', 'M', 'R', 'G', 'O', 'Z',
	);
	static private $multivalue_elements = array(
		'M', 'R', 'G',
	);

	/**
	 *  return array of HTML elements supported
	 *  array key - code of element
	 *  [
	 *   type - element type
	 *   method - method in html class to get element html
	 *   class - element class
	 *  ]
	 *
	 * @static
	 * @return array
	 */
	static function getAvailableElements() {
		return self::$available_elements;
	}

	/**
	 * return array of elements indexes for elements which has options
	 *
	 * @static
	 * @return array
	 */
	static function getElementsWithOptions() {
		return self::$elements_with_options;
	}

	/**
	 * return array of elements indexes for elements which has options
	 *
	 * @static
	 * @return array
	 */
	static function getMultivalueElements() {
		return self::$multivalue_elements;
	}

	/**
	 * return element type
	 *
	 * @static
	 * @param $code - element code ( from $available_elements )
	 * @return null | element type
	 */
	static function getElementType($code) {
		if (!array_key_exists($code, self::$available_elements)) {
			return null;
		}
		return self::$available_elements[ $code ][ 'type' ];
	}

	static function create($data) {

		$class = ucfirst($data[ 'type' ] . 'HtmlElement');
		if (!class_exists($class)) {
			throw new AException(AC_ERR_LOAD, 'Error: Could not load HTML element ' . $data[ 'type' ] . '!');
		}
		return new $class($data);
	}
}

abstract class HtmlElement {

	protected $data = array();
	protected $view;
	public $element_id;

	function __construct($data) {
		if (!isset($data[ 'value' ])) $data[ 'value' ] = '';
		if (isset($data[ 'required' ]) && $data[ 'required' ] == 1) $data[ 'required' ] = 'Y';
		if (isset($data[ 'attr' ])) {
			$data[ 'attr' ] = ' ' . htmlspecialchars_decode($data[ 'attr' ]) . ' ';
		}
		$data[ 'registry' ] = Registry::getInstance();
		$this->data = $data;

		$this->view = new AView(Registry::getInstance(), 0);
		$this->element_id = $data[ 'name' ];
		if (isset($data[ 'form' ]))
			$this->element_id = $data[ 'form' ] . '_' . $data[ 'name' ];
	}

	public function __get($name) {

		if (array_key_exists($name, $this->data)) {
			return $this->data[ $name ];
		}
		return null;
	}

	public function __isset($name) {
		return isset($this->data[ $name ]);
	}

	public function getHtml() {
		return null;
	}

}

class HiddenHtmlElement extends HtmlElement {

	public function getHtml() {
		//var_dump($this->data);
		$this->view->batchAssign(
			array(
				'id' => $this->element_id,
				'name' => $this->name,
				'value' => $this->value,
				'attr' => $this->attr,
			)
		);
		$return = $this->view->fetch('form/hidden.tpl');
		return $return;
	}
}

class MultivalueListHtmlElement extends HtmlElement {

	public function getHtml() {
		$data = array(
			'id' => $this->element_id,
			'name' => $this->name,
			'values' => $this->values,
			'content_url' => $this->content_url,
			'edit_url' => $this->edit_url,
			'postvars' => $this->postvars,
			'form_name' => $this->form,
			'multivalue_hidden_id' => $this->multivalue_hidden_id,
			'return_to' => ($this->return_to ? $this->return_to : $this->form . '_' . $this->multivalue_hidden_id . '_item_count'),
		);

		$data[ 'text' ][ 'delete' ] = $this->text[ 'delete' ] ? $this->text[ 'delete' ] : 'delete';
		$data[ 'text' ][ 'delete_confirm' ] = $this->text[ 'delete_confirm' ] ? $this->text[ 'delete_confirm' ] : 'Confirm to delete?';

		$this->view->batchAssign($data);
		$return = $this->view->fetch('form/multivalue_list.tpl');
		return $return;
	}
}

class MultivalueHtmlElement extends HtmlElement {

	public function getHtml() {

		$data = array(
			'id' => $this->element_id,
			'name' => $this->name,
			'selected_name' => ($this->selected_name ? $this->selected_name : 'selected[]'),
			'title' => $this->title,
			'selected' => $this->selected,
			'content_url' => $this->content_url,
			'postvars' => ($this->postvars ? json_encode($this->postvars) : ''),
			'form_name' => $this->form,
			'return_to' => ($this->return_to ? $this->return_to : $this->element_id . '_item_count'),
			'no_save' => (isset($this->no_save) ? (bool)$this->no_save : false),
			'popup_height' => ((int)$this->popup_height ? (int)$this->popup_height : 620),
			'popup_width' => ((int)$this->popup_width ? (int)$this->popup_width : 800),
			'js' => array( // custom triggers for dialog events (custom fucntions calls)
				'apply' => $this->js[ 'apply' ],
				'cancel' => $this->js[ 'cancel' ],
			) );

		$data[ 'text_selected' ] = $this->text[ 'selected' ];
		$data[ 'text_edit' ] = $this->text[ 'edit' ] ? $this->text[ 'edit' ] : 'Add / Edit';
		$data[ 'text_apply' ] = $this->text[ 'apply' ] ? $this->text[ 'apply' ] : 'apply';
		$data[ 'text_save' ] = $this->text[ 'save' ] ? $this->text[ 'save' ] : 'save';
		$data[ 'text_reset' ] = $this->text[ 'reset' ] ? $this->text[ 'reset' ] : 'reset';

		$this->view->batchAssign($data);
		$return = $this->view->fetch('form/multivalue_hidden.tpl');
		return $return;
	}
}

class SubmitHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'form' => $this->form,
				'name' => $this->name,
				'value' => $this->value,
				'attr' => $this->attr,
				'style' => $this->style,
			)
		);
		$return = $this->view->fetch('form/submit.tpl');
		return $return;
	}
}

class InputHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'text',
				'value' => str_replace('"', '&quot;', $this->value),
				'default' => $this->default,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/input.tpl');
		return $return;
	}
}

class PasswordHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'password',
				'has_value' => ($this->value) ? 'Y' : 'N',
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		$return = $this->view->fetch('form/input.tpl');
		return $return;
	}
}

class TextareaHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'ovalue' => htmlentities($this->value, ENT_QUOTES, 'UTF-8'),
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/textarea.tpl');
		return $return;
	}
}

class SelectboxHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!is_array($this->value)) $this->value = array( $this->value => (string)$this->value );

		$this->options = !$this->options ? array() : $this->options;
		foreach ($this->options as &$opt) {
			$opt = (string)$opt;
		}
		unset($opt);
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/selectbox.tpl');
		return $return;
	}
}

class MultiSelectboxHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!is_array($this->value)) $this->value = array( $this->value => $this->value );

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr . ' multiple="multiple" ',
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/selectbox.tpl');
		return $return;
	}
}


class CheckboxHtmlElement extends HtmlElement {

	public function getHtml() {

		$checked = false;
		if ($this->value == 1 && is_null($this->checked)) {
			$checked = true;
		} else {
			if (empty($this->value)) { //}  is_null($this->value) || $this->value == '' || $this->value === 0 || $this->value === '0') {
				$this->value = 1;
			} else {
				if (!is_null($this->checked)) {
					$checked = $this->checked;
				}
			}
		}

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'attr' => $this->attr,
				'required' => $this->required,
				'label_text' => $this->label_text,
				'checked' => $checked,
				'style' => $this->style,
			));
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/checkbox.tpl');
		return $return;
	}
}

class CheckboxGroupHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->value = !is_array($this->value) ? array( $this->value => $this->value ) : $this->value;
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr,
				'required' => $this->required,
				'scrollbox' => $this->scrollbox,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/checkboxgroup.tpl');
		return $return;
	}
}

class FileHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
				'default_text' => $this->data[ 'registry' ]->get('language')->get('text_click_browse_file'),
				'text_browse' => $this->data[ 'registry' ]->get('language')->get('text_browse'),
				'help_url' => $this->help_url,
			)
		);
		$return = $this->view->fetch('form/file.tpl');
		return $return;
	}
}

class RadioHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/radio.tpl');
		return $return;
	}
}

class ButtonHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'text' => $this->text,
				'title' => $this->title,
				'id' => $this->element_id,
				'attr' => $this->attr,
				'style' => $this->style,
				'href' => $this->href,
				'href_class' => $this->href_class,
			)
		);
		$return = $this->view->fetch('form/button.tpl');
		return $return;
	}
}

class FormHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->method = empty($this->method) ? 'post' : $this->method;
		$this->view->batchAssign(
			array(
				'id' => $this->name,
				'name' => $this->name,
				'action' => $this->action,
				'method' => $this->method,
				'attr' => $this->attr,
				'style' => $this->style,
			)
		);

		$return = $this->view->fetch('form/form_open.tpl');
		return $return;
	}
}

class RatingHtmlElement extends HtmlElement {

	function __construct($data) {
		parent::__construct($data);
		if (!$this->data[ 'registry' ]->has('star-rating')) {
			/**
			 * @var $doc ADocument
			 */
			$doc = $this->data[ 'registry' ]->get('document');
			$doc->addScript($this->view->templateResource('/javascript/jquery/star-rating/jquery.MetaData.js'));
			$doc->addScript($this->view->templateResource('/javascript/jquery/star-rating/jquery.rating.pack.js'));

			$doc->addStyle(array(
				'href' => $this->view->templateResource('/javascript/jquery/star-rating/jquery.rating.css'),
				'rel' => 'stylesheet',
				'media' => 'screen',
			));

			$this->data[ 'registry' ]->set('star-rating', 1);
		}
	}

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'style' => 'star',
				'required' => $this->required,
			)
		);
		$return = $this->view->fetch('form/rating.tpl');
		return $return;
	}
}


class CaptchaHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'attr' => 'aform_field_type="captcha" ' . $this->attr,
				'style' => $this->style,
				'required' => $this->required,
				'captcha_url' => $this->data[ 'registry' ]->get('html')->getURL('common/captcha'),
			)
		);
		$return = $this->view->fetch('form/captcha.tpl');
		return $return;
	}
}

class PasswordsetHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'attr' => $this->attr,
				'style' => $this->style,
				'required' => $this->required,
				'text_confirm_password' => $this->data[ 'registry' ]->get('language')->get('text_confirm_password'),
			)
		);
		$return = $this->view->fetch('form/passwordset.tpl');
		return $return;
	}
}

class ResourceHtmlElement extends HtmlElement {

	function __construct($data) {
		parent::__construct($data);
	}

	public function getHtml() {
		$this->view->batchAssign(array( 'id' => $this->element_id,
			'name' => $this->name,
			'value' => $this->value,
			'preview' => $this->preview,
		));

		$return = $this->view->fetch('form/resource.tpl');
		return $return;
	}
}

class ResourceImageHtmlElement extends HtmlElement {

	function __construct($data) {
		parent::__construct($data);
	}

	public function getHtml() {

		$this->view->batchAssign(array( 'url' => $this->url,
			'width' => $this->width,
			//'height' => $this->height, // disabled because when is set it will broke proportions for img tag
			'attr' => $this->attr,
		));

		$return = $this->view->fetch('common/resource_image.tpl');
		return $return;
	}

}

class DateHtmlElement extends HtmlElement {

	function __construct($data) {
		parent::__construct($data);
		if (!$this->data[ 'registry' ]->has('date-field')) {

			$doc = $this->data[ 'registry' ]->get('document');
			$doc->addScript($this->view->templateResource('/javascript/jquery/ui/jquery-ui-1.8.22.custom.min.js'));
			$doc->addScript($this->view->templateResource('/javascript/jquery/ui/jquery.ui.datepicker.js'));

			$doc->addStyle(array(
				'href' => $this->view->templateResource('/javascript/jquery/ui/themes/ui-lightness/ui.all.css'),
				'rel' => 'stylesheet',
				'media' => 'screen',
			));

			$this->data[ 'registry' ]->set('date-field', 1);
		}
	}


	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;

		$this->element_id = preg_replace('/[\[+\]+]/', '_', $this->element_id);

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'text',
				'value' => str_replace('"', '&quot;', $this->value),
				'default' => $this->default,
				'attr' => 'aform_field_type="date" ' . $this->attr,
				'required' => $this->required,
				'style' => $this->style,
				'dateformat' => $this->dateformat,
				'highlight' => $this->highlight
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/date.tpl');
		return $return;
	}
}

class EmailHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'text',
				'value' => str_replace('"', '&quot;', $this->value),
				'default' => $this->default,
				'attr' => 'aform_field_type="email" ' . $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/input.tpl');
		return $return;
	}
}

class NumberHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'text',
				'value' => str_replace('"', '&quot;', $this->value),
				'default' => $this->default,
				'attr' => 'aform_field_type="number" ' . $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/input.tpl');
		return $return;
	}
}

class PhoneHtmlElement extends HtmlElement {

	public function getHtml() {

		if (!isset($this->default)) $this->default = '';
		if ($this->value == '' && !empty($this->default)) $this->value = $this->default;
		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'type' => 'text',
				'value' => str_replace('"', '&quot;', $this->value),
				'default' => $this->default,
				'attr' => 'aform_field_type="phone" ' . $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/input.tpl');
		return $return;
	}
}

class IPaddressHtmlElement extends HtmlElement {

	public function getHtml() {
		$this->view->batchAssign(
			array(
				'id' => $this->element_id,
				'name' => $this->name,
				'value' => $_SERVER[ 'REMOTE_ADDR' ],
				'attr' => 'aform_field_type="ipaddress" ' . $this->attr,
			)
		);
		$return = $this->view->fetch('form/hidden.tpl');
		return $return;
	}
}

class CountriesHtmlElement extends HtmlElement {

	public function __construct($data) {
		parent::__construct($data);
		$this->data[ 'registry' ]->get('load')->model('localisation/country');
		$results = $this->data[ 'registry' ]->get('model_localisation_country')->getCountries();
		$this->options = array();
		foreach ($results as $c) {
			$this->options[ $c[ 'name' ] ] = $c[ 'name' ];
		}
	}

	public function getHtml() {

		if (!is_array($this->value)) $this->value = array( $this->value => (string)$this->value );

		$this->options = !$this->options ? array() : $this->options;

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/selectbox.tpl');
		return $return;
	}

}

class ZonesHtmlElement extends HtmlElement {

	public function __construct($data) {
		parent::__construct($data);
		$this->data[ 'registry' ]->get('load')->model('localisation/country');
		$results = $this->data[ 'registry' ]->get('model_localisation_country')->getCountries();
		$this->options = array();
		foreach ($results as $c) {
			$this->options[ $c[ 'name' ] ] = $c[ 'name' ];
		}
	}

	public function getHtml() {

		if (!is_array($this->value)) $this->value = array( $this->value => (string)$this->value );

		$this->zone_name = !$this->zone_name ? '' : urlencode($this->zone_name);
		$this->options = !$this->options ? array() : $this->options;
		$this->element_id = preg_replace('/[\[+\]+]/', '_', $this->element_id);

		$html = new AHtml($this->data[ 'registry' ]);

		$this->view->batchAssign(
			array(
				'name' => $this->name,
				'id' => $this->element_id,
				'value' => $this->value,
				'options' => $this->options,
				'attr' => $this->attr,
				'required' => $this->required,
				'style' => $this->style,
				'url' => $html->getSecureURL('common/zone/names'),
				'zone_name' => $this->zone_name,
			)
		);
		if (!empty($this->help_url)) {
			$this->view->assign('help_url', $this->help_url);
		}
		$return = $this->view->fetch('form/countries_zones.tpl');
		return $return;
	}

}