<?php
/*
GisClient map browser

Copyright (C) 2008 - 2009  Roberto Starnini - Gis & Web S.r.l. -info@gisweb.it

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/
/************ Session Name ********/
define('GC_SESSION_NAME', 'gisclient3'); // se definito, viene chiamato session_name() prima di session_start();

ini_set('max_execution_time',90);
ini_set('memory_limit','512M');
error_reporting  (E_ALL);

define('LONG_EXECUTIONE_TIME',300);
define('LONG_EXECUTION_MEMORY','512M');

/*******************Installation path *************************/
define('ROOT_PATH', realpath(__DIR__.'/..').'/');
define('PUBLIC_URL', 'http://localhost/');
define('MAP_URL', 'http://localhost/gisclient-3.0/template/');
define('IMAGE_PATH','/ms4w/apps/tmp/ms_tmp/');
define('IMAGE_URL','/tmp/');
define('TILES_CACHE','/ms4w/apps/gisclient-3.0/public/services/tms/');
define('OPENLAYERS','http://openlayers.org/dev/OpenLayers.js');
//define('PROJ_LIB',"/msiis/proj/nad/");
/*******************                  *************************/

/*******************OWS service url *************************/
define('GISCLIENT_OWS_URL','http://localhost/gisclient/services/ows.php');//NON E' OBBLIGATORIO
define('GISCLIENT_TMS_URL','http://localhost/gisclient/services/tms/');//NON E' OBBLIGATORIO
define('MAPSERVER_URL', 'http://192.168.0.13/cgi-bin/mapserv?'); //NON E' OBBLIGATORIO (serve per le richieste WFS di OpenLayers, quando il loadparams non funziona, vedi ows.php commento #LOADPARAMS)
/*******************                  *************************/

/**************** PRINT - EXPORT ***************/
define('GC_PRINT_TPL_DIR', ROOT_PATH.'public/services/print/');
define('GC_PRINT_TPL_URL', PUBLIC_URL.'services/print/');
define('GC_PRINT_IMAGE_SIZE_INI', ROOT_PATH.'config/print_image_size.ini');
define('GC_WEB_TMP_DIR', ROOT_PATH.'public/services/tmp/');
define('GC_WEB_TMP_URL', PUBLIC_URL.'services/tmp/');
define('GC_PRINT_LOGO_SX', 'http://localhost/images/logo_sx.gif');
define('GC_PRINT_LOGO_DX', 'http://localhost/images/logo_dx.gif');
define('GC_FOP_LIB', '/lib/fop.php');
define('GC_PRINT_SAVE_IMAGE', true); // baco mapscript: il saveImage a volte funziona solo specificando il nome del file, altre volte funziona solo se NON si specifica il nome del file
define('PRINT_RELATIVE_URL_PREFIX', 'http://localhost'); // se GISCLIENT_OWS_URL è relativo, questo prefisso viene aggiunto in fase di stampa

/****** print vectors ********/
define('PRINT_VECTORS_TABLE', 'print_vectors');
define('PRINT_VECTORS_SRID', 32632);

/******************* TINYOWS **************/
define('TINYOWS_PATH', '/var/www/cgi-bin');
define('TINYOWS_EXEC', 'tinyows');
define('TINYOWS_SCHEMA_DIR', '/usr/share/tinyows/schema/');
define('TINYOWS_ONLINE_RESOURCE', PUBLIC_URL.'services/tinyows/');

/*************  REDLINE ***************/
define('REDLINE_SCHEMA', 'public'); //non obbligatorio, default public
define('REDLINE_TABLE', 'annotazioni');
define('REDLINE_SRID', '4326');
define('REDLINE_FONT', 'arial'); // non obbligatorio, default arial

require_once (ROOT_PATH."lib/postgres.php");
require_once (ROOT_PATH."lib/debug.php");
require_once (ROOT_PATH."config/config.db.php");
require_once (ROOT_PATH.'lib/gcapp.class.php');

//Author
define('ADMIN_PATH',ROOT_PATH.'public/admin/');

//debug
if(!defined('DEBUG_DIR')) define('DEBUG_DIR',ROOT_PATH.'config/debug/');
if(!defined('DEBUG')) define('DEBUG', 1); // Debugging 0 off 1 on

require_once (ROOT_PATH."config/login.php");

//COSTANTI DEI REPORT
define('MAX_REPORT_ROWS',5000);
define('REPORT_PROJECT_NAME','REPORT');
define('REPORT_MAPSET_NAME','report');
define('FONT_LIST','fonts');
define('MS_VERSION','');

