<?php


function wysylanie_tekst ($access_token, $sender, $message_to_reply, $input, $przycisk, $przycisk_url, $przycisk_sub, $przycisk_pz, $generic) {

    //API Url
    $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$access_token;

    //Initiate cURL.
    $ch = curl_init($url);
    

    //The JSON data.
    $jsonData = '{
        "recipient":{
            "id":"'.$sender.'"
        },
        "message":{
            "text":"'.$message_to_reply.'"
        }
    }';
    
    // Jesli zdefiniowano tekst i przycisk
    if (isset($przycisk)) {
        $jsonData = '{
        "recipient":{
            "id":"'.$sender.'"
        },
        "message":{
            "attachment":{
              "type":"template",
              "payload":{
                "template_type":"button",
                "text":"'.$message_to_reply.'",
                "buttons":[
                 {
                  "type":"postback",
                  "title":"'.$przycisk[0].'",
                  "payload":"'.$przycisk[1].'"
                }
            ]
          }
        }
      }
    }';
    }
    
    // Jesli zdefiniowano przycyciski quick reply
    if (isset($przycisk_pz)) {
        $jsonData = '{
          "recipient":{
            "id":"'.$sender.'"
          },
          "message":{
            "text":"'.$message_to_reply.'",
            "quick_replies":[
              {
                "content_type":"text",
                "title":"'.$przycisk_pz[0].'",
                "payload":"'.$przycisk_pz[1].'"
              },
              {
                "content_type":"text",
                "title":"'.$przycisk_pz[2].'",
                "payload":"'.$przycisk_pz[3].'"
              }
            ]
          }
        }';
    }
    // Jesli zdefiniowano tekst i przycisk URL
    if (isset($przycisk_url)) {
        $jsonData = '{
        "recipient":{
            "id":"'.$sender.'"
        },
        "message":{
            "attachment":{
              "type":"template",
              "payload":{
                "template_type":"button",
                "text":"'.$message_to_reply.'",
                "buttons":[
                  {
                    "type":"web_url",
                    "url":"'.$przycisk_url[1].'",
                    "title":"'.$przycisk_url[0].'",
                    "webview_height_ratio": "tall"
                  }
                ]
              }
            }
        }
    }';
    }
    

    //Encode the array into JSON.
    $jsonDataEncoded = $jsonData;
    
    //Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
    
    //Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    
    //Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
    //Execute the request
    if(!empty($input['entry'][0]['messaging'][0]['message'])){
        $result = curl_exec($ch);
    }
    if(!empty($input['entry'][0]['messaging'][0]['postback'])){
        $result = curl_exec($ch);
    }
}

function wysylanie_json ($access_token, $sender, $wiadomosc_zwrotna_json, $input) {
    
    //API Url
    $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$access_token;

    //Initiate cURL.
    $ch = curl_init($url);

    //Wiadomosc zwrotna
    $jsonData = $wiadomosc_zwrotna_json;

    //Encode the array into JSON.
    $jsonDataEncoded = $jsonData;
    
    //Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
    
    //Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    
    //Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
    //Execute the request
    if(!empty($input['entry'][0]['messaging'][0]['message']) || !empty($input['entry'][0]['messaging'][0]['postback'])) {
        $result = curl_exec($ch);
    }
}


?>