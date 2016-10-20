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
    
    // Generic template
    if ($generic == 'lista_kebabow') {
        $jsonData = '{
          "recipient":{
            "id":"'.$sender.'"
          },
          "message":{
            "attachment":{
              "type":"template",
              "payload":{
                "template_type":"generic",
                "elements":[
                  {
                    "title":"Kebab turecki",
                    "image_url":"http://samira.radom.pl/img/kebab_w_bulceB.png",
                    "subtitle":"Oryginalny turecki kebab.",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Wybieram Cie!",
                        "payload":"menu_keb1"
                      }              
                    ]
                  },
                  {
                    "title":"Kebaby dla twardzieli",
                    "image_url":"http://static.fachowcy.pl/files/525493200/na-cienkim-.jpg",
                    "subtitle":"Wykwintne dania dla prawdziwych koneserów.",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Wybieram Cie!",
                        "payload":"menu_keb2"
                      }              
                    ]
                  }
                ]
              }
            }
          }
        }';
    }
    
    // Menu keb1
    if ($generic == 'kebab1') {
        $jsonData = '{
          "recipient":{
            "id":"'.$sender.'"
          },
          "message":{
            "attachment":{
              "type":"template",
              "payload":{
                "template_type":"generic",
                "elements":[
                  {
                    "title":"Kebab ostry!",
                    "image_url":"http://samira.radom.pl/img/kebab_w_bulceB.png",
                    "subtitle":"Baraninka, suróweczki, sosik. Cena - 10 PLN",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Chcę tego!",
                        "payload":"order_keb1"
                      }              
                    ]
                  },
                  {
                    "title":"Kebsik Ameryksik",
                    "image_url":"http://static.fachowcy.pl/files/525493200/na-cienkim-.jpg",
                    "subtitle":"Baraninka, fryteczki, sosiczki. Cena - 15 PLN",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Dej mie to",
                        "payload":"order_keb2"
                      }              
                    ]
                  }
                ]
              }
            }
          }
        }';
    }
    
    //Menu kebab 2
     if ($generic == 'kebab2') {
        $jsonData = '{
          "recipient":{
            "id":"'.$sender.'"
          },
          "message":{
            "attachment":{
              "type":"template",
              "payload":{
                "template_type":"generic",
                "elements":[
                  {
                    "title":"Kebab łagodny",
                    "image_url":"http://samira.radom.pl/img/kebab_w_bulceB.png",
                    "subtitle":"Mięso drobiowe, surówki. Cena - 10 PLN",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Chcę tego!",
                        "payload":"order_keb1"
                      }              
                    ]
                  },
                  {
                    "title":"Zawijaniec",
                    "image_url":"http://static.fachowcy.pl/files/525493200/na-cienkim-.jpg",
                    "subtitle":"Baraninka, surówka, sos. Cena - 15 PLN",
                    "buttons":[
                      {
                        "type":"postback",
                        "title":"Chcę to!",
                        "payload":"order_keb2"
                      }              
                    ]
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


?>