define('CATALOG_EXT','SHP,TIFF,TIF,ECW');//elenco delle estensioni caricabili sul layer
define('DEFAULT_ZOOM_BUFFER',100);//buffer di zoom in metri in caso non venga specificato layer.tolerance
define('MAX_HISTORY',6);//massimo numero di viste memorizzate
define('MAX_OBJ_SELECTED',2000);//massimo numero di oggetti selezionabili
define('WIDTH_SELECTION', 4);//larghezza della polilinea di selezione
define('TRASP_SELECTION', 50);//trasparenza della polilinea di selezione
define('COLOR_SELECTION', '255 0 255');//colore della polilinea di selezione
define('MAP_BG_COLOR', '255 255 255');//colore dello sfondo per default
define('EDIT_BUTTON', 'edit');

define('DEFAULT_TOLERANCE',4);//Raggio di ricerca in caso non venga specificato layer.tolerance
define('LAYER_SELECTION','__sel_layer');//Nome per i layer di selezione
define('LAYER_IMAGELABEL','__image_label');//Nome per il layer testo sulla mappa
define('LAYER_READLINE','__readline_layer');
define('DATALAYER_ALIAS_TABLE','__data__');//nome riservato ad alias per il nome della tabella del layer (usato dal sistema nelle query, non ci devono essere tabelle con questo nome)
define('WRAP_READLINE','\\');
define('COLOR_REDLINE','0 0 255');//Colore Line di contorno oggetti poligono o linea selezionati
define('OBJ_COLOR_SELECTION','255 255 0');//Colore Line di contorno oggetti poligono o linea selezionati
define('MAP_DPI',72);//Mapserver map resolution
define('TILE_SIZE',256);//Mapserver map resolution
// define('SERVICE_MAX_RESOLUTION',156543.03392812); // WMTS: Calcolare in base al valore presente nel campo ScaleDenominator del GetCapabilities (nella TileMatrix 0)
// define('SERVICE_MIN_ZOOM_LEVEL',7); // WMTS: min zoom level (default: 0 for google maps)
// define('SERVICE_MAX_ZOOM_LEVEL',19); // WMTS: max zoom level (default: 21 for google maps)
define('PDF_K',2);//Mapserver map resolution

define('SCALE','1000000,500000,250000,100000,50000,25000,10000,7500,5000,4000,3000,2000,1000,900,800,700,600,500,400,300,200,100,50');
define('GMAP_MIN_ZOOM_LEVEL',6);


//Icone della legenda
define('LEGEND_ICON_W',24);
define('LEGEND_ICON_H',16);
define('LEGEND_POINT_SIZE',15);
define('LEGEND_LINE_WIDTH',1);
define('LEGEND_POLYGON_WIDTH',2);
define('PRINT_PDF_FONT','times');

//custom tab files
//define('TAB_DIR','geoweb');

define('USE_DATA_IMPORT', false);
// 
//define('LAST_EDIT_USER_COL_NAME', 'gc_last_edit_user'); //funzione per aggiungere una colonna che in wfs-t viene popolata automaticamente con il nome utente di chi ha fatto l'ultima modifica
//define('LAST_EDIT_DATE_COL_NAME', 'gc_last_edit_date'); //come sopra, ma con la data
//define('MEASURE_AREA_COL_NAME', 'gc_area'); //funzione per aggiungere una colonna che viene popolata automaticamente con l'area del poligono editato
//define('MEASURE_LENGTH_COL_NAME', 'gc_length'); //come sopra, ma con la lunghezza

//define('CURRENT_EDITING_USER_TABLE', 'gc_current_editing_user'); // tabella che contiene il nome dell'utente attuale

define('CLIENT_LOGO', null);

define('MAPFILE_MAX_SIZE', '4096');

// Cache in ows.php
define('OWS_CACHE_TTL', 60); // Map cache (Prevent OL bug for multiple request)
define('OWS_CACHE_TTL_OPEN', 4*60*60); // Map cache for the 1st open of the map
//define('DYNAMIC_LAYERS', ''); // comma separated list of dynamic layers (same url different result)

$GEOLOCATOR_CONFIG = array(
    'pei' => array(
        'namefield'=>'search_name',
        'idfield'=>'id',
        'geomfield'=>'the_geom',
        'tablename'=>'common.map_search',
        'order'=>' order_id, search_name'
    )
);

