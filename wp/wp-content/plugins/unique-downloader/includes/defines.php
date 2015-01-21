<?php
define('WPUS_PAGES_PROJECTS_PAGE', 'download_code_projects');
define('WPUS_PAGES_CODE_PAGE', 'download_code_history');
define('WPUS_PAGES_SALES_PAGE', 'download_code_sales');
define('WPUS_PAGES_USERS_PAGE', 'download_code_users');
define('WPUS_PAGES_LOG_PAGE', 'download_code_attempts');
define('WPUS_PAGES_CONFIGURE_PAGE', 'download_code_configure');

define('WPUS_CODE_BAD_FORMAT', -1);
define('WPUS_CODE_INVALID', -2);
define('WPUS_CODE_NO_USES_REMAINING', -3);
define('WPUS_CODE_DISABLED', -4);
define('WPUS_CODE_DATABASE_ERROR', -5);
define('WPUS_CODE_PROJECT_DISABLED', -6);

define('WPUS_KEY_REGEX', '/^[a-z][0-9]{2}[a-z]?$/');

define('WPUS_CODE_REGEX', '/^[0-9a-z]{5}\-[0-9a-z]{5}\-[0-9a-z]{5}\-[0-9a-z]{5}\-[0-9a-z]{5}$/');
define('WPUS_CODE_REGEX2', '/^[a-z][0-9]{2}[a-z][0-9]{8}$/');
define('WPUS_CODE_FORMAT_HUMAN', 'Spring 2013 and earlier: xxxxx-xxxxx-xxxxx-xxxxx-xxxxx where x is a number or lowercase letter.');
define('WPUS_CODE_FORMAT_HUMAN2', 'Spring 2014 and later: xddxdddddddd where x is a lowercase letter and d is a number.');
define('WPUS_USER_CREATE_SUCCESS', 1);
define('WPUS_USER_CREATE_ERROR', -1);
define('WPUS_USER_BAD_FORMAT', -2);
define('WPUS_USER_ALREADY_EXISTS', -3);
define('WPUS_USER_BAD_LOGIN', -4);
define('WPUS_USER_ACCOUNT_DISABLED', -5);

define('WPUS_LOG_USER', "USER");
define('WPUS_LOG_CDN_DOWNLOAD', "CDNREQUEST");
define('WPUS_LOG_DOWNLOAD', "DOWNLOAD");
define('WPUS_LOG_BAD_LOGIN', "BADLOGIN");
define('WPUS_LOG_BAD_CODE', "BADCODE");
define('WPUS_LOG_NEW_USER', "NEWUSER");
define('WPUS_LOG_USER_ADD_CODE', "USERADDCODE");
define('WPUS_LOG_ACTIVATE_CODE', "ACTIVATECODE");
define('WPUS_LOG_PWRESET_REQUEST', "PWRESETREQUEST");
define('WPUS_LOG_PWRESET_FAIL', "PWRESETFAIL");
define('WPUS_LOG_PWRESET_SUCCESS', "PWRESETSUCCESS");
