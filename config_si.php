<?php

$host = 'localhost';
$flav = '<FLAV>';
$name = '<NAME>';
$demoData = <DEMO>;
$key = '<KEY>';

$sugar_config_si = array (
  'setup_db_host_name' => 'localhost',
  'setup_license_key' => $key,
  'setup_db_admin_user_name' => 'root',
  'setup_db_pop_demo_data' => '0',
  'setup_system_name' => 'SugarCRM',
  'setup_db_database_name' => "sugar_$name"."_$flav",
  'export_delimiter' => ',',
  'default_language' => 'en_us',
  'default_currency_name' => 'US Dollar',
  'setup_site_url' => "http://$name.localhost/$name/$flav/sugarcrm/",
  'default_currency_significant_digits' => '2',
  'setup_db_create_sugarsales_user' => 'false',
  'setup_site_admin_password' => 'asdf',
  'default_currency_iso4217' => 'USD',
  'setup_num_lic_oc' => '10',
  'setup_license_key_expire_date' => '2016-02-26',
  'web_user' => 'apache',
  'setup_fts_type' => 'Elastic',
  'dbUSRData' => 'same',
  'setup_db_create_database' => 'true',
  'default_number_grouping_seperator' => ',',
  'default_decimal_seperator' => '.',
  'default_date_format' => 'Y-m-d',
  'default_currency_symbol' => '$',
  'setup_db_admin_password' => 'asdf',
  'setup_fts_port' => '9200',
  'setup_fts_host' => 'localhost',
  'demoData' => ($demoData ? 'multi' : 'no'),
  'setup_license_key_users' => '100',
  'setup_site_admin_user_name' => 'admin',
  'setup_db_type' => 'mysql',
  'default_export_charset' => 'ISO-8859-1',
  'default_locale_name_format' => 's f l',
  'default_time_format' => 'H:i',
  'setup_db_drop_tables' => 'true',
  //'developerMode' => 'true',
  'developerMode' => 'false',
);
