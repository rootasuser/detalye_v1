<?php 

 if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ==> Rename thi title if you want only
$web_title = "Detalye Barong";
  
  if (isset($_SESSION['web_title'])) {
    $web_title = $_SESSION['web_title'];
  } else {
    $_SESSION['web_title'] = $web_title;
  }

  
?>