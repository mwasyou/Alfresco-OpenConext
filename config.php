<?php
$config = array ();

//---Alfresco API settings---.

//Alfresco API URl.
$config['api_url'] = 'http://api/alfresco/service/api/';

//Alfresco API Username.
$config['api_username'] = 'username';

//Alfresco API Password.
$config['api_password'] = 'password';

//Redirect url, for forwarding after JIT is executed.
$config['redirect_url'] = 'https://host.nl/share';



//---Additional Options and settings---

//Set specific users Alfresco administartor(s).
$config['admin_user'] ="admin@example.com";

//SURFconext groups integration.
$config['groups_set'] = TRUE;
$config['groups_set'] = TRUE;

	//Automatic sites 
	$config['sites_set'] = TRUE;

//To use automatic site creation disable sitecreation for normal user in alfresco:


//Set Quota for alfresco users.
$config['quota_set'] = TRUE;

	//Set quota for normal user in Mb.
	$config['quota_normal_user'] = "250"; 

	//Set quota for a specific email domain
	$config['quota_domain'] = "example.com";
	$config['quoato_domain_user'] = "20000";

//To use user quoate in Alfresco edit:





?>
