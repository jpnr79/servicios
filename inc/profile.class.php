<?php
/*
 * @version $Id: HEADER 15930 2020-01-10 14:40:00Z JDMZ$
 -------------------------------------------------------------------------
 Servicios plugin for GLPI
 Copyright (C) 2020 by the CARM Development Team.

 https://github.com/calidadcarm/servicios
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Servicios.

 Servicios is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Servicios is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Servicios. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginServiciosProfile extends Profile {

   static $rightname = "profile";

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile') {
            return PluginServiciosServicio::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, 
                                    array('plugin_servicios'               => 0,
                                          'plugin_servicios_open_ticket'   => 0,
										  'plugin_servicios_security_fields'   => 0,
										  'plugin_servicios_general_fields'   => 0));
         $prof->showForm($ID);
      }
      return true;
   }
   
   static function createFirstAccess($ID) {
      //85
      self::addDefaultProfileInfos($ID,
                                    array('plugin_servicios'               => 1001,
                                          'plugin_servicios_open_ticket'     => 1,
										  'plugin_servicios_security_fields'   => 1,
										  'plugin_servicios_general_fields'   => 1), true);
   }
   
    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
		  
$criteria = [
"profiles_id" => $profiles_id,
"name" => $right,
];		  
		  
         if (countElementsInTable('glpi_profilerights',
                                   $criteria) && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights',
                                   $criteria)) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }


   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>".__('Helpdesk')."</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($profiles_id, array('plugin_servicios_open_ticket'));
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Associable items to a ticket')."</td>";
      echo "<td>";
      Html::showCheckbox(array('name'    => '_plugin_servicios_open_ticket',
                               'checked' => $effective_rights['plugin_servicios_open_ticket']));
      echo "</td>";
	  $effective_rights = ProfileRight::getProfileRights($profiles_id, array('plugin_servicios_general_fields'));
      echo "<td width='20%'>".__('Modificacion de atributos generales')."</td>";	  
      echo "<td>";
	  Html::showCheckbox(array('name'    => '_plugin_servicios_general_fields',
                               'checked' => $effective_rights['plugin_servicios_general_fields']));	  
      echo "</td>";	  

	  $effective_rights = ProfileRight::getProfileRights($profiles_id, array('plugin_servicios_security_fields'));
      echo "<td width='20%'>".__('Modificacion atributo de seguridad')."</td>";	  
      echo "<td>";
	  Html::showCheckbox(array('name'    => '_plugin_servicios_security_fields',
                               'checked' => $effective_rights['plugin_servicios_security_fields']));	  
      echo "</td>";	 	  
	  echo "</tr>\n";
      echo "</table>";
      
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights($all = false) {
      $rights = array(
          array('itemtype'  => 'PluginServiciosServicio',
                'label'     => _n('Servicio', 'Servicios', 2, 'servicios'),
                'field'     => 'plugin_servicios'
          ),
      );

      if ($all) {
         $rights[] = array('itemtype' => 'PluginServiciosServicio',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_servicios_open_ticket');
						   
         $rights[] = array('itemtype' => 'PluginServiciosServicio',
                           'label'    =>  __('Modificar atributos generales', 'Modificar atributos generales'),
                           'field'    => 'plugin_servicios_general_fields');
						   
		$rights[] = array('itemtype' => 'PluginServiciosServicio',
                           'label'    =>  __('Modificar atributos de seguridad', 'Modificar atributos de seguridad'),
                           'field'    => 'plugin_servicios_security_fields');	

							   
      }
      
      return $rights;
   }

   /**
    * Init profiles
    *
    **/
    
   static function translateARight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
   
   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->TableExists('glpi_plugin_servicios_profiles')) {
      return true;
      }
      
      foreach ($DB->request('glpi_plugin_servicios_profiles', 
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching = array('servicios'    => 'plugin_servicios', 
                           'open_ticket' => 'plugin_servicios_open_ticket',
						   'general_fields' => 'plugin_servicios_general_fields',
						   'security_fields' => 'plugin_servicios_security_fields');
         $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
         foreach ($matching as $old => $new) {
            if (!isset($current_rights[$old])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='".self::translateARight($profile_data[$old])."' 
                         WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
               $DB->query($query);
            }
         }
      }
   }
   
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
		  
$criteria = [
"name" => $data['field'],
];		  
		  
         if (countElementsInTable("glpi_profilerights", 
                                  $criteria) == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
      
      //Migration old rights in new ones
      foreach ($DB->request('glpi_profiles') as $prof) {
         self::migrateOneProfile($prof['id']);
      }

      // If the active profile has no servicios rights set, grant full rights to admins
      // so the menu shows the add button without manual profile tweaks.
      if (Session::haveRight('config', UPDATE)) {
         $activeProfileId = $_SESSION['glpiactiveprofile']['id'];
         $current = ProfileRight::getProfileRights($activeProfileId, array('plugin_servicios'));
         if (isset($current['plugin_servicios']) && $current['plugin_servicios'] == 0) {
            $full = READ | UPDATE | CREATE | DELETE | PURGE;
            $pr = new ProfileRight();
            $pr->updateByCriteria(
               array('profiles_id' => $activeProfileId, 'name' => 'plugin_servicios'),
               array('rights' => $full)
            );
            $_SESSION['glpiactiveprofile']['plugin_servicios'] = $full;
         }
      }

      foreach ($DB->request([
         'FROM' => 'glpi_profilerights',
         'WHERE' => [
            'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
            'name' => ['LIKE', '%plugin_servicios%']
         ]
      ]) as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights']; 
      }
   }

   
   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}

?>