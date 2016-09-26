<?
  
  /**
   * Match
   */
  class Match {


    public $messages;
    public $teams; 
    public $endTime;
    public $events;

    public function __construct($messages,$endTime){
      $this->messages = $messages;
      $this->endTime = $endTime;
    }

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
     * { Main trigger -- turns on parsing process }
     */
    public function play(){


      $importnat_event_types =  
        ["replacePlayer", "yellowCard",  "dangerousMoment",  "goal"];


      /*Get Anounce message*/
      $annonce = array_shift($this->messages["startPeriod"]);

      /*Get match teams*/
      $this->teams = $this->GetTeams($annonce);

      /*Get important events*/
      $this->events = $this->getImpotantEvents($importnat_event_types);
  
      
      foreach ($this->teams as $key => $team) {
        $team->setEndTime($this->endTime);
       
      }

       

    }
 
    /**
     * Gets the teams from announce message
     *
     * @param      <Message>  $annonce  The annonce of match with team lists inclded
     *
     * @return     <Array[Team]>  The teams.
     */
    public function GetTeams($annonce){
  
      foreach ($annonce->details as $key => $team) {
        /*looking for team key*/
        if ($key === "team1" || $key === "team2") {
              $new_team =  new Team(
                 $annonce->details[$key]["title"],
                 $annonce->details[$key]["country"],
                 $annonce->details[$key]["players"],
                 $annonce->details[$key]["coach"],
                 $annonce->details[$key]["startPlayerNumbers"]
                 
                
              );
            /*add this staff with title key*/            
            $teams[$team["title"]] = $new_team; 
          
        }
      }

      return $teams;
    
    }
 
    /**
     * Gets the impotant events according to a $types list
     *  
     *
     * @param      <Array[string,..]>  $types  The types
     *
     * @return     <Message>  The impotant events.
     */
    public function getImpotantEvents($types){
      
      /*look throw messages*/
      foreach ($this->messages as $key => $event_type) {
        /*if event_type is important -- accodrding $types*/
        if(in_array($key, array_values($types))){          
          /*get events*/
          foreach ($event_type as $key_ev => $event) {

              if($key == "yellowCard"){  
                /// give a card to a player 
                  $this->giveCard(
                    $event->time,
                    $event->details["team"],
                    $event->details["playerNumber"]
                  );
              }

              if($key == "replacePlayer"){
                  // replace player
  
                  $this->replacePlayer(
                    $event->time,
                    $event->details["team"],
                    $event->details["inPlayerNumber"],  
                    $event->details["outPlayerNumber"]  
                  );
              }

              if($key == "dangerousMoment"){
                // some actions (if you want)
              }

              if($key == "goal"){
                  //  goal
                  $this->goal(
                    $event->time,
                    $event->details["team"],
                    $event->details["playerNumber"],
                    $event->details["assistantNumber"]
                  );
              }

            $events[] =  $event;
          }          
        }  
      }

      /*finally sort this staff by time -- accending*/
      usort($events, function($a,$b){
        if ($a->time > $b->time)  return $a;
      });

      return $events;
    }


     
    /**
     * { Triggers Players values, when goal event is happend }
     *
     * @param      <type>  $time             The time
     * @param      <type>  $team             The team
     * @param      <type>  $goalerNumber     The goaler number
     * @param      <type>  $assistantNumber  The assistant number
     */
    public function goal($time,$team,$goalerNumber,$assistantNumber){
          
          /*Forming goal event*/
          $goal["time"] = $time;
          $goal["playerNumber"] = $goalerNumber;
          if($assistantNumber != null)
            $goal["assistantNumber"] = $assistantNumber;
          else $goal["assistantNumber"] = "-";

          /*Add goal event to a team*/
          $this->teams[$team]->goals[] = $goal;

          /* Dealing with players Stats*/
          /* Add goal event to a goaler*/
          $goaler= $this->teams[$team]->GetPlayerByNumber($goalerNumber);
            $goaler->stats["goal"][] = $goal;

          /* Add goal event to a assitant*/
          if($assistantNumber != null){
          $assistant= $this->teams[$team]->GetPlayerByNumber($assistantNumber);
            $goal["result"] = "Голевая";
            $assistant->stats["assistance"][] = $goal;          
          }
           
    }

    /**
     * {Giving a card to  aplayer }
     *
     * @param      <string>  $time           The time
     * @param      <string>  $team           The team
     * @param      <string>  $player_number  The player number
     */
    public function giveCard($time,$team,$player_number){

      ///get player by link
      $player = $this->teams[$team]->GetPlayerByNumber($player_number);

      $card = null;
      //if card is first
      if(!isset($player->stats["cards"]))
      {
        $card["type"] = "Желтая";
        $card["time"] = $time;
        $player->stats->cards[] = $card;
      }
      else {
        //else - giving more cards
        $card["type"] = "Желтая";
        $card["time"] = $time;
        
        $player->stats["cards"][] = $card;
        
        $card["type"] = "Красная";
        $card["time"] = $time;

        $player->stats["cards"][] = $card;

              
      }

      $player->stats = (array) $player->stats;

    }


    /**
     * { Replace the player -- triggering players properties}
     *
     * @param      <string>  $time        The time
     * @param      <string>  $team        The team
     * @param      <string>  $in_player   In player
     * @param      <string>  $out_player  The out player
     */
    public function replacePlayer($time,$team,$in_player,$out_player){
          
        $player_in = $this->teams[$team]->GetPlayerByNumber($in_player);

        $player_in->startTime = $time;
              
        $replace["player_num"] = $out_player;
        $replace["direction"] = "IN";
        $replace["time"] = $time;      
              
        $player_in->replacedBy = $replace;

        $replace = null;
       
       $player_out = $this->teams[$team]->GetPlayerByNumber($out_player);


       $player_out->endTime = $time;

        $replace["player_num"] = $out_player;
        $replace["direction"] = "OUT";
        $replace["time"] = $time;

        $player_out->replacedBy = $replace;

       $replace = null;
       
    }

   

    /**** VIEW GENERATORS ****/



    /**
     * Builds a compleate view
     *
     * @return     string  The view.
     */
    public function BuildView(){
      
      $output.= $this->BuildHeader("templates/header.html");
      $output.= $this->BuildResultSection("templates/result_section.html");
      $output.= $this->BuildCommands("templates/commands_section.html");
      $output.= $this->BuildMomentes("templates/events_section.html");
      $output.= $this->BuildReserve("templates/reserve_section.html");
      $output.= $this->BuildReplaced("templates/replaced_section.html");
      $output.= $this->BuildFooter("templates/footer.html");

      return "<main>".$output."</main>";
       
    }

    /**
     * Builds a header view
     *
     * @param      <string>  $header  The header template path
     *
     * @return     <string>  The header view
     */
    public function BuildHeader($header){
       return file_get_contents($header);
    }

     /**
     * Builds a footer  view
     *
     * @param      <string>  $footer  The footer template path
     *
     * @return     <string>  The footer view
     */
    public function BuildFooter($footer){
       return file_get_contents($footer);
    }
    
    /**
     * Builds a Reserve section view
     *
     * @param      <string>  $addres  Reserve section template path
     *
     * @return     <string>  Reserve section view
     */
    public function BuildReserve($addres){

      $block_template = file_get_contents($addres);
        $i = 1;
        foreach ($this->teams as $keyt => $team) {
          
          $player_st = null;
          foreach ($team->players as $keyp => $player) {
            if(!empty($player->reserve)){
              $player_st.= 
              '<div class="opponent__player">
              <div class="opponent__player-info">
                    <div class="opponent__player-name">'.$player->name.'</div>  
                    <div class="opponent__player-number">'.$player->number.'</div>  
                                  
              </div>      
              </div>';
            }
          }

           $placeholder =  '#team'.$i.'#';
                         
            $block_template =   str_replace( $placeholder, $player_st, $block_template);
             
            $i++;

        }
      
        return $block_template;

    }
    
    /**
     * Builds a Replaced section view
     *
     * @param      <string>  $addres  Replaced section template path
     *
     * @return     <string>  Replaced section view
     */
    public function BuildReplaced($addres){
        
        $block_template = file_get_contents($addres);
          $i = 1;
          foreach ($this->teams as $keyt => $team) {
            
            $player_st = null;
            foreach ($team->players as $keyp => $player) {
              if(!empty($player->replacedBy) && 
                  $player->replacedBy["direction"] =="IN"){
                $player_st.= 
                '<div class="opponent__player">
                <div class="opponent__player-info">
                      <div class="opponent__player-name">'.$player->name.'</div>  
                      <div class="opponent__player-time">'.$player->replacedBy["time"].'</div>  
                      <div class="opponent__player-who">'. $player->replacedBy["player_num"].'</div>  
                </div>      
                </div>';
              }
            }

             $placeholder =  '#team'.$i.'#';
                           
              $block_template =   str_replace( $placeholder, $player_st, $block_template);
               
              $i++;

          }
        
          return $block_template;
  
     } 

     /**
     * Builds a Momentes section view
     *
     * @param      <string>  $addres  Momentes section template path
     *
     * @return     <string>  Momentes section view
     */
    public function BuildMomentes($addres){
      

         $block_template = file_get_contents($addres);
 
          $i = 1;
         
         foreach ($this->events as $key_ev => $event) {

               $details= null;
               if($event->type == 'replacePlayer'){
                $event->type = "Замена";
                  $details = 
                     ' <div class="match-events__item-details-team"> 
                        '.$event->details["team"].'</div>
                      <div class="match-events__item-details-inPlayerNumber"> 
                      '.$event->details["inPlayerNumber"].'</div>
                      <div class="match-events__item-details-outPlayerNumber"> 
                      '.$event->details["outPlayerNumber"].'</div>';
                    
                     
                }

               if($event->type =='yellowCard'){
                $event->type = "Желтая карточка";
                     $details = 
                     '
                      <div class="match-events__item-details-team"> 
                        '.$event->details["team"].'</div>
                      <div class="match-events__item-details-PlayerNumber"> 
                      '.$event->details["playerNumber"].'</div>';
                }

                if($event->type =='goal'){
                  $event->type = "Гол";
                    $details = 
                     '
                      <div class="match-events__item-details-team"> 
                       '.$event->details["team"].'</div>
                      <div class="match-events__item-details-goaler"> 
                      '.$event->details["playerNumber"].'</div>
                      <div class="match-events__item-details-assistant"> 
                      '.$event->details["assistantNumber"].'</div>';
                }

                     
                     
                if($event->type =='dangerousMoment'){
                  $event->type = "Опасный момент";
                     $details = 
                     '<div class="match-events__item-details-team"> 
                       '.$event->details["team"].'</div>';
                }
       


              $events.=
                '<div class="match-events__item">
                <div class="match-events__item-header">
                  <div class="match-events__item-type">'.$event->type.'</div>
                  <div class="match-events__item-time">'.$event->time.'</div>
                  <div class="match-events__item-button"></div>
                </div>
                <div class="match-events__item-footer">
                  <div class="match-events__item-details"> 
                    '.$details.'
                  </div>
                  <div class="match-events__item-description">
                    '.$event->description.'
                  </div>
                </div>
              </div>';
         }

         

         $placeholder =  '#events#';
                           
         $block_template =  str_replace( $placeholder, $events, $block_template);
         return $block_template;

    }

     /**
     * Builds a Commands section view
     *
     * @param      <string>  $addres  Commands section template path
     *
     * @return     <string>  Commands section view
     */
    public function BuildCommands($addres){


         $block_template = file_get_contents($addres);
 
          $i = 1;
         
         foreach ($this->teams as $keyt => $team) {
   
              foreach ($team->players as $number => $player) {
              
                $field_time = $player->endTime - $player->startTime;

                if($player->reserve){
                     $status = "в запасе";
                     $st_flag = "opponent__player-info--out-field";
                }
                else {
                  $status = "в поле";
                  $st_flag = "opponent__player-info--in-field";
                }

                
                if(!empty($player->stats["cards"])){
                 

                  foreach ($player->stats["cards"] as $keyd => $card) {
                      $cards_n .=  
                       '<div class="opponent__player-stats-cards__item">
                        <div class="opponent__player-stats-cards__item-type">'.$card["type"].'</div>  
                        <div class="opponent__player-stats-cards__item-time">'.$card["time"].'</div>
                     </div>';
                     
                  }

                  $cards = '<div class="opponent__player-stats-cards">'.$cards_n.'</div>';
                }
                else 
                { 
                  $cards = '<div class="opponent__player-stats-cards">'."-".'</div>';
                }

                if(!empty($player->stats)){
                  foreach ($player->stats as $keya => $action) {


                    if($keya=="goal"){
                        foreach ($action as $keyg => $goal ) {
                         $goals_n .=  
                         '<div class="opponent__player-stats-goals__item">
                          <div class="opponent__player-stats-goals__item-time">'.$goal["time"].'</div>  
                          <div class="opponent__player-stats-goals__item-assistant">'.$team->GetNameByNumber($goal["assistantNumber"]).'</div>
                       </div>';
                            }                       
                    }

                    if($keya=="assistance"){
                      foreach ($action as $keyas => $assist ) {

                         $assistanse_n .=  
                           '<div class="opponent__player-stats-assistanse__item">
                            <div class="opponent__player-stats-assistanse__item-time">'.$assist["time"].'</div>  
                            <div class="opponent__player-stats-assistanse__item-to">'.$team->GetNameByNumber($assist["playerNumber"]).'</div>
                            <div class="opponent__player-stats-assistanse__item-result">'.$assist["result"].'</div>
                         </div>';
                       }
                    }
                    
                  }

                  if(strlen($goals_n))
                  $goals ='<div class="opponent__player-stats-goals">'.$goals_n.'</div>';
                   else $goals ='<div class="opponent__player-stats-goals">'.'0'.'</div>';

                  if(strlen($assistanse_n))
                    $assistanse =' <div class="opponent__player-stats-assistanse">'.$assistanse_n.'</div>';
                  else $assistanse =' <div class="opponent__player-stats-assistanse">'.'0'.'</div>';


                  $goals_n = null;
                  $assistanse_n = null;
                  $cards_n = null;
                 
                }
                else
                {
                      if(strlen($goals_n))
                    $goals ='<div class="opponent__player-stats-goals">'.$goals_n.'</div>';
                  else $goals ='<div class="opponent__player-stats-goals">'.'0'.'</div>';

                  if(strlen($assistanse_n))
                    $assistanse =' <div class="opponent__player-stats-assistanse">'.$assistanse_n.'</div>';
                  else $assistanse =' <div class="opponent__player-stats-assistanse">'.'0'.'</div>';
                }



                
                   $players_list.='
                  <div class="opponent__player" id="p_'.$i."_".$player->number.'">
                    <div class="opponent__player-info '.$st_flag.'">
                      <div class="opponent__player-name">'.$player->name.'</div>  
                      <div class="opponent__player-number">'.$player->number.'</div>  
                      <div class="opponent__player-status">'.$status.'</div>              
                      <div class="opponent__player-stats-button"></div>  
                    </div>
                    <div class="opponent__player-stats">
                      <div class="opponent__player-stats-title">Статистика</div>  
                      <div class="opponent__player-stats-time">'.$field_time.'</div>'  
                      .$cards.$goals.$assistanse.
                    '</div>  
                  </div>';
                $status = null;
                $field_time = null;
                $cards = null;
                $goals = null;
                $assistanse = null;
                 
              }
              
              $placeholder =  '#team'.$i.'.players#';
                           
              $block_template =   str_replace( $placeholder, $players_list, $block_template);
              $i++;
              $actions = null;
              $players_list = null;
              $placeholder = null;

         }

         return  $block_template;  

            
    }
    
     /**
     * Builds a Result section view
     *
     * @param      <string>  $addres  Result section template path
     *
     * @return     <string>  Result section view
     */
    public function BuildResultSection($addres){
         
         $i = 1;

         $block_template = file_get_contents($addres);
          
         
         foreach ($this->teams as $key => $team) {

              if(!empty($team->goals)){
                foreach ($team->goals as $key => $goal) {
                  $player = $team->GetPlayerByNumber($goal["playerNumber"]);
                  $name = $player->name;
                  $id = "p_".$i."_".$player->number;
                  $time = $goal["time"]."'";

                  $actions.= '<div class="opponent__action" data-id="'.$id.'">
                      <div class="opponent__author">'.$name.'</div>
                      <div class="opponent__time">'.$time.'</div>
                     </div>';
                }
              }

              
             
              $placeholders = array(
                "#team".$i."_title#" => $team->title,
                "#team".$i."_goals#" => count($team->goals),
                "#team".$i."_actions#" => $actions
              );

              
              foreach ($placeholders as $key => $placeholder) {
                $block_template = str_replace($key, $placeholder,$block_template);

              }

            $i++;
            $actions = null;
            $placeholders = null;

         } 

         
       
         return $block_template;          
                
    }

 
  }?>