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
      } else {
        // In the case of the dashboard there might be no task argument in the URL
        $class = 'page-civicrm';
      }

      // Use PHP's DOMDocument to modify the body element's class attribute.
      // When loading the HTML, prevent parsing errors from being displayed.
      $dom = new DOMDocument;
      $useErrors = libxml_use_internal_errors(true);
      $dom->loadHTML($html);
      libxml_use_internal_errors($useErrors);

      // Get the body element and if found add the new class.
      $body = $dom->getElementsByTagName('body');
      if ($body && $body->length > 0) {
        $body = $body->item(0);
        $origClass = $body->getAttribute('class');
        $class = $origClass . " " . $class;
        $body->setAttribute('class', $class);
      }

      // Write the modified HTML to the CMS
      $html = $dom->saveHTML();
      $app->setBody($html);
    }
  }

}
