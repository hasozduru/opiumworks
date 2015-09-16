<?php

// entries.php

require '../Facebook/autoload.php';

$appId = '831916303594448';
$appSecret = 'bf20ab163ef2fa74532fbda9c95288d5';

$fb = new Facebook\Facebook([
  'app_id' => $appId,
  'app_secret' => $appSecret,
]);

$helper = $fb->getCanvasHelper();

try {
  $accessToken = $helper->getAccessToken();
  $fb->setDefaultAccessToken($accessToken);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  //When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$appToken = '831916303594448|u5KWwnWjiBF2OOVFJoTlFFZ6G7Q';
$responseBody = $fb->get('/' . $appId . '/roles', $appToken);
$body = json_decode($responseBody->getBody());
$adminUsers = $body->data;

try {
  $response = $fb->get('/me');
  $userNode = $response->getGraphUser();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}
//print_r($userNode);


$loggedInUserId = $userNode->getId();

$isLoggedInUserAdmin = false;
foreach($adminUsers as $obj) {
  if($obj->user == $loggedInUserId && $obj->role == 'administrators') {
    $isLoggedInUserAdmin = true;
  }
}

if($isLoggedInUserAdmin) {
  require_once '../database.php';
  
  $query = "SELECT * FROM opium_user";
  
  if($_GET['from'] && $_GET['to']) {
    $fromYmd = substr($_GET['from'], 4, 4) . '-' . substr($_GET['from'], 2, 2) . '-' . substr($_GET['from'], 0, 2);
    $toYmd = substr($_GET['to'], 4, 4) . '-' . substr($_GET['to'], 2, 2) . '-' . substr($_GET['to'], 0, 2);
    
    $query .= " WHERE timestamp BETWEEN '" . $fromYmd . "' AND '" . $toYmd . "'";
  }
  
  //echo $query;
  
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
  
  $users = array();
  while($row = $stmt->fetch()) {
    $users[] = $row;
  }
  
  // Set HTTP Response Content Type
  header('Content-Type: application/json; charset=utf-8');
  
  // Format data into a JSON response
  $json_response = json_encode($users);
  
  // Deliver formatted data
  echo $json_response;
} else {
  echo 'Sorry, only App administrators can see the entries.';
}

?>
