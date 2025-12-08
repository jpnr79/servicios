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

// Init the hooks of the plugins -Needed
function plugin_init_servicios() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['servicios'] = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['servicios']   = array('PluginServiciosProfile',
                                                                'initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['servicios'] = true;
 
   if (class_exists('PluginServiciosServicio_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['plugin_datainjection_populate']['servicios']
                                       = 'plugin_datainjection_populate_servicios';
   }

   // Params : plugin name - string type - number - class - table - form page
   Plugin::registerClass('PluginServiciosServicio',
                         array('linkgroup_tech_types'         => true,
                               'linkuser_tech_types'          => true,
                               'document_types'          => true,
                               'contract_types'          => true,
                               'ticket_types'            => true,
                               'helpdesk_visible_types'  => true,
                               'addtabon' => 'Supplier'));
   
   Plugin::registerClass('PluginServiciosProfile', array('addtabon' => array('Profile')));
   
   if (class_exists('PluginAccountsAccount')) {
      PluginAccountsAccount::registerType('PluginServiciosServicio');
   }
   
   if (class_exists('PluginCertificatesCertificate')) {
      PluginCertificatesCertificate::registerType('PluginServiciosServicio');
   }
   
   
   
   //if glpi is loaded
   if (Session::getLoginUserID()) {

      //if environment plugin is installed
      $plugin = new Plugin();
      if (!$plugin->isActivated('environment') 
         && Session::haveRight("plugin_servicios", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['servicios'] = array('assets'   => 'PluginServiciosMenu');
      }
      
      if (Session::haveRight("plugin_servicios", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['servicios']=1;
      }

      if (Session::haveRight("plugin_servicios", READ)
          || Session::haveRight("config",UPDATE)) {
       }

      // Import from Data_Injection plugin
//      $PLUGIN_HOOKS['migratetypes']['servicios']
 //                                   = 'plugin_datainjection_migratetypes_servicios';
      $PLUGIN_HOOKS['plugin_pdf']['PluginServiciosServicio']
                                 = 'PluginServiciosServicioPDF';
   }
   
   // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['servicios'] = 'plugin_servicios_postinit';
}


// Get the name and the version of the plugin - Needed
function plugin_version_servicios() {

   return array('name'          => _n('Servicio' , 'Servicios' ,2, 'servicios'),
                'version'        => '2.2',
                'license'        => 'GPLv2+',
                'oldname'        => 'servicio',
                'author'  		=> '<a href="http://www.carm.es">CARM</a>',
                'homepage'       =>'https://github.com/calidadcarm/servicios',
                'requirements'   => ['glpi' => ['min' => '11.0', 'max' => '12.0']]);
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_servicios_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'9.4','lt')) {
      echo "This plugin requires GLPI >= 9.4";
      return false;
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_servicios_check_config() {
   return true;
}

function plugin_datainjection_migratetypes_servicios($types) {

   $types[1300] = 'PluginServiciosServicio';
   return $types;
}

?>