<?
 /**
  * Player
  */
  class Player  
  {
    
    public $name;
    public $number;
    public $cards;
    public $reserve;
    public $startTime;
    public $endTime;
    public $replacedBy;


    /**
     * { Dump object }
     *
     * @param      <type>  $obj    The object
     */
    public function dump($obj){
      echo "<pre>";
        print_r($obj);
      echo "</pre>";
    }
    
    /**
     * { Player constructor }
     *
     * @param      <string>  $name     The name
     * @param      <string>  $number   The number
     * @param      <boolean>  $reserve  The reserve
     * @param      <string>  $cards    The cards
     */
    function __construct( $name,$number,$reserve,$cards)
    {
      $this->name = $name;
      $this->number = $number;

      /*if cards were before match*/
      //$this->cards =     $cards;

    }
  }

?>