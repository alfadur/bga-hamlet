<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * hamletthevillagebuildinggame.action.php
 *
 * HamletTheVillageBuildingGame main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/hamletthevillagebuildinggame/hamletthevillagebuildinggame/myAction.html", ...)
 *
 */

class action_hamletthevillagebuildinggame extends APP_GameAction
{
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if (self::isArg('notifwindow')) {
            $this->view = 'common_notifwindow';
  	        $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
  	    } else {
            $this->view = 'hamletthevillagebuildinggame_hamletthevillagebuildinggame';
            self::trace( 'Complete reinitialization of board game' );
        }
  	} 

    public function build() {
        self::setAjaxMode();
        $buildingId = self::getArg('x', AT_posint, true);
        $x = self::getArg('x', AT_int, true);
        $y = self::getArg('y', AT_int, true);
        $z = self::getArg('z', AT_int, true);
        $orientation = self::getArg('orientation', AT_posint, true);
        $this->game->build($buildingId, $x, $y, $z, $orientation);
        self::ajaxResponse();
    }

    public function moveDonkey()
    {
        self::setAjaxMode();
        $id = self::getArg('id', AT_posint, true);
        $tile = self::getArg('tile', AT_posint, true);
        $this->game->moveDonkey($id, $tile);
        self::ajaxResponse();
    }
}
  

