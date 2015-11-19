<?php
return [
	  'f_code' => 0,
	  'f_type_id' => '16',
	  'f_phase_id' => '',
	  'f_module_name' => 'mdl_usr_tg',
	  'unique' => 
	  array (
		0 => 'fc_usr_usn',
		1 => 'fc_usr_usn',
		2 => 'fc_usr_usn',
		3 => 'fc_usr_usn',
		4 => 'fc_usr_usn',
	  ),
	  'attachfields' => 
	  array (
	  ),
	  'mandatory' => 
	  array (
	  ),
	  'error' => 
	  array (
	  ),
	  'attachments' => 
	  array (
		0 => 'fc_usr_avatar',
	  ),
	  'fc_usr_firstname' => 'nome',                 // fill this field properly
	  'fc_usr_lastname' => 'cognome',       	// fill this field properly
	  'f_title' => 'nome cognome',  		// fill this field properly
	  'fc_usr_gender' => '0',			// fill this field properly
	  'fc_usr_address' => '',			// fill this field properly
	  'fc_usr_phone' => '',				// fill this field properly
	  'fc_usr_mail' => 'cognome@mainsim.com', 	// fill this field properly
	  'fc_usr_usn' => 'nome.cognome',		// fill this field properly
	  'fc_usr_password' => 'password',		// fill this field properly
	  'fc_usr_repeat_pwd' => 'password',		// fill this field properly
	  'fc_usr_language' => '2',			// 2: Italian
          'fc_usr_language_str' => 'Italian',           // 2: Italian
          'fc_usr_level' => 64,                         // 64: Supervisor PRO
	  'fc_usr_level_text' => 'Supervisor PRO',      // 64: Supervisor PRO
	  'fc_usr_pwd_registration' => time(),		// calculated timestamp
	  'fc_usr_usn_expiration' => '',
	  'fc_usr_avatar' => '',
	  't_wares_parent_16' => 
	  array (
		'f_code' => 
		array (
		),
		'f_code_main' => 0,
		'f_type' => '16',
		'Ttable' => 't_wares_parent',
		'f_module_name' => 'mdl_usr_parent_tg',
	  ),
// Removed to prevent errors when a non-admin generate a user
/* 	  't_systems_4' => 
	  array (
		'f_code' => 
		array (
		  0 => '10524',
		),
		'f_code_main' => 0,
		'f_type' => '4',
		'Ttable' => 't_systems',
		'f_module_name' => 'mdl_usr_lvl_tg',
	  ),*/
	  't_selectors_1,2,3,4,5,6,7,8,9,10' => 
	  array (
		'f_code' => 
		array (
		),
		'f_code_main' => 0,
		'f_type' => '1,2,3,4,5,6,7,8,9,10',
		'Ttable' => 't_selectors',
		'f_module_name' => 'mdl_usr_slc_tg',
	  ),
];
?>