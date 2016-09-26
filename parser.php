<?
  error_reporting(1);

  include_once("classes/Team.php");
  include_once("classes/Player.php");
  include_once("classes/Message.php");
  include_once("classes/Match.php");
  include_once("classes/Parser.php");

 
  $result_dir = "source/matches/";
  $dir = scandir("source/matches/");
   
  foreach ($dir as $key => $file) {
    
    if(file_exists($result_dir.$file) && $file!="." && $file!=".."){
      
       $parser = new Parser($result_dir.$file);
       $parser->Process();
    }
  }
  
  
 
  
?>