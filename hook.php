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

	// [INICIO] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso. 
	 
	function servicios_notMigratedDatetime() {
		global $DB;
		
		$result = $DB->request([
			//	'COUNT'       => 'cpt',
				'FROM'        => 'information_schema.columns',
				'WHERE'       => [
					 'information_schema.columns.table_schema' => $DB->dbdefault,
					 'information_schema.columns.table_name'   => ['LIKE', 'glpi\_plugin\_servicios\_%'],
					 'information_schema.columns.data_type'    => ['datetime']
				]
		]);
	
		while ($data = $result->next()) { 
	
			// Convert datetime to timestamp
			$query = "ALTER TABLE `".$data["TABLE_NAME"]."` MODIFY `".$data["COLUMN_NAME"]."` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;";
		//	echo $query;
			$DB->query($query);
				
		}
	
	}
	
	// [FINAL] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso.
	



function plugin_servicios_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/servicios/inc/profile.class.php");

   $update = false;
   if (!$DB->TableExists("glpi_plugin_servicios_servicios")) {

      $DB->runFile(GLPI_ROOT ."/plugins/servicios/sql/install_servicios_1.0.sql");
		
   }
   
   PluginServiciosProfile::initProfile();
   PluginServiciosProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   

   return true;
}


function plugin_servicios_uninstall() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/servicios/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/servicios/inc/menu.class.php");
   
   $tables = array("glpi_plugin_servicios_servicios",
                   "glpi_plugin_servicios_serviciotypes",
                   "glpi_plugin_servicios_servicios_items",
				   "glpi_plugin_servicios_autentications",
				   "glpi_plugin_servicios_criticidads",
				   "glpi_plugin_servicios_ensnivels",
				   "glpi_plugin_servicios_orientados"
				   );

   foreach($tables as $table) {
      if ($DB->tableExists($table)) {
         $DB->doQuery("DROP TABLE IF EXISTS `$table`");
      }
   }

   //old versions
   $tables = array("glpi_plugin_servicio",
                   "glpi_dropdown_plugin_servicio_type",
                   "glpi_dropdown_plugin_servicio_server_type",
                   "glpi_dropdown_plugin_servicio_technic",
                   "glpi_plugin_servicio_device",
                   "glpi_plugin_servicio_profiles",
                   "glpi_plugin_servicios_profiles");

   foreach($tables as $table) {
      if ($DB->tableExists($table)) {
         $DB->doQuery("DROP TABLE IF EXISTS `$table`");
      }
   }

   $tables_glpi = array("glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_bookmarks",
                        "glpi_logs",
                        "glpi_notepads");

   foreach($tables_glpi as $table_glpi) {
      // Use deleteOrDie() to delete rows matching criteria for GLPI 11 compatibility
      // Check if table exists before attempting deletion
      if ($DB->tableExists($table_glpi)) {
         $DB->deleteOrDie($table_glpi, [
            'itemtype' => 'PluginServiciosServicio'
         ]);
      }
   }

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype' => 'PluginServiciosServicio'));
   }
   
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginServiciosProfile::getAllRights(true) as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   PluginServiciosMenu::removeRightsFromSession();
   PluginServiciosProfile::removeRightsFromSession();

   return true;
}


// Define dropdown relations
function plugin_servicios_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("servicios")) {
      return array("glpi_plugin_servicios_serviciotypes"
                        => array("glpi_plugin_servicios_servicios"
                                    => "plugin_servicios_serviciotypes_id"),
                   "glpi_users"
                        => array("glpi_plugin_servicios_servicios" => "users_id_tech"),
                   "glpi_groups"
                        => array("glpi_plugin_servicios_servicios" => "groups_id_tech"),
                   "glpi_suppliers"
                        => array("glpi_plugin_servicios_servicios" => "suppliers_id"),
                   "glpi_locations"
                        => array("glpi_plugin_servicios_servicios" => "locations_id"),
                   "glpi_plugin_servicios_servicios"
                        => array("glpi_plugin_servicios_servicios_items"
                                    => "plugin_servicios_servicios_id"),
                   "glpi_entities"
                        => array("glpi_plugin_servicios_servicios"     => "entities_id",
                                 "glpi_plugin_servicios_serviciotypes" => "entities_id"));
   }
   return array();
}


// Define Dropdown tables to be manage in GLPI :
function plugin_servicios_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("servicios")) {
      return array('PluginServiciosServicioType'
                        => PluginServiciosServicioType::getTypeName(2),

                   'PluginServiciosOrientado'
                        => PluginServiciosOrientado::getTypeName(2),
						
                   'PluginServiciosEnsnivel'
                        => PluginServiciosEnsnivel::getTypeName(2),
                   'PluginServiciosCriticidad'
                        => PluginServiciosCriticidad::getTypeName(2));
   }
   return array();
}


function plugin_servicios_AssignToTicket($types) {

   if (Session::haveRight("plugin_servicios_open_ticket", "1")) {
      $types['PluginServiciosServicio'] = PluginServiciosServicio::getTypeName(2);
   }
   return $types;
}


////// SEARCH FUNCTIONS ///////() {

function plugin_servicios_getAddSearchOptions($itemtype) {

   $sopt = array();

   if (in_array($itemtype, PluginServiciosServicio::getTypes(true))) {
      
      if (Session::haveRight("plugin_servicios", READ)) {
         $sopt[1100]['table']          = 'glpi_plugin_servicios_servicios';
         $sopt[1100]['field']          = 'name';
         $sopt[1100]['name']           = PluginServiciosServicio::getTypeName(2)." - ".
                                         __('Name');
         $sopt[1100]['forcegroupby']   = true;
         $sopt[1100]['datatype']       = 'itemlink';
         $sopt[1100]['massiveaction']  = false;
         $sopt[1100]['itemlink_type']  = 'PluginServiciosServicio';
         $sopt[1100]['joinparams']     = array('beforejoin'
                                                   => array('table'      => 'glpi_plugin_servicios_servicios_items',
                                                            'joinparams' => array('jointype' => 'itemtype_item')));
                                                            
         $sopt[1101]['table']          = 'glpi_plugin_servicios_serviciotypes';
         $sopt[1101]['field']          = 'name';
         $sopt[1101]['name']           = PluginServiciosServicio::getTypeName(2)." - ".
                                         PluginServiciosServicioType::getTypeName(1);
         $sopt[1101]['forcegroupby']   = true;
         $sopt[1101]['datatype']       = 'dropdown';
         $sopt[1101]['massiveaction']  = false;
         $sopt[1101]['joinparams']     = array('beforejoin' => array(
                                                      array('table'      => 'glpi_plugin_servicios_servicios',
                                                            'joinparams' => $sopt[1100]['joinparams'])));
      }
   }

   return $sopt;
}

