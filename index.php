<?php 

// index.php

session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Please enter your details</title>

    <!-- Bootstrap core CSS -->
    <link href="public/stylesheets/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="public/stylesheets/signin.css" rel="stylesheet">

    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="public/javascripts/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
      
      <form class="form-signin" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <?php 
        
        require_once 'Facebook/autoload.php';
        
        $appId = '831916303594448';
        $appSecret = 'bf20ab163ef2fa74532fbda9c95288d5';
        
        $fb = new Facebook\Facebook([
            'app_id' => $appId,
            'app_secret' => $appSecret,
        ]);
        
        $helper = $fb->getCanvasHelper();
        //print_r($helper);
        
        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          //When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
        
        if(!isset($accessToken) && !$_POST['firstName']) {
          ?>
          <script>window.top.location = 'https://www.facebook.com/dialog/oauth?client_id=831916303594448&redirect_uri=https://apps.facebook.com/opiumworks';</script>
          <?php 
        }
        
        $status = null;
        
        require_once 'database.php';
        
        if($_POST['firstName']) {
          $accessToken = $_POST['accessToken'];
        }
        $fb->setDefaultAccessToken($accessToken);
        
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
        
        // Logged in.
        
        // Each user is only allowed 1 entry/ day
        $stmt = $conn->prepare("SELECT timestamp FROM opium_user WHERE facebook_user_id = :facebook_user_id ORDER BY id DESC LIMIT 1");
        $facebook_user_id = $userNode->getId();
        $stmt->bindParam(':facebook_user_id', $facebook_user_id);
        $stmt->execute();
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $timestamp = '2015-01-01 00:00:00';
        if($row = $stmt->fetch()) {
          $timestamp = $row['timestamp'];
        }
         
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $date1 = new DateTime($timestamp);
        $date2 = new DateTime($currentTimestamp);
        $diff = $date2->diff($date1);
        $hours = $diff->h;
        $diffHours = $hours + ($diff->days*24);
        
        if($diffHours <= 24) {
          ?>
          <p class="text-danger"><strong>Each user is only allowed 1 entry / day</strong></p>
          <?php
        } else if(!$_POST['firstName']) {
          ?>
          <h2 class="form-signin-heading">Please enter your details</h2>
          <label for="firstName" class="sr-only">First name</label>
          <input type="text" id="firstName" name="firstName" class="form-control" placeholder="First name" required autofocus>
        
          <label for="lastName" class="sr-only">Last name</label>
          <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Last name" required>
        
          <label for="inputEmail" class="sr-only">Email</label>
          <input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="Email" required>
        
          <label for="telephone" class="sr-only">Telephone (optional)</label>
          <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Telephone (optional)">
        
          <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
          <?php 
        }
        
        // Form posted
        if(isset($_POST) && $_POST['firstName']) {
          
          //echo 'Logged in as ' . $userNode->getId();
          
          try {
            // prepare sql and bind parameters
            $stmt = $conn->prepare("INSERT INTO opium_user (facebook_user_id, first_name, last_name, email, telephone) VALUES (:facebook_user_id, :first_name, :last_name, :email, :telephone)");
            $stmt->bindParam(':facebook_user_id', $facebook_user_id);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            
            // insert a row
            $first_name = $_POST['firstName'];
            $last_name = $_POST['lastName'];
            $email = $_POST['inputEmail'];
            $telephone = $_POST['telephone'];
            
            if($stmt->execute()) {
              $status = 'inserted';
            }
          
            //echo "New records created successfully";
          }
          catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
          }
          $conn = null;
          
          if($status == 'inserted') {
            ?>
            <p class="text-danger"><strong>Thank you, your details have been saved.</strong></p>
            <?php
          }
        }
        ?>
        <input type="hidden" name="accessToken" value="<?php echo $accessToken; ?>">
      </form>

    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="public/javascripts/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

