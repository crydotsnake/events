<?php

namespace Alexplusde\Events;

use rex_addon;
use rex;
use rex_extension;
use rex_yform_manager_dataset;
use rex_yform_manager_table;
use rex_config;
use rex_extension_point;
use rex_be_controller;
use rex_view;
use rex_cronjob_manager;
use rex_plugin;
use rex_csrf_token;
use rex_url;


if (rex::isBackend()) {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
        $suchmuster = 'class="###events-settings-editor###"';
        $ersetzen = rex_config::get("events", "editor") ?? 'class="form-control"';
        $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
    });
}

// rex_extension::register('REX_YFORM_SAVED', ['event_registration', 'ep_saved'], rex_extension::LATE);

if (rex::isBackend() && rex_be_controller::getCurrentPage() == 'events/calendar') {
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/core/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/core/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/daygrid/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/daygrid/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/bootstrap/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/bootstrap/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/timegrid/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/timegrid/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/list/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/list/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/core/locales/de.js'));
    rex_view::addJsFile($this->getAssetsUrl('backend_fullcalendar.js'));
}

if (rex::isBackend() && rex_be_controller::getCurrentPage() == 'events/date') {
    rex_view::addJsFile($this->getAssetsUrl('backend_date.js'));
}

if (rex_addon::get('yform')->isAvailable() && !rex::isSafeMode()) {

    rex_yform_manager_dataset::setModelClass(
        'rex_event_date',
        Date::class
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_event_location',
        Location::class
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_event_category',
        Category::class
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_event_date_offer',
        Offer::class
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_event_date_registration',
        Registration::class
    );

    rex_yform_manager_dataset::setModelClass(
        'rex_event_date_registration_person',
        RegistrationPerson::class
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_event_category_request',
        CategoryRequest::class,
    );
    
	rex_yform_manager_dataset::setModelClass(
		'rex_event_date_lang',
		DateLang::class,
	);

}

if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType('rex_cronjob_events_ics_import');
}

if (rex_plugin::get('yform', 'rest')->isAvailable() && !rex::isSafeMode()) {
    /* YForm Rest API */
    $rex_event_date_route = new \rex_yform_rest_route(
        [
        'path' => '/v5.0/event/date/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => Date::class,
        'query' => Date::query(),
        'get' => [
            'fields' => [
                'rex_event_date' => [
                    'id',
                    'name',
                    'description',
                    'location',
                    'image',
                    'startDate',
                    'doorTime',
                    'endDate',
                    'eventStatus',
                    'url'
                 ],
                 'rex_event_category' => [
                    'id',
                    'name',
                    'image'
                 ],
                 'rex_event_location' => [
                    'id',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_date' => [
                    'name',
                    'description',
                    'location',
                    'image',
                    'startDate',
                    'doorTime',
                    'endDate',
                    'eventStatus',
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_date' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_date_route);


    /* YForm Rest API */
    $rex_event_category_route = new \rex_yform_rest_route(
        [
        'path' => '/v5.0/event/category/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => Category::class,
        'query' => Category::query(),
        'get' => [
            'fields' => [
                 'rex_event_category' => [
                    'id',
                    'name',
                    'image'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_category' => [
                    'name',
                    'image'
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_category' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_category_route);

    /* YForm Rest API */
    $rex_event_location_route = new \rex_yform_rest_route(
        [
        'path' => '/v5.0/event/location/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => Location::class,
        'query' => Location::query(),
        'get' => [
            'fields' => [
                 'rex_event_location' => [
                    'id',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_location' => [
                    'name',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_location' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_location_route);
}

rex_extension::register('YFORM_DATA_LIST', function ($ep) {
    if ($ep->getParam('table')->getTableName()=="rex_event_date") {
        $list = $ep->getSubject();

        $list->setColumnFormat(
            'name',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_date')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_date';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                return '<a href="'.rex_url::backendPage('events/date', $params) .'">'. $a['value'].'</a>';
            }
        );
        $list->setColumnFormat(
            'category_id',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_category')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_category';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                $return = [];

                $category_ids = array_filter(explode(",", $a['value']));

                foreach ($category_ids as $category_id) {
                    $event = Category::get($category_id);
                    if ($event) {
                        $return[] = '<a href="'.rex_url::backendPage('events/category', $params) .'">'. $event->getName().'</a>';
                    }
                }
                return implode("<br>", $return);
            }
        );
        $list->setColumnFormat(
            'location',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_location')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_location';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';

                $location_ids = array_filter(explode(",", $a['value']));

                $return = [];
                
                foreach ($location_ids as $location_id) {
                    $location = Location::get($location_id);
                    if ($location) {
                        $return[] = '<a href="'.rex_url::backendPage('events/location', $params) .'">'. $location->getValue('name').'</a>';
                    }
                }
                return implode("<br>", $return);
            }
        );
    }
});

if (rex::isBackend() && \rex_addon::get('events') && \rex_addon::get('events')->isAvailable() && !rex::isSafeMode()) {
    $addon = rex_addon::get('events');
    $pages = $addon->getProperty('pages');

    if (rex::isBackend() && !empty($_REQUEST)) {
        $_csrf_key = rex_yform_manager_table::get('rex_event_date')->getCSRFKey();
        
        $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

        $params = [];
        $params['table_name'] = 'rex_event_date'; // Tabellenname anpassen
        $params['rex_yform_manager_popup'] = '0';
        $params['_csrf_token'] = $token['_csrf_token'];
        $params['func'] = 'add';

        $href = rex_url::backendPage('events/date', $params);

        $pages['events']['title'] .= ' <a class="label label-primary tex-primary" style="position: absolute; right: 18px; top: 10px; padding: 0.2em 0.6em 0.3em; border-radius: 3px; color: white; display: inline; width: auto;" href="' . $href . '">+</a>';
        $addon->setProperty('pages', $pages);
    }
}



\rex_api_function::register('ics_file', Api\IcsFile::class);
\rex_api_function::register('fullcalendar', Api\Fullcalendar::class);
