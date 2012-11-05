<?php
require_once( 'amfphp/core/amf/app/Gateway.php');
require_once( AMFPHP_BASE . 'amf/io/AMFSerializer.php');
require_once( AMFPHP_BASE . 'amf/io/AMFDeserializer.php');
  include_once('ustreamrip.class.php');
  $rip = new Ustreamrip();
  $rip->Init();
//  $rip->setChannel('dj-kentai-jcore-hardcore-mix');
  $rip->setChannel('bear-lake-golden-doodles-puppy-cam-2');
  $data = $rip->getRTMPCommand();
  var_dump($data);
  
?>
