<?php

/**
 * @file DownloadsWorldMapPlugin.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DownloadsWorldMapPlugin
 * @brief Plugin class for the DownloadsWorldMap plugin.
 */

namespace APP\plugins\generic\downloadsWorldMap;

use PKP\plugins\Hook;
use APP\core\Application;
use PKP\plugins\GenericPlugin;
use APP\plugins\generic\downloadsWorldMap\controllers\DownloadsWorldMapHandler;
class DownloadsWorldMapPlugin extends GenericPlugin
{
    public function getDisplayName()
    {
        return __('plugins.generic.downloadsWorldMap.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.downloadsWorldMap.description');
    }

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return $success;
        }
        if ($success && $this->getEnabled($mainContextId)) {
            // Registrar los hooks
            Hook::add('TemplateManager::display', [$this, 'injectMapIntoMonograph']); 
            Hook::add('TemplateManager::display', [$this, 'addLeafletAssets']); 
            Hook::add('LoadHandler', [$this,'setPageHandler']);

        }
        return $success;
    }

    // Añade los archivos js y css necesarios
    public function addLeafletAssets($hookName, $args)
    {

        $templateManager = $args[0];

        $templateManager->addJavaScript('leaflet-js','/'.$this->getPluginPath().'/js/leaflet.js"');
        $templateManager->addStyleSheet('map-css','/'.$this->getPluginPath().'/css/map.css"');
        $templateManager->addStyleSheet('leaflet-css','/'.$this->getPluginPath().'/css/leaflet.css"');
        $templateManager->addJavaScript('map-js','/'.$this->getPluginPath().'/js/map.js"');

    }


    // Asignar la plnatilla del plugin al manejador de plantillas
    public function injectMapIntoMonograph($hookName, $args)
    {
        $templateManager = $args[0];
      
        $mapHtml = $templateManager->fetch($this->getTemplateResource('downloadsWorldMap.tpl'));
        $templateManager->assign('downloadsWorldMapHtml', $mapHtml);
    }

    // Asignar manejador de plantillas 
    function setPageHandler($hookName, $params){

        $page=$params[1];
        
        if($page==='downloadsPerCountry'){
            define("HANDLER_CLASS",new DownloadsWorldMapHandler());
            return true;
        }
    }


}
?>