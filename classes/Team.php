<?
  /**
    * Team
    */
    
    class Team
    {
      public $title;  
      public $country; 
      public $players; /// Player array()
      public $coach;
      public $startPlayerNumbers;
      public $goals;
      public $win;

     /**
     * Getting Player name by his number
     *
     * @param      <string>  $number  Gettinf
     *
     * @return     <string>  player name
     */
      public function GetNameByNumber($number){  
          return   $this->players[$number]->name;  
      }

      /**
     * Getting Player Object link by his number
     *
     * @param      <string>  $number  Gettinf
     *
     * @return     <&Player>  player name
     */
      public function &GetPlayerByNumber($number){
        return $this->players[$number];
      }

      /**
     * Setting endTime Attribute to all the players
     *
     * @param      <string>  $number  Gettinf
     */
      public function setEndTime($endTime){
        foreach ($this->players as $key => $player) {
           
           /*if player was always on filed*/
           if(!$player->reserve && empty($player->replacedBy) ){
              $this->players[$key]->endTime = $endTime;
           }

           /*needs to be refactored -- when double replaced varriant is triggered*/
           if($player->reserve && !empty($player->replacedBy)){
              $this->players[$key]->endTime = $endTime;
           }

         } 

      }

      
      /**
       * { Team constructor }
       *
       * @param      <type>  $title               The title
       * @param      <type>  $country             The country
       * @param      <type>  $team                The team
       * @param      <type>  $coach               The coach
       * @param      <type>  $startPlayerNumbers  The start player numbers
       */
      function __construct($title, $country, $team,$coach,
        $startPlayerNumbers)
      {
          $this->title = $title;
          $this->country =   $country;

          $this->coach = $coach;
          $this->startPlayerNumbers = $startPlayerNumbers;



          foreach ($team as $key => $player) {
            
            $newPlayer = new Player (
                $player["name"],
                $player["number"]                
            );
            
            $newPlayer->reserve = $this->isReserved($newPlayer->number); 

            if($newPlayer->reserve){
               $newPlayer->startTime = 0;
               $newPlayer->endTime = 0;
            }
            else {
              $newPlayer->startTime = 0;
            }
            
            $this->players[$player["number"]] = $newPlayer;
          }

         /* usort($this->players, function($a,$b){
            if ($newPlayer->reserve > $newPlayer->reserve)  return $a;
          });*/
          
          
          
      }

       
      /**
       * Determines if player is reserved.
       *
       * @param      <string>   $number  The number
       *
       * @return     boolean  True if reserved, False otherwise.
       */
      public function isReserved($number){    
  
        if(in_array( intval($number), array_values($this->startPlayerNumbers)))
          return false;
        else return true;
      }


    }  
?>