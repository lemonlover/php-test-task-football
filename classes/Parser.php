<?
class Parser{ 
    
    public $json; 
    public $filename; 
    public $raw_data;
    public $endTime;


    public function __construct($name){
      
      try {
        /* check file exists*/
        if(file_exists($name))
          $this->filename = $name;  
        else throw new Exception("Filename is wrong or file doesn't exist, bro", 1);
        
      } 
      catch (Exception $e){
        $this->dump( $e->getMessage());        
        die();
      }

      //echo "<br>File founded, bro";
      
    }   



    /**
     * { Parsing process function }
     */
    public function Process(){  

      $this->DecodeFile();   
      $messages =   $this->ParseMessages();   
      $match = new Match($messages,$this->endTime);
      $match->play();   
      $view = $match->BuildView();   
      $source_dir =    explode("/",$this->filename);   
      $source_file =    explode(".",$source_dir[2]);   
      $new_file_name = $source_file[0].".html";
      $result_file = fopen("result/".$new_file_name, "w");
      fwrite($result_file,$view);     fclose($result_file);   
      echo '< atarget="_blank"  href="result/'.$new_file_name.'">'.$new_file_name.'</a><br>'; 
    }

    public function dump($obj){
      echo "<pre>";
        print_r($obj);
      echo "</pre>";
    }

    /**
     * { Decoding json files to a raw_json array }
     */
    public function DecodeFile(){
      if($raw_data = file_get_contents($this->filename)){
          $this->raw_data =  json_decode($raw_data, true);
        //echo "<br>File is decoded, dude!";
      }
      else echo "File ".$this->filename." is missing, bro";
    }


    /**
     * { Parsing raw json document to a list of Message objects }
     *
     * @return     <Array[Message]>  List of Messages
     */
    public function ParseMessages(){
      
      $this->endTime = 0;
      /*Parse raw_data to messages array*/
      foreach ($this->raw_data as $key => $message) {        
        $messages[$message["type"]][] = 
          new Message(
            $message["type"],
            $message["time"],
            $message["description"],
            $message["details"]
          ); 

        if($message["time"] >  $this->endTime  )
           $this->endTime =  $message["time"];
      } 

      return $messages;
    }
 
    
   

  }
?>