//display custom fields in the search
function plugin_servicios_giveItem($type, $ID, $data, $num) {
   global $CFG_GLPI, $DB;

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      //display associated items with servicios
      case "glpi_plugin_servicios_servicios_items.items_id" :
         $criteria = [
            'SELECT' => ['itemtype'],
            'DISTINCT' => true,
            'FROM' => 'glpi_plugin_servicios_servicios_items',
            'WHERE' => ['plugin_servicios_servicios_id' => $data['id']],
            'ORDER' => 'itemtype'
         ];
         $result_device = $DB->request($criteria);
         $number_device = count($result_device);
         $out = '';
         $servicios = $data['id'];
         if ($number_device > 0) {
            foreach ($result_device as $device_row) {
               $column = "name";
               $itemtype = $device_row["itemtype"];
               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

                  if ($itemtype != 'Entity') {
                     $criteria = [
                        'SELECT' => [$table_item.'.*', new QueryExpression('`glpi_plugin_servicios_servicios_items`.`id` AS table_items_id'), new QueryExpression('`glpi_entities`.`id` AS entity')],
                        'FROM' => 'glpi_plugin_servicios_servicios_items',
                        'INNER JOIN' => [
                           $table_item => [
                              'ON' => [
                                 'glpi_plugin_servicios_servicios_items' => 'items_id',
                                 $table_item => 'id'
                              ]
                           ]
                        ],
                        'LEFT JOIN' => [
                           'glpi_entities' => [
                              'ON' => [
                                 'glpi_entities' => 'id',
                                 $table_item => 'entities_id'
                              ]
                           ]
                        ],
                        'WHERE' => [
                           'glpi_plugin_servicios_servicios_items.itemtype' => $itemtype,
                           'glpi_plugin_servicios_servicios_items.plugin_servicios_servicios_id' => $servicios
                        ],
                        'ORDER' => ['glpi_entities.completename', $table_item.'.`'.$column.'`']
                     ];
                     if ($item->maybeTemplate()) {
                        $criteria['WHERE'][$table_item.'.is_template'] = 0;
                     }
                     $result_linked = $DB->request($criteria);

                  } else {
                     $criteria = [
                        'SELECT' => [$table_item.'.*', new QueryExpression('`glpi_plugin_servicios_servicios_items`.`id` AS table_items_id'), new QueryExpression('`glpi_entities`.`id` AS entity')],
                        'FROM' => 'glpi_plugin_servicios_servicios_items',
                        'INNER JOIN' => [
                           $table_item => [
                              'ON' => [
                                 'glpi_plugin_servicios_servicios_items' => 'items_id',
                                 $table_item => 'id'
                              ]
                           ]
                        ],
                        'LEFT JOIN' => [
                           'glpi_entities' => [
                              'ON' => [
                                 'glpi_entities' => 'id',
                                 $table_item => 'entities_id'
                              ]
                           ]
                        ],
                        'WHERE' => [
                           'glpi_plugin_servicios_servicios_items.itemtype' => $itemtype,
                           'glpi_plugin_servicios_servicios_items.plugin_servicios_servicios_id' => $servicios
                        ],
                        'ORDER' => ['glpi_entities.completename', $table_item.'.`'.$column.'`']
                     ];
                     if ($item->maybeTemplate()) {
                        $criteria['WHERE'][$table_item.'.is_template'] = 0;
                     }
                     $result_linked = $DB->request($criteria);
                  }
               
                  if ($result_linked && count($result_linked) > 0) {
                     $item = new $itemtype();
                     foreach ($result_linked as $datal) {
                        if ($item->getFromDB($datal['id'])) {
                           $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                        }
                     }
                  } else {
                     $out .= ' ';
                  }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_servicios_MassiveActions($type) {

   if (in_array($type,PluginServiciosServicio::getTypes(true))) {
      return array('PluginServiciosServicio'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_servicios_add_item' =>
                                                              __('Associate a servicio', 'servicios'));
   }
   return array();
}

/*
function plugin_servicios_MassiveActionsDisplay($options=array()) {

   $web = new PluginServiciosServicio();

   if (in_array($options['itemtype'], PluginServiciosServicio::getTypes(true))) {
      $web->dropdownservicios("plugin_servicios_servicios_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . _sx('button','Post') . "\" >";
   }
   return "";
}


function plugin_servicios_MassiveActionsProcess($data) {
   
   $web_item = new PluginServiciosServicio_Item();
   
   $res = array('ok' => 0,
               'ko' => 0,
               'noright' => 0);

   switch ($data['action']) {
      case "plugin_servicios_add_item":     
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_servicios_servicios_id' => $data['plugin_servicios_servicios_id'],
                        'items_id'      => $key,
                        'itemtype'      => $data['itemtype']);
               if ($web_item->can(-1,'w',$input)) {
                  if ($web_item->add($input)){
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            }
         }
         break;
   }
   return $res;
}
*/
function plugin_servicios_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['servicios'] = array();

   foreach (PluginServiciosServicio::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['servicios'][$type]
         = array('PluginServiciosServicio_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginServiciosServicio_Item');
   }
}


function plugin_datainjection_populate_servicios() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginServiciosServicioInjection'] = 'servicios';
}

function plugin_servicios_translatearight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return CREATE;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
}
?>