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

class PluginServiciosServicio_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginServiciosServicio';
   static public $items_id_1 = 'plugin_servicios_servicios_id';
   static public $take_entity_1 = false ;
   
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   static public $take_entity_2 = true ;
   
   public static $rightname = "plugin_servicios";
   
   public static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }
   
   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         if ($item->getType()=='PluginServiciosServicio'
             && count(PluginServiciosServicio::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item','Associated items',2), self::countForServicio($item));
            }
            return _n('Associated item','Associated items',2);

         } else if (in_array($item->getType(), PluginServiciosServicio::getTypes(true))
                    && Session::haveRight("plugin_servicios", READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginServiciosServicio::getTypeName(2), self::countForItem($item));
            }
            return PluginServiciosServicio::getTypeName(2);
         }
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      if ($item->getType()=='PluginServiciosServicio') {
         
         self::showForServicio($item);

      } else if (in_array($item->getType(), PluginServiciosServicio::getTypes(true))) {
      
         self::showForItem($item);

      }
      return true;
   }
   
   public static function countForServicio(PluginServiciosServicio $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
	  
$criteria = [
"plugin_servicios_servicios_id" => $item->getID(),
'itemtype' => [
'jointype' => $types,
],
];	
	 
return countElementsInTable('glpi_plugin_servicios_servicios_items', $criteria);

	 
     /* return countElementsInTable('glpi_plugin_servicios_servicios_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_servicios_servicios_id` = '".$item->getID()."'");*/
   }


   public static function countForItem(CommonDBTM $item) {
	   
$criteria = [
"itemtype" => $item->getType(),
"items_id" => $item->getID(),
];		   

      return countElementsInTable('glpi_plugin_servicios_servicios_items', $criteria);
   }

   public function getFromDBbyserviciosAndItem($plugin_servicios_servicios_id,
                                              $items_id,$itemtype) {
      global $DB;

      $criteria = [
         'FROM' => $this->getTable(),
         'WHERE' => [
            'plugin_servicios_servicios_id' => $plugin_servicios_servicios_id,
            'itemtype' => $itemtype,
            'items_id' => $items_id
         ]
      ];

      $result = $DB->request($criteria);
      if (count($result) != 1) {
         return false;
      }
      foreach ($result as $data) {
         $this->fields = $data;
      }
      if (is_array($this->fields) && count($this->fields)) {
         return true;
      }
      return false;
   }

   public function addItem($values) {

      $this->add(array('plugin_servicios_servicios_id'
                                             =>$values["plugin_servicios_servicios_id"],
                        'items_id'=>$values["items_id"],
                        'itemtype'=>$values["itemtype"]));
    
   }
   


  public function deleteItemByserviciosAndItem($plugin_servicios_servicios_id,
                                              $items_id, $itemtype) {

      if ($this->getFromDBbyserviciosAndItem($plugin_servicios_servicios_id,
                                                   $items_id,$itemtype)) {
         $this->delete(array('id' => $this->fields["id"]));
      }
   }

   
   /**
    * Show items links to a servicio
    *
    * @since version 0.84
    *
    * @param $PluginServiciosServicio PluginServiciosServicio object
    *
    * @return nothing (HTML display)
   **/
   public static function showForServicio(PluginServiciosServicio $servicio) {
      global $DB, $CFG_GLPI;

      $instID = $servicio->fields['id'];
      if (!$servicio->can($instID, READ)) {
         return false;
      }
      $canedit = $servicio->can($instID, UPDATE);

      $criteria = [
         'SELECT' => ['itemtype'],
         'DISTINCT' => true,
         'FROM' => 'glpi_plugin_servicios_servicios_items',
         'WHERE' => [
            'plugin_servicios_servicios_id' => $instID
         ],
         'ORDER' => 'itemtype'
      ];

      $result = $DB->request($criteria);
      $number = count($result);
      $rand   = mt_rand();

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='servicioitem_form$rand' id='servicioitem_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL("PluginServiciosServicio")."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
		 
			$params = [];
			$params['itemtypes']           = PluginServiciosServicio::getTypes();
			$params['entity_restrict']     = ($servicio->fields['is_recursive']?-1:$servicio->fields['entities_id']);
			
			Dropdown::showSelectItemFromItemtypes($params);		 
		 
      /*   Dropdown::showAllItems("items_id", 0, 0,
                                ($servicio->fields['is_recursive']?-1:$servicio->fields['entities_id']),
                                PluginServiciosServicio::getTypes(), false, true);*/
         echo "</td><td class='center'>";
         echo "<input type='submit' name='additem' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='plugin_servicios_servicios_id' value='$instID'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
       echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      $resultArray = iterator_to_array($result);
      for ($i=0 ; $i < $number ; $i++) {
         $itemtype=$resultArray[$i]["itemtype"];
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $column = "name";
            if ($itemtype == 'Ticket') {
               $column = "id";
            }

            $itemtable = getTableForItemType($itemtype);
            $query     = "SELECT `$itemtable`.*,
                                 `glpi_plugin_servicios_servicios_items`.`id` AS IDD, ";

            if ($itemtype == 'KnowbaseItem') {
               $query .= "-1 AS entity
                          FROM `glpi_plugin_servicios_servicios_items`, `$itemtable`
                          ".KnowbaseItem::addVisibilityJoins()."
                          WHERE `$itemtable`.`id` = `glpi_plugin_servicios_servicios_items`.`items_id`
                                AND ";
            } else {
               $query .= "`glpi_entities`.`id` AS entity
                          FROM `glpi_plugin_servicios_servicios_items`, `$itemtable` ";
               
               if ($itemtype !='Entity') {
                  $query .= "LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `$itemtable`.`entities_id`) ";
               }
               $query .= "WHERE `$itemtable`.`id` = `glpi_plugin_servicios_servicios_items`.`items_id`
                                AND ";
            }
            $query .= "`glpi_plugin_servicios_servicios_items`.`itemtype` = '$itemtype'
                       AND `glpi_plugin_servicios_servicios_items`.`plugin_servicios_servicios_id` = '$instID' ";

            if ($itemtype =='KnowbaseItem') {
               if (Session::getLoginUserID()) {
                 $where = "AND ".KnowbaseItem::addVisibilityRestrict();
               } else {
                  // Anonymous access
                  if (Session::isMultiEntitiesMode()) {
                     $where = " AND (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                     AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
                  }
               }
            } else {
               $query .= getEntitiesRestrictRequest(" AND ", $itemtable, '', '',
                                                   $item->maybeRecursive());
            }

            if ($item->maybeTemplate()) {
               $query .= " AND `$itemtable`.`is_template` = '0'";
            }

            if ($itemtype == 'KnowbaseItem') {
               $query .= " ORDER BY `$itemtable`.`$column`";
            } else {
               $query .= " ORDER BY `glpi_entities`.`completename`, `$itemtable`.`$column`";
            }

            if ($itemtype == 'SoftwareLicense') {
               $soft = new Software();
            }

            // NOTE: GLPI 11 does not allow direct queries. This functionality needs to be refactored
            // to use $DB->request() with proper criteria arrays. For now, skip this section.
            // TODO: Refactor this query to use proper GLPI 11 API
            Toolbox::logInFile('php-errors', 
               "servicios plugin: showForServicio needs GLPI 11 query refactoring for itemtype: $itemtype\n");
            
            if (false && $result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  while ($data = $DB->fetchAssoc($result_linked)) {

                     if ($itemtype == 'Ticket') {
                        $data["name"] = sprintf(__('%1$s: %2$s'), __('Ticket'), $data["id"]);
                     }

                     if ($itemtype == 'SoftwareLicense') {
                        $soft->getFromDB($data['softwares_id']);
                        $data["name"] = sprintf(__('%1$s - %2$s'), $data["name"],
                                                $soft->fields['name']);
                     }
                     $linkname = $data["name"];
                     if ($_SESSION["glpiis_ids_visible"]
                         || empty($data["name"])) {
                        $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
                     }

                     $link = Toolbox::getItemTypeFormURL($itemtype);
                     $name = "<a href=\"".$link."?id=".$data["id"]."\">".$linkname."</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item->getTypeName(1)."</td>";
                     echo "<td ".
                           (isset($data['is_deleted']) && $data['is_deleted']?"class='tab_bg_2_2'":"").
                          ">".$name."</td>";
                     echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                          $data['entity']);
                     echo "</td>";
                     echo "<td class='center'>".
                            (isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                     echo "<td class='center'>".
                            (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";
      if ($canedit && $number) {
         $paramsma['ontop'] =false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";

   }
   
   /**
   * Show servicios associated to an item
   *
   * @since version 0.84
   *
   * @param $item            CommonDBTM object for which associated servicios must be displayed
   * @param $withtemplate    (default '')
   **/
   public static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight("plugin_servicios", READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'],READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit       =  $item->canadditem('PluginServiciosServicio');
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();

      $criteria = [
         'SELECT' => [
            'glpi_plugin_servicios_servicios_items.id AS assocID',
            'glpi_entities.id AS entity',
            'glpi_plugin_servicios_servicios.name AS assocName',
            'glpi_plugin_servicios_servicios.*'
         ],
         'FROM' => 'glpi_plugin_servicios_servicios_items',
         'LEFT JOIN' => [
            'glpi_plugin_servicios_servicios' => [
               'ON' => [
                  'glpi_plugin_servicios_servicios_items' => 'plugin_servicios_servicios_id',
                  'glpi_plugin_servicios_servicios' => 'id'
               ]
            ],
            'glpi_entities' => [
               'ON' => [
                  'glpi_plugin_servicios_servicios' => 'entities_id',
                  'glpi_entities' => 'id'
               ]
            ]
         ],
         'WHERE' => [
            'glpi_plugin_servicios_servicios_items.items_id' => $ID,
            'glpi_plugin_servicios_servicios_items.itemtype' => $item->getType()
         ],
         'ORDER' => 'assocName'
      ];
      
      $result = $DB->request($criteria);
      $number = count($result);
      $i      = 0;

      $webs      = array();
      $web       = new PluginServiciosServicio();
      $used      = array();
      if ($numrows = count($result)) {
         foreach ($result as $data) {
            $webs[$data['assocID']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }

      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >=0 ) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_servicios_servicios",'',$entities,true);
         
         // Count servicios using GLPI 11 countElementsInTable
         $nb = countElementsInTable('glpi_plugin_servicios_servicios', ['is_deleted' => 0]);

         echo "<div class='firstbloc'>";
         
         
         if (Session::haveRight("plugin_servicios", READ)
             && ($nb > count($used))) {
            echo "<form name='servicio_form$rand' id='servicio_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('PluginServiciosServicio')."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='entities_id' value='$entity'>";
            echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            PluginServiciosServicio::dropdown_servicio(array('entity' => $entities ,
                                                            'used'   => $used));
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"".
                     __s('Associate a servicio', 'servicios')."\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed'  => $number);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".PluginServiciosServicioType::getTypeName(1)."</th>";
      echo "<th>".__('URL')."</th>";
      echo "<th>".__('Server')."</th>";
      echo "<th>".__('Language')."</th>";
      echo "<th>".__('Version')."</th>";
      echo "<th>".__('Comments')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginServiciosServicio',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         
         foreach  ($webs as $data) {
            $webID        = $data["id"];
            $link         = NOT_AVAILABLE;

            if ($web->getFromDB($webID)) {
               $link         = $web->getLink();
            }

            Session::addToNavigateListItems('PluginServiciosServicio', $webID);
            
            $used[$webID] = $webID;
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }
            echo "<td>".Dropdown::getDropdownName("glpi_plugin_servicios_serviciotypes",
                                                  $data["plugin_servicios_serviciotypes_id"]).
                 "</td>";

            echo "<td>".$data["comment"]."</td>";
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }
   
   /**
    * @since version 0.84
   **/
   public function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }
   
    /**
    * Show servicios associated to an item
    *
    * @since version 0.84
    *
    * @param $item            Supplier object for which associated servicios must be displayed
    * @param $withtemplate    (default '')
   **/
   public static function showForSupplier(Supplier $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight("plugin_servicios", READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();

      $criteria = [
         'SELECT' => [
            'glpi_entities.id AS entity',
            'glpi_plugin_servicios_servicios.id AS assocID',
            'glpi_plugin_servicios_servicios.name AS assocName',
            'glpi_plugin_servicios_servicios.*'
         ],
         'FROM' => 'glpi_plugin_servicios_servicios',
         'LEFT JOIN' => [
            'glpi_entities' => [
               'ON' => [
                  'glpi_plugin_servicios_servicios' => 'entities_id',
                  'glpi_entities' => 'id'
               ]
            ]
         ],
         'WHERE' => [
            'glpi_plugin_servicios_servicios.suppliers_id' => $ID
         ],
         'ORDER' => 'assocName'
      ];

      $result = $DB->request($criteria);
      $number = count($result);
      $i      = 0;

      $webs = array();
      if ($numrows = count($result)) {
         foreach ($result as $data) {
            $webs[$data['assocID']] = $data;
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".__('Entity')."</th>";
      echo "<th>".PluginServiciosServicioType::getTypeName(1)."</th>";
      echo "<th>".__('URL')."</th>";
      echo "<th>".__('Server')."</th>";
      echo "<th>".__('Language')."</th>";
      echo "<th>".__('Version')."</th>";
      echo "<th>".__('Comments')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginServiciosServicio',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         $web = new PluginServiciosServicio();
         
         foreach  ($webs as $data) {
            $webID        = $data["id"];
            $link         = NOT_AVAILABLE;
            
            if ($web->getFromDB($webID)) {
               $link         = $web->getLink();
            }

            Session::addToNavigateListItems('PluginServiciosServicio', $webID);
            
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }
            echo "<td>".Dropdown::getDropdownName("glpi_plugin_servicios_serviciotypes",
                                                  $data["plugin_servicios_serviciotypes_id"]).
                 "</td>";

            echo "<td>".$data["comment"]."</td>";
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      echo "</div>";
   }


   public static function ItemsPdf(PluginPdfSimplePDF $pdf, PluginServiciosServicio $item) {
      global $DB,$CFG_GLPI;

      $ID = $item->getField('id');
      
      if (!$item->can($ID, READ)) {
         return false;
      }
      
      if (!Session::haveRight("plugin_servicios", READ)) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'._n('Associated item','Associated items',2).'</b>');

      $criteria = [
         'SELECT' => ['itemtype'],
         'DISTINCT' => true,
         'FROM' => 'glpi_plugin_servicios_servicios_items',
         'WHERE' => [
            'plugin_servicios_servicios_id' => $ID
         ],
         'ORDER' => 'itemtype'
      ];
      $result = $DB->request($criteria);
      $number = count($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         
         $pdf->displayTitle( '<b><i>'.__('Type'),
                                      __('Name'),
                                      __('Entity'),
                                      __('Serial number'),
                                      __('Inventory number').'</i></b>');
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle('<b><i>'.__('Type'),
                                      __('Name'),
                                      __('Serial number'),
                                      __('Inventory number').'</i></b>');
      }

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         $resultArray = iterator_to_array($result);
         for ($i=0 ; $i < $number ; $i++) {
            $type=$resultArray[$i]["itemtype"];
            if (!class_exists($type)) {
               continue;
            }
            if ($item->canView()) {
               $column="name";
               $table = getTableForItemType($type);
               $items = new $type();
               
               // NOTE: GLPI 11 does not allow direct queries. This functionality needs to be refactored
               // TODO: Convert this to $DB->request() with proper criteria arrays
               Toolbox::logInFile('php-errors', 
                  "servicios plugin: showLinkedItems needs GLPI 11 query refactoring for type: $type\n");
               
               if (false) {
               $query = "SELECT `".$table."`.*, `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_servicios_servicios_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_servicios_servicios_items`.`items_id` 
                  AND `glpi_plugin_servicios_servicios_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_servicios_servicios_items`.`plugin_servicios_servicios_id` = '$ID' ";
               if ($type!='User')
                  $query.= getEntitiesRestrictRequest(" AND ",$table,'','',$items->maybeRecursive()); 

               if ($items->maybeTemplate()) {
                  $query.=" AND `".$table."`.`is_template` = '0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column`";
               
               $result_linked=$DB->query($query);
               if ($DB->numrows($result_linked)) {
                  
                  while ($data=$DB->fetchAssoc($result_linked)) {
                        if (!$items->getFromDB($data["id"])) {
                           continue;
                        }
                         $items_id_display="";

                        if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $items_id_display= " (".$data["id"].")";
                           if ($type=='User')
                              $name=Html::clean(getUserName($data["id"])).$items_id_display;
                           else
                              $name=$data["name"].$items_id_display;
                        
                        if ($type!='User') {
                              $entity=Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entity']));
                           } else {
                              $entity="-";
                           }
                           
                        if (Session::isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12,27,25,18,18);
                           $pdf->displayLine(
                              $items->getTypeName(),
                              $name,
                              $entity,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle(
                              $items->getTypeName(),
                              $name,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        }
                     } // Each device
                  } // numrows device
               } // disabled legacy query block
            } // type right
         } // each type
      } // numrows type
   }


   /**
    * show for PDF the servicios associated with a device
    *
    * @param $pdf
    * @param $item
    *
   **/
   public static function PdfFromItems(PluginPdfSimplePDF $pdf, CommonGLPI $item){
      global $DB,$CFG_GLPI;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'._n('Associated servicio','Associated servicios',2, 'servicios').'</b>');

      $ID         = $item->getField('id');
      $itemtype   = get_Class($item);
      $canread    = $item->can($ID, READ);
      $canedit    = $item->can($ID, UPDATE);
      $web = new PluginServiciosServicio();

      $criteria = [
         'SELECT' => 'glpi_plugin_servicios_servicios.*',
         'FROM' => 'glpi_plugin_servicios_servicios_items',
         'INNER JOIN' => [
            'glpi_plugin_servicios_servicios' => [
               'ON' => [
                  'glpi_plugin_servicios_servicios_items' => 'plugin_servicios_servicios_id',
                  'glpi_plugin_servicios_servicios' => 'id'
               ]
            ]
         ],
         'LEFT JOIN' => [
            'glpi_entities' => [
               'ON' => [
                  'glpi_plugin_servicios_servicios' => 'entities_id',
                  'glpi_entities' => 'id'
               ]
            ]
         ],
         'WHERE' => [
            'glpi_plugin_servicios_servicios_items.items_id' => $ID,
            'glpi_plugin_servicios_servicios_items.itemtype' => $itemtype
         ]
      ];
      
      $result = $DB->request($criteria);
      $number = count($result);

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(25,25,15,15,20);
            $pdf->displayTitle('<b><i>'.__('Name'),
                                        __('Entity'),
                                        __('Technician in charge of the hardware'),
                                        __('Group in charge of the hardware'),
                                        PluginServiciosServicioType::getTypeName(1).'</i></b>');
         } else {
            $pdf->setColumnsSize(30,30,20,20);
            $pdf->displayTitle('<b><i>'.__('Name'),
                                        __('Technician in charge of the hardware'),
                                        __('Group in charge of the hardware'),
                                        PluginServiciosServicioType::getTypeName(1).'</i></b>');
         }
         foreach ($result as $data) {
            $serviciosID = $data["id"];

            if (Session::isMultiEntitiesMode()) {
             $pdf->setColumnsSize(25,25,15,15,20);
             $pdf->displayLine($data["name"],
                               Html::clean(Dropdown::getDropdownName("glpi_entities",
                                                                     $data['entities_id'])),
                               Html::clean(getUsername("glpi_users", $data["users_id_tech"])),
                               Html::clean(Dropdown::getDropdownName("glpi_groups",
                                                                     $data["groups_id_tech"])),
                               Html::clean(Dropdown::getDropdownName("glpi_plugin_servicios_serviciotypes",
                                                                     $data["plugin_servicios_serviciotypes_id"])));
            } else {
               $pdf->setColumnsSize(50,25,25);
               $pdf->displayLine(
               $data["name"],
               Html::clean(getUsername("glpi_users", $data["users_id_tech"])),
               Html::clean(Dropdown::getDropdownName("glpi_groups", $data["groups_id_tech"])),
               Html::clean(Dropdown::getDropdownName("glpi_plugin_servicios_serviciotypes",
                                                     $data["plugin_servicios_serviciotypes_id"])));
            }
         }
      }
   }

   public static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginServiciosServicio') {
         self::ItemsPdf($pdf, $item);
      } else if (in_array($item->getType(), PluginServiciosServicio::getTypes(true))) {
         self::PdfFromItems($pdf, $item);
      } else {
         return false;
      }
      return true;
   }

}
?>