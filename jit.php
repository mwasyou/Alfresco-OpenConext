<?php
/*
 *  Alfresco - Shibboleth JIT Script
 *  Copyright (C) 2013 Frank Niesten f.niesten@fontys.nl
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 	TO DO:
 	# Check if user exist then create account or upgrade
	# Check if Admin user is member of admin group. If not ad admin to group. Extra mutiple admins
	# Create Sepperate Config file for Jit Script.
	
	Ideas:
	# Create sites automaticly. 
 */ 

$alf_admin = "****";
$password = "****";

//URL Alfresco API
$api_url ="http://api/alfresco/service/api/";

//URL from Alfresco Share for forwarding after JIT is executed.
$redirect_url="https://host.nl/share";

//Set Administrator Email
$alf_admin_user ="admin@example.com";

//Set User Quata in Alfresco in bytes. -1 is unlimited.
//Set quota for normal user.
$quota_user = "262144000"; // 262144000 = 250 Mb

//Set quota for a specific email domain
$set_domain = "example.com"; //domain
$quota_domain_user = "1048567000"; // 1048567000 = 1000 Mb

//----------------------------------------------------------------------------------

require_once "/var/www/html/php-oauth-client/lib/_autoload.php";


$new_teams;

//Extract attributes from shibboleth
$persistent_id = $_SERVER['REMOTE_USER'];
$firstname = $_SERVER['Shib-givenName'];
$lastname = $_SERVER['Shib-surName'];
$email = $_SERVER['Shib-email'];
$shib_user = $_SERVER['Shib-uid'];

//Get email domain from user
$split_mail = explode("@", $email);
	if ($set_domain == $split_mail[1]) { 
		$quota = $quota_domain_user;
	}
	else { 
		$quota = $quota_user;
	}
	
$alf_user = array(
	'userName' => $persistent_id,
	'firstName' => $firstname,
	'lastName' => $lastname,
	'email' => $email,
	'quota' => $quota
);	
//-----ADD / UPDATE SURFconext user-----($user, $password, $url, $method, $params) {

//Add new user to Alfresco
rest_call($alf_admin, $password, $api_url.'people', 'POST', $alf_user);

//Update existing Alfresco User
rest_call($alf_admin, $password, $api_url.'people/'.$persistent_id, 'PUT', $alf_user);

//Add Admin Role
if ($email == $alf_admin_user)
{
	$all = rest_call($alf_admin, $password, $api_url.'groups/ALFRESCO_ADMINISTRATORS/children/'.$persistent_id, 'POST', '');
}

//-----ADD SURFconext groups to alfresco------
get_SURFteams($shib_user);

//All groups
//$alls = rest_call($alf_admin, $password, $api_url.'rootgroups', 'GET', $alf_user);
//print_r($alls);


//-----Remove membership of groups that user is not a member of-----
$abandoned_groups = rest_call($alf_admin, $password, $api_url.'people/'.$persistent_id.'?groups=true', 'GET', '');
$abandoned_groups = object2Array($abandoned_groups);

$remove_groups=array();

foreach($abandoned_groups['groups'] as $k => $v) {
	$remove_groups[$v['itemName']] = $v['displayName'];
	
	//Extract SURFteams groups from Alfresco system groups.
	foreach (array_keys($remove_groups) as $key) {
    if (!preg_match('/^GROUP_urn:collab:group:surfteams.nl/', $key)) {
        unset($remove_groups[$key]);
    }
}	
};
//Compare SURFteams groups with Alfresco Groups.
$result = array_diff($remove_groups, $new_teams);
	
	//Split mutiple teams.
	foreach($result as $k => $v) {
	//Remove GROUP_ from shortname.
	$k = substr($k, 6);
		rest_call($alf_admin, $password, $api_url.'groups/'.$k.'/children/'.$persistent_id, 'DELETE', '');
	}
	
//-----REDIRECT after running the JIT script to Alfresco share-----

//Redirect to Alfresco Share.
header( 'Location:'.$redirect_url ) ;

//-----FUNCTIONS-----

//Get SURFteams from API and post them to Alfresco.
function get_SURFteams($shib_user){
$groups = array();

try { 
    $client = new \OAuth\Client\Api("SURFconext");
    $client->setUserId($shib_user);
    $client->setScope(array("read"));
    $client->setReturnUri("https://host.nl/");
    $response = $client->makeRequest("https://api.surfconext.nl/v1/social/rest/groups/@me");
    
	header("Content-Type: application/json");
	
	if (200 !== $response->getStatusCode()) {
            $message = "[voot-roles] ERROR: unexpected status code from VOOT provider (" . $response->getStatusCode() . "): " . $response->getContent();
            error_log($message);
            die($message);
        }

        $content = $response->getContent();
        if (empty($content)) {
            $message = "[voot-roles] ERROR: empty response from VOOT provider";
            error_log($message);
            die($message);
        }

        $data = json_decode($content, TRUE);
        if (NULL === $data || !is_array($data)) {
            $message = "[voot-roles] ERROR: invalid/no JSON response from VOOT provider: " . $content;
            error_log($message);
            die($message);
        }

        if (!array_key_exists("entry", $data)) {
            $message = "[voot-roles] ERROR: invalid JSON response from VOOT provider, missing 'entry': " . $content;
            error_log($message);
            die($message);
        }
       $groups = $data['entry'];
    
} 
	catch (\OAuth\Client\ApiException $e) {
    echo $e->getMessage();
}

$groups = array();
        if(array_key_exists("entry", $data)) {
            foreach($data['entry'] as $k => $v) {
                $groups[$v['id']] = $v['title'];
				
				$groupName = $v['id'];
				
				$alf_group = array(
				'shortName' => $v['id'],
				'fullName' => $v['title'],
				'displayName' => $v['title'],
				);

				global $alf_admin, $password, $api_url, $persistent_id;

				//Create SURFteam in Alfresco.
				rest_call($alf_admin, $password, $api_url.'rootgroups/'.$groupName, 'POST', $alf_group);
				//Add user to just created group.
				rest_call($alf_admin, $password, $api_url.'groups/'.$groupName.'/children/'.$persistent_id, 'POST', '');	
							}
						}
						
					global $new_teams;
					$new_teams=$groups;	
				}

//REST call function
function rest_call($alf_admin, $password, $url, $method, $params) {
  $cparams = array(
    'http' => array(
      'method' => $method,
      'ignore_errors' => true,
      'header' => sprintf("Authorization: Basic %s\r\n", base64_encode($alf_admin.':'.$password)).
		  "Content-type: application/json;charset=UTF-8\r\n",
      'content' => json_encode($params)
    )
  );
  $context = stream_context_create($cparams);
  $fp = @fopen($url, 'rb', false, $context);
  if (@fopen($url, 'rb', false, $context)) {
    $res = stream_get_contents($fp);
  } else {
    $res = false;
  }
  return json_decode($res);
}

//Covert Object to Array.
function object2Array($d)
	{
		if (is_object($d))
		{
			$d = get_object_vars($d);
		}
 
		if (is_array($d))
		{
			return array_map(__FUNCTION__, $d);
		}
		else
		{
			return $d;
		}
	}
?>
