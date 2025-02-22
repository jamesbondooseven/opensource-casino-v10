<?php 
namespace VanguardLTE\Games\PirateGoldDeluxe
{

     use VanguardLTE\Game;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\Collect;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\GameSettings;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\Loader;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\Log;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\Spin;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\DoMysteryScatter;
    use VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib\DoBonus;
    use VanguardLTE\Shop;
    use VanguardLTE\User;

    set_time_limit(10);
    class Server
    {
        public function get($request, $game)
        {
            try
                {

                  $userId = \Auth::id();  
                 if( $userId == null )
                {
                    $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                    var_dump($request->callbackUrl);
                    var_dump($request->userId);
                    exit( $response );
                }
                $user = User::lockForUpdate()->find($userId);
                $shop = Shop::find($user->shop_id);
                $game = Game::where([
                    'name' => $game,
                    'shop_id' => $user->shop_id
                ])->lockForUpdate()->first();
                $bank = \VanguardLTE\GameBank::where(['shop_id' => $user->shop_id])->first();
                $jpgs = \VanguardLTE\JPG::where(['shop_id' => $user->shop_id, ])->lockForUpdate()->get();
                $init = require 'init.php';
                $log = new Log($game->id, $user->id);
                $callbackUrl = $request->callbackUrl;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                

                $action = $request->input('action');
                $bet = $request->input('c');
                $lines = $request->input('l');
                $index = $request->input('index');
                $counter = $request->input('counter');
                // $doubleChance = $request->input('bl');
                $pur = $request->input('pur');
                /*$panic = $request->input('panic');
                $panic1 = $request->input('panic1');
                if ($panic){
                    return $panic($panic1);
                }*/

                ///////////////////////////////////////////

                if( $action == 'doInit')
                {
                    $loader = new Loader($init, $user->balance, $log);
                    $response = $loader->initStr();
                    exit( $response );
                }
                ///////////////////////////////////////////
                if ($action == 'doSpin'){
                    $gameSettings = new GameSettings($init);
                    $response = Spin::spinResult($user, $game, $bet, $lines, $log, $gameSettings, $index, $counter, $callbackUrl, $pur, $bank, $shop, $jpgs);
                    exit( $response );
                }
                ///////////////////////////////////////////
                if ($action == 'doCollect' || $action == 'doCollectBonus' ){
                    $response = Collect::collect($user, $index, $counter, $log, $callbackUrl, $game);
                    exit( $response );
                }
                ///////////////////////////////////////////
                if ($action == 'doMysteryScatter'){
                    $response = DoMysteryScatter::doMystery($user, $game);
                    exit( $response );
                }
                ///////////////////////////////////////////
                if ($action == 'doBonus'){
                    var_dump('before doBonus');
                    $gameSettings = new GameSettings($init);
                    $response = DoBonus::doBonus($user, $game, $bet, $lines, $log->getLog(), $index, $counter, $bank, $shop, $jpgs, $gameSettings->all);
                    exit( $response );
                }
                ///////////////////////////////////////////
                if( $request['action'] == 'settings' )
                {
                    $response = 'SoundState=true_true_true_false_false;FastPlay=false;Intro=false;StopMsg=0;TurboSpinMsg=0;BetInfo=0_0;BatterySaver=false;ShowCCH=false;ShowFPH=true;CustomGameStoredData=;Coins=false;Volume=1;InitialScreen=10,7,6_11,3,9_6,4,8_5,11,4_10,11,7;SBPLock=true';
                    exit( $request['settings'] );
                }
                ///////////////////////////////////////////
                if( $request['action'] == 'update' )
                {
                    $time = (int) round(microtime(true) * 1000);
                    $response = 'balance_bonus=0.00&balance='.$user->balance.'&balance_cash='.$user->balance.'&stime='.$time;
                    exit( $response );
                }
                ///////////////////////////////////////////
                    $response = ["error" => 0,"description" => "OK"];
                    echo json_encode($response);
                }
                catch( \Exception $e ) 
                {
                }
             
        }
    }

}
