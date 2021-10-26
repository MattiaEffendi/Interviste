<?php

namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BotController extends Controller {
    const CB_TEXT = "\xf0\x9f\x91\x8c\xf0\x9f\x8f\xbb Ok!";

    const ADMINS = [
        135094094,
        119063642
    ];

    public function run(Request $request) {
        $update = $request->input();

        $chatID = $update->message->chat->id;
        $userID = $update->message->from->id;
        $msg = $update->message->text;
        $username = $update->message->from->username;
        $nome = $update->message->from->first_name;
        $cognome = $update->message->from->last_name;

        if ($chatID < 0) {
            $titolo = $update->message->chat->title;
            $usernamechat = $update->message->chat->username;
        }

        $voice = $update["message"]["voice"]["file_id"];
        $photo = $update["message"]["photo"][0]["file_id"];
        $document = $update["message"]["document"]["file_id"];
        $audio = $update["message"]["audio"]["file_id"];
        $sticker = $update["message"]["sticker"]["file_id"];

        $cbData = null;

        if (isset($update->callback_query)) {
            $cbID = $update->callback_query->id;
            $cbData = $update->callback_query->data;
            $cbmID = $update->callback_query->message->message_id;
            $chatID = $update->callback_query->message->chat->id;
            $userID = $update->callback_query->from->id;
            $nome = $update->callback_query->from->first_name;
            $cognome = $update->callback_query->from->last_name;
            $username = $update->callback_query->from->username;
        }

        if ($message || $cbData) {
            $user = DB::table('utenti')->where('id', $userID)->first();

            if (!$user) {
                TelegramHelper::sendMessage($chatID, 'aaa');
                TelegramHelper::answerCallbackQuery($cbID, 'Ti ho registrato al bot!', false);
            }
        }

        if ($message === '/start' && $chatId > 0) {
            $kb = [
                [
                    'text' => "\xf0\x9f\x91\xa4 Profilo",
                    "callback_data" => 'Profile'
                ]
            ];

            TelegramHelper::sendMessage($chatID, $kb,  "\xf0\x9f\x8e\xa4 <b>Ciao!</b>\nBenvenuto nel bot della classifica di @Interviste!\n\nPer vedere le tue statistiche, clicca il bottone sottostante.");
        }

        if($cbdata == "Profile"){
            $q = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $q->execute(array(':id' => $userID));
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $rank = $res['Rank'];
            $suggerimenti = $res['Suggerimenti'];
            $commenti = $res['Commenti'];
            $totale = $res['Totale'];
            $qq = $sql->prepare('SELECT * FROM Utenti');
            $qq->execute();
            $totale = $qq->rowCount();
            $menu[] = array(
                array(
                    "text" => "\xf0\x9f\x94\x99 Indietro",
                    "callback_data" => "Home"
                )
            );
            if($res['Suggerimenti'] == 1) $lettera = "a";
            else $lettera = "e";

            if($res['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";
            cb_reply($cbid, $cbtext, false, $cbmid, "\xf0\x9f\x91\xa4 <b>Profilo</b>\nHai suggerito attualmente <b>".$suggerimenti." domand$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa tua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $menu);
        }

        if($cbdata == "Home"){
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x91\xa4 Profilo",
                    "callback_data" => "Profile"
                )
            );
            cb_reply($cbid, $cbtext, false, $cbmid, "\xf0\x9f\x8e\xa4 <b>Ciao!</b>\nBenvenuto nel bot della classifica di @Interviste!\n\nPer vedere le tue statistiche, clicca il bottone sottostante.", $kb);
        }

        if($msg == "/utente" && in_array($userID, $admins)) {
            $id = $update['message']['reply_to_message']['from']['id'];
            $messageID = $update['message']['reply_to_message']['message_id'];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $res = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $res['Rank'];
            $suggerimenti = $res['Suggerimenti'];
            $commenti = $res['Commenti'];
            $totale = $res['Totale'];
            $qq = $sql->prepare('SELECT * FROM Utenti');
            $qq->execute();
            $totale = $qq->rowCount();
            if ($res['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if ($res['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|" . $id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|" . $id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|" . $id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|" . $id
                )
            );
            fw($userID, $chatID, $messageID);
            sm($userID, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>" . $suggerimenti . " suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>" . $rank . "°</b> su <b>" . $totale . "</b>.", $kb);
        }

        if($cbdata && explode("|", $cbdata)[0] == "AddSugg" && in_array($userID, $admins)){
            $id = explode("|", $cbdata)[1];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $qq = $sql->prepare('UPDATE Utenti SET Suggerimenti = :s WHERE ID = :id');
            $new = intval($res['Suggerimenti']) + 1;
            $qq->execute(array(':id' => $id, ':s' => $new));
            $neww = intval($res['Suggerimenti']) + intval($res['Commenti']) + intval(1);
            $qqqqqqq = $sql->prepare('UPDATE Utenti SET Totale = :s WHERE ID = :id');
            $qqqqqqq->execute(array(':id' => $id, ':s' => $neww));
            $qrq = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $qrq->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $ress = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $ress['Rank'];
            $suggerimenti = $ress['Suggerimenti'];
            $commenti = $ress['Commenti'];
            $totale = $ress['Totale'];
            $qqqqq = $sql->prepare('SELECT * FROM Utenti');
            $qqqqq->execute();
            $totale = $qqqqq->rowCount();
            if($ress['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if($ress['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|".$id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|".$id
                )
            );
            cb_reply($cbid, $cbtext, false, $cbmid, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>".$suggerimenti." suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $kb);
        }

        if($cbdata && explode("|", $cbdata)[0] == "RemoveSugg" && in_array($userID, $admins)){
            $id = explode("|", $cbdata)[1];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $qq = $sql->prepare('UPDATE Utenti SET Suggerimenti = :s WHERE ID = :id');
            $new = intval($res['Suggerimenti']) - 1;
            $qq->execute(array(':id' => $id, ':s' => $new));
            $neww = intval($res['Suggerimenti']) + intval($res['Commenti']) - intval(1);
            $qqqqqqq = $sql->prepare('UPDATE Utenti SET Totale = :s WHERE ID = :id');
            $qqqqqqq->execute(array(':id' => $id, ':s' => $neww));
            $qrq = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $qrq->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $ress = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $ress['Rank'];
            $suggerimenti = $ress['Suggerimenti'];
            $commenti = $ress['Commenti'];
            $totale = $ress['Totale'];
            $qqqqq = $sql->prepare('SELECT * FROM Utenti');
            $qqqqq->execute();
            $totale = $qqqqq->rowCount();
            if($ress['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if($ress['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|".$id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|".$id
                )
            );
            cb_reply($cbid, $cbtext, false, $cbmid, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>".$suggerimenti." suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $kb);
        }

        if($cbdata && explode("|", $cbdata)[0] == "AddComment" && in_array($userID, $admins)){
            $id = explode("|", $cbdata)[1];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $qq = $sql->prepare('UPDATE Utenti SET Commenti = :s WHERE ID = :id');
            $new = intval($res['Commenti']) + 1;
            $qq->execute(array(':id' => $id, ':s' => $new));
            $neww = intval($res['Suggerimenti']) + intval($res['Commenti']) + intval(1);
            $qqqqqqq = $sql->prepare('UPDATE Utenti SET Totale = :s WHERE ID = :id');
            $qqqqqqq->execute(array(':id' => $id, ':s' => $neww));
            $qrq = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $qrq->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $ress = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $ress['Rank'];
            $suggerimenti = $ress['Suggerimenti'];
            $commenti = $ress['Commenti'];
            $totale = $ress['Totale'];
            $qqqqq = $sql->prepare('SELECT * FROM Utenti');
            $qqqqq->execute();
            $totale = $qqqqq->rowCount();
            if($ress['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if($ress['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|".$id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|".$id
                )
            );
            cb_reply($cbid, $cbtext, false, $cbmid, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>".$suggerimenti." suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $kb);
        }

        if($cbdata && explode("|", $cbdata)[0] == "RemoveComment" && in_array($userID, $admins)){
            $id = explode("|", $cbdata)[1];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $qq = $sql->prepare('UPDATE Utenti SET Commenti = :s WHERE ID = :id');
            $new = intval($res['Commenti']) - 1;
            $qq->execute(array(':id' => $id, ':s' => $new));
            $neww = intval($res['Suggerimenti']) + intval($res['Commenti']) - intval(1);
            $qqqqqqq = $sql->prepare('UPDATE Utenti SET Totale = :s WHERE ID = :id');
            $qqqqqqq->execute(array(':id' => $id, ':s' => $neww));
            $qrq = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $qrq->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $ress = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $ress['Rank'];
            $suggerimenti = $ress['Suggerimenti'];
            $commenti = $ress['Commenti'];
            $totale = $ress['Totale'];
            $qqqqq = $sql->prepare('SELECT * FROM Utenti');
            $qqqqq->execute();
            $totale = $qqqqq->rowCount();
            if($ress['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if($ress['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|".$id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|".$id
                )
            );
            cb_reply($cbid, $cbtext, false, $cbmid, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>".$suggerimenti." suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $kb);
        }

        if($cbdata == "Placeholder"){
            cb_reply($cbid, "Questo pulsante è qui per funzione estetica!", false);
        }

        if(strpos(" ".$msg, "/user ") && in_array($userID, $admins)){
            $id = explode(" ", $msg, 2)[1];
            $q = $sql->prepare('SELECT * FROM Utenti WHERE ID = :id');
            $q->execute(array(':id' => $id));
            $qqqq = $sql->prepare("SELECT ID, Suggerimenti, Commenti, Totale, FIND_IN_SET( Totale, ( SELECT GROUP_CONCAT( Totale ORDER BY Totale DESC ) FROM Utenti ) ) AS Rank FROM Utenti WHERE ID = :id");
            $qqqq->execute(array(':id' => $id));
            $res = $qqqq->fetch(PDO::FETCH_ASSOC);
            $rank = $res['Rank'];
            $suggerimenti = $res['Suggerimenti'];
            $commenti = $res['Commenti'];
            $totale = $res['Totale'];
            $qq = $sql->prepare('SELECT * FROM Utenti');
            $qq->execute();
            $totale = $qq->rowCount();
            if($res['Suggerimenti'] == 1) $lettera = "o";
            else $lettera = "i";

            if($res['Commenti'] == 1) $lettera2 = "o";
            else $lettera2 = "i";

            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Suggerimenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddSugg|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveSugg|".$id
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xf0\x9f\x94\xbb Commenti \xf0\x9f\x94\xbb",
                    "callback_data" => "Placeholder"
                )
            );
            $kb[] = array(
                array(
                    "text" => "\xe2\x9e\x95 Aggiungi",
                    "callback_data" => "AddComment|".$id
                ),
                array(
                    "text" => "\xe2\x9e\x96 Togli",
                    "callback_data" => "RemoveComment|".$id
                )
            );
            sm($chatID, "<b>Dettaglio dell'utente $id</b>.\n\nHa suggerito attualmente <b>".$suggerimenti." suggeriment$lettera</b> e <b>$commenti comment$lettera2</b>.\n\nLa sua posizione in classifica è attualmente <b>".$rank."°</b> su <b>".$totale."</b>.", $kb);

        }

        if($msg == "/classifica"){
            $q = $sql->prepare('SELECT * FROM Utenti ORDER BY Totale DESC LIMIT 10');
            $q->execute();
            $class = "";
            $i = 0;
            while($res = $q->fetch(PDO::FETCH_ASSOC)){
                ++$i;
                if(json_decode(getChat($res['ID']), true)['description'] == "Bad Request: chat not found") $men = $res['ID'];
                else $men = "<a href='tg://user?id=".$res['ID']."'>".json_decode(getChat($res['ID']), true)['result']['first_name']."</a>";
                $class .= "<b>$i:</b> ".$men.": <b>".$res['Suggerimenti']." domande</b> e <b>".$res['Commenti']." commenti</b>.\n";
            }
            $sum = $sql->prepare('SELECT SUM(Suggerimenti) FROM Utenti');
            $sum->execute();
            $res = $sum->fetch(PDO::FETCH_ASSOC);
            $sum2 = $sql->prepare('SELECT SUM(Commenti) FROM Utenti');
            $sum2->execute();
            $ress = $sum2->fetch(PDO::FETCH_ASSOC);
            sm($chatID, "<b>\xf0\x9f\x8f\x85 Classifica degli utenti</b>\n\n".$class."\n<i>In totale, sono state suggerite ".$res['SUM(Suggerimenti)']." domande, e sono stati effettuati ".$ress['SUM(Commenti)']." commenti.</i>");
        }
    }

    private static function setPage($userID, $page = '-') {
        DB::table('utenti')->where('id', $userID)->update(['stato' => $page]);
    }

    private function getStatus($userID){
        $user = DB::table('utenti')->where('id', $userID);
        return $user->stato;
    }
}
