<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

define('REQ_OK', 0);

define('ERROR_USERNAME_TAKEN', 1);
define('ERROR_MOBILE_PHONE_NUMBER_TAKEN', 2);
define('ERROR_SMS_WRONG', 3);
define('ERROR_MISS_PARAMETERS', 4);
define('ERROR_AT_LEAST_ONE_UPDATE', 5);
define('ERROR_NOT_IN_SESSION', 6);
define('ERROR_USER_NOT_EXIST', 7);
define('ERROR_OBJECT_NOT_EXIST', 8);
define('ERROR_LOGIN_FAILED', 10);
define('ERROR_ONLY_LEARNER_CAN_ORDER', 11);
define('ERROR_UNKNOWN_TYPE', 12);
define('ERROR_NOT_ALLOW_DO_IT', 13);
define('ERROR_PINGPP_CHARGE', 14);
define('ERROR_PARAMETER_ILLEGAL', 15);
define('ERROR_AMOUNT_UNIT', 16);
define('ERROR_INVALID_IP', 17);
define('ERROR_ALREADY_DO_IT', 18);
define('ERROR_PASSWORD_FORMAT', 19);
define('ERROR_EXCEED_MAX_ORDERS', 20);
define('ERROR_CODE_LINES_INVALID', 21);

define('TYPE_REVIEWER', 1);
define('TYPE_LEARNER', 0);

define('KEY_COOKIE_TOKEN', 'crtoken');
define('KEY_SESSION_HEADER', 'X-CR-Session');

define('KEY_SKIP', 'skip');
define('KEY_LIMIT', 'limit');
define('KEY_ID', 'id');

// users table
define('KEY_MOBILE_PHONE_NUMBER', 'mobilePhoneNumber');
define('KEY_AVATAR_URL', 'avatarUrl');
define('KEY_SESSION_TOKEN', 'sessionToken');
define('KEY_SESSION_TOKEN_CREATED', 'sessionTokenCreated');
define('KEY_PASSWORD', 'password');
define('KEY_USERNAME', 'username');
define('KEY_TYPE', 'type');
define('KEY_VALID', 'valid');

define('KEY_SMS_CODE', 'smsCode');

define('KEY_INTRODUCTION', 'introduction');
define('KEY_EXPERIENCE', 'experience');
define('KEY_COMPANY', 'company');
define('KEY_JOB_TITLE', 'jobTitle');
define('KEY_GITHUB_USERNAME', 'gitHubUsername');
define('KEY_MAX_ORDERS', 'maxOrders');

// orders table
define('KEY_ORDER_ID', 'orderId');
define('KEY_GITHUB_URL', 'gitHubUrl');
define('KEY_LEARNER_ID', 'learnerId');
define('KEY_REVIEWER_ID', 'reviewerId');
define('KEY_CODE_LINES', 'codeLines');
define('KEY_REMARK', 'remark');
define('KEY_STATUS', 'status');
define('KEY_CREATED', 'created');
define('KEY_UPDATED', 'updated');

// reviews table
define('KEY_REVIEW_ID', 'reviewId');
define('KEY_TITLE', 'title');
define('KEY_CONTENT', 'content');

// rewards rable
define('TABLE_REWARDS', 'rewards');
define('KEY_AMOUNT', 'amount');
define('KEY_REWARD_ID', 'rewardId');
define('KEY_ORDER_NO', 'orderNo');
define('KEY_PAID', 'paid');
define('KEY_CREATOR', 'creator');
define('KEY_CREATOR_IP', 'creatorIP');

define('ORDER_STATUS_NOT_PAID', 0);
define('ORDER_STATUS_FINISHED', 1);
define('ORDER_STATUS_REJECTED', 2);
define('ORDER_STATUS_PAID', 3);

define('TABLE_ORDERS', 'orders');
define('TABLE_REVIEWS', 'reviews');
define('TABLE_LEARNERS', 'learners');
define('TABLE_REVIEWERS', 'reviewers');
define('TABLE_TAGS', 'tags');
define('TABLE_USERS_TAGS', 'users_tags');
define('TABLE_ORDERS_TAGS', 'orders_tags');

define('CHARGE_UNPAID', 0);
define('CHARGE_PAID', 1);

// charges table
define('TABLE_CHARGES', 'charges');
define('KEY_CHARGE_ID', 'chargeId');

define('LEAST_FIRST_REWARD', 5000);
define('LEAST_COMMON_REWARD', 1000);

define('KEY_OP', 'op');
define('KEY_OP_ADD', 'add');
define('KEY_OP_REMOVE', 'remove');

// tags table
define('KEY_TAG_NAME', 'tagName');
define('KEY_TAG_ID', 'tagId');
define('KEY_COLOR', 'color');

define('KEY_USER_ID', 'userId');