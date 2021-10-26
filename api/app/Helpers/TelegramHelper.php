<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TelegramHelper {
    const API_URL = 'https://api.telegram.org/' . env('API');

    const CONFIG = [
        'formattazione_predefinita' => 'HTML',
        'formattazione_messaggi_globali' => 'HTML',
        'nascondi_anteprima_link' => false,
        'tastiera_predefinita' => 'inline',
        'funziona_nei_canali' => true,
        'funziona_messaggi_modificati' => true,
        'funziona_messaggi_modificati_canali' => true
    ];

    public static function getChat($chatID) {
        $args = [
            'chat_idl' => $chatID
        ];

        return Http::post(self::API_URL . '/getChat', $args);
    }

    public static function sendMessage($chatID, $text, $rmf = false, $pm = 'pred', $dis = false, $replyto = false, $inline = 'pred') {
        if ($pm === 'pred') {
            $pm = self::CONFIG['formattazione_predefinita'];
        }

        if($inline=='pred') {
            if(self::CONFIG['tastiera_predefinita'] == 'inline') {
                $inline = true;
            }
            elseif(self::CONFIG['tastiera_predefinita'] == 'normale')
                $inline = false;
            }

            if($rmf === 'nascondi') {
                $inline = false;
            }


            $dal = self::CONFIG['nascondi_anteprima_link'];

            if(!$inline) {
                if ($rmf === 'nascondi') {
                    $rm = ['hide_keyboard' => true];
                }
                else{
                    $rm = [
                        'keyboard' => $rmf,
                        'resize_keyboard' => true
                    ];
                }
            }
            else{
                $rm = ['inline_keyboard' => $rmf];
            }

            $rm = json_encode($rm);

            $args = array(
                'chat_id' => $chatID,
                'text' => $text,
                'disable_notification' => $dis,
                'parse_mode' => $pm
            );

            if($dal) {
                $args['disable_web_page_preview'] = $dal;
            }

            if($replyto) {
                $args['reply_to_message_id'] = $replyto;
            }

            if($rmf) {
                $args['reply_markup'] = $rm;
            }

            if($text) {
                $response = Http::post(self::API_URL . '/sendMessage', $args)->json();

                if ($response->error_code) {
                    return false;
                }

                return $response;
            }
    }

    public static function sendPhoto($chatID, $img, $rmf = false, $cap = '') {
        $args = [
            'chat_id' => $chatID,
            'photo' => $img,
            'caption' => $cap
        ];

        if(strpos($args['video'], '.')) {
            $args['video'] = str_replace('index.php', '', $_SERVER['SCRIPT_URI']) . $args['video'];
        }

        if ($rmf) {
            $args['reply_markup'] = json_encode(['inline_keyboard' => $rmf]);
        }

        $response = Http::post(self::API_URL . '/sendPhoto', $args)->json();

        if ($response->error_code) {
            return false;
        }

        return $response;
    }

    public static function sendVideo($chatID, $videoPath, $keyboard = null, $caption = '') {
        $args = [
            'chat_id' => $chatID,
            'video' => $videoPath,
            'caption' => $caption
        ];

        if(strpos($args['video'], '.')) {
            $args['video'] = str_replace('index.php', '', $_SERVER['SCRIPT_URI']) . $args['video'];
        }

        if ($keyboard) {
            $args['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }

        $response = Http::post(self::API_URL . '/sendVideo', $args)->json();

        if ($response->error_code) {
            return false;
        }

        return $response;
    }

    public static function answerCallbackQuery($id, $text, $alert = false, $cbmid = false, $ntext = false, $nmenu = false, $dis = False, $npm = 'pred') {
        if ($npm === 'pred') {
            $npm = self::CONFIG['formattazione_predefinita'];
        }

        $args = [
            'callback_query_id' => $id,
            'text' => $text,
            'show_alert' => $alert
        ];

        $response = Http::get(self::API_URL . '/answerCallbackQuery', $args);

        if($cbmid) {
            if($nmenu) {
                $rm = array('inline_keyboard' => $nmenu);
                $rm = json_encode($rm);
            }

            $args = [
                'chat_id' => $chatID,
                'message_id' => $cbmid,
                'text' => $ntext,
                'parse_mode' => $npm,
            ];

            if ($nmenu) {
                $args['reply_markup'] = json_encode(['inline_keyboard' => $nmenu]);
            }

            if ($dis) {
                $args['disable_web_page_preview'] = true;
            }

            $response = Http::post(self::API_URL . '/editMessageText', $args)->json();

            return $response;
        }
    }

    public static function forwardMessage($chatID, $fromChat, $msgID) {
        $args = array(
            'chat_id' => $chatID,
            'from_chat_id' => $fromChat,
            'message_id' => $msgID
        );

        return Http::get(self::API_URL . '/forwardMessage', $args);
    }

    public static function deleteMessage($chatID, $messageID) {
        $args = [
            'chat_id' => $chatID,
            'message_id' => $messageID
        ];

        Http::post(self::API_URL . '/deleteMessage', $args);
    }
}
