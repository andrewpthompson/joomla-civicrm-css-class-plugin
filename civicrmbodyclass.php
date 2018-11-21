<?php

/*
  +--------------------------------------------------------------------+
  | CiviCRM version 5                                                  |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2018                                |
  +--------------------------------------------------------------------+
  | This file is a part of CiviCRM.                                    |
  |                                                                    |
  | CiviCRM is free software; you can copy, modify, and distribute it  |
  | under the terms of the GNU Affero General Public License           |
  | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
  |                                                                    |
  | CiviCRM is distributed in the hope that it will be useful, but     |
  | WITHOUT ANY WARRANTY; without even the implied warranty of         |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
  | See the GNU Affero General Public License for more details.        |
  |                                                                    |
  | You should have received a copy of the GNU Affero General Public   |
  | License and the CiviCRM Licensing Exception along                  |
  | with this program; if not, contact CiviCRM LLC                     |
  | at info[AT]civicrm[DOT]org. If you have questions about the        |
  | GNU Affero General Public License or the licensing of CiviCRM,     |
  | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
  +--------------------------------------------------------------------+
 */

/**
 * Joomla plugin for allowing CiviCRM to add a CSS class to the body element
 *
 * @since  3.8
 */
// No direct access.
defined('_JEXEC') or die;

class plgSystemCivicrmbodyclass extends JPlugin {

  /**
   * Constructor.
   *
   * @param   object  &$subject  The object to observe.
   * @param   array   $config	An optional associative array of configuration settings.
   *
   * @since   3.8
   */
  public function __construct(&$subject, $config) {
    // Call the parent Constructor
    parent::__construct($subject, $config);
  }

  /**
   * Listener for the `onAfterRender` event
   * Appends a CSS 'class' to the <body> tag. The class name is derived from the
   * 'task' URL argument.
   *
   * @return  void
   *
   * @since   3.8
   */
  public function onAfterRender() {
    $app = JFactory::getApplication();
    // Get the rendered HTML
    $html = $app->getBody();

    // Get the task URL argument
    // FIXME: is there a better way of getting the information to form the
    // required class name from CiviCRM?
    $task = $app->input->get('task', '', 'PATH');
    $option = $app->input->get('option', '', 'STRING');

    if ($option == 'com_civicrm') {
      if (strpos($task, 'civicrm') !== false) {
        // Create the new class name from the task URL argument
        $class = 'page-' . str_replace('/', '-', $task);
        
        // Angular pages don't get a meaningful class name but this at least
        // makes it consistent with Drupal by removing the trailing hyphen
        $class = preg_replace('/-a-$/', '-a', $class);
        
        // Make the prefix 'civi-page' instead of 'page-civicrm'
        $class = preg_replace('/^page-civicrm/', 'civi-page', $class);
      }
      else {
        // In the case of the dashboard there might be no task argument in the URL
        $class = 'civi-page-dashboard';
      }

      // Use preg_replace to add the new class to the existing body class
      // attribute. This could be slightly brittle in that it would fail if
      // 'class' is not immediately after '<body ' and class needs to exist
      // already; this is determined by the template. Note: PHP's DOMDocument
      // did not work reliably - it broke some of CiviCRM's inline scripts.
      // QueryPath or PHPQuery could be alternatives but were not tested.
      $html = preg_replace('/<body(.*)(class=("|\')[^("|\')]*)/', "<body$1$2 $class", $html);

      // Write the modified HTML to the CMS
      $app->setBody($html);
    }
  }

}
