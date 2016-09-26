<?
   class Message 
   {
      public $type;
      public $time;
      public $description;
      public $details;

     /**
      * { dump class object }
      *
      * @param      this  $obj    The object
      */
       public function dump($obj){
      echo "<pre>";
        print_r($obj);
      echo "</pre>";
    }
      /**
       * { Message constructor }
       *
       * @param      <string>  $type         The type
       * @param      <string>  $time         The time
       * @param      <string>  $description  The description
       * @param      <Array[string]>  $details      The details
       */
      function __construct($type,$time,$description,$details)
      {
        $this->type = $type;
        $this->time =     $time;
        $this->description =      $description;
        
        foreach ($details as $key => $detail) {
          $this->details[$key] =   $detail;
        }
      }
   } 
?>