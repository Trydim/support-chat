<?php

class VC {
  /** Const */
  const PUBLIC_PAGE     = 'PUBLIC_PAGE',
        USE_DATABASE    = 'USE_DATABASE',
        CHANGE_DATABASE = 'CHANGE_DATABASE';

  /** cmsParams */
  const PROJECT_TITLE = 'PROJECT_TITLE',
        DB_CONFIG     = 'dbConfig',
        ONLY_LOGIN    = 'onlyLogin',
        CSV_PATH      = 'csvPath',
        LEGEND_PATH   = 'legendPath',
        IMG_PATH      = 'imgPath',
        URI_IMG       = 'uriImg',
        URI_CSS       = 'uriCss',
        URI_JS        = 'uriJs';

  /**
   * Setting action
   */


  /**
   * Hooks
   */
  const HOOKS_PUBLIC_TEMPLATE   = 'publicTemplate',
        HOOKS_CALENDAR_TEMPLATE = 'calendarTemplate',
        HOOKS_CATALOG_TEMPLATE  = 'catalogTemplate',
        HOOKS_ORDER_TEMPLATE    = 'orderTemplate',
        HOOKS_CUSTOMERS_TEMPLATE = 'customersTemplate',
        HOOKS_FILE_MANAGER_TEMPLATE = 'fileManagerTemplate',
        HOOKS_SETTING_TEMPLATE      = 'settingTemplate',
        HOOKS_USERS_TEMPLATE        = 'usersTemplate',
        HOOKS_DEALERS_TEMPLATE      = 'dealersTemplate',
        HOOKS_DEALERS_BEFORE_CREATE = 'beforeCreateDealer',
        HOOKS_DEALERS_AFTER_CREATE  = 'afterCreateDealer',
        HOOKS_AUTH_LOGIN_BEFORE     = 'authLoginBefore';
}
