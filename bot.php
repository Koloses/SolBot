<?php
header('Content-Type: text/html; charset=utf-8');

include ('config.php');
include ('wysylanie.php');


// ##################################################################

if(isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    $hub_verify_token = $_REQUEST['hub_verify_token'];
}


if ($hub_verify_token === $verify_token) {
    echo $challenge;
}

// ##################################################################

$input = json_decode(file_get_contents('php://input'), true);

$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
$message = $input['entry'][0]['messaging'][0]['message']['text'];
$czas = $input['entry'][0]['messaging'][0]['timestamp'];
$payload = $input['entry'][0]['messaging'][0]['postback']['payload'];
$quick_payload = $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];
$naklejka = $input['entry'][0]['messaging'][0]['message']['sticker_id'];

// $zapisanie = file_put_contents('input.json', print_r($input, true), FILE_APPEND);
// ##################################################################


// Sprawdzenie jaki tryb ma ustawiony uzytkownik

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8");

// Sprawdzenie
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$query = "SELECT tryb FROM tryby WHERE senderID = ".$sender." ";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

echo '<br>';
// var_dump($row);

if ($row['tryb'] != null) {
    $tryb = $row['tryb'];
}

$conn->close();

    // Rozpoznawanie wiadomosci
    if(preg_match('[witaj]', strtolower($message)) || preg_match('[siema]', strtolower($message)) || preg_match('[siemka]', strtolower($message)) || preg_match('[hej]', strtolower($message)) || preg_match('[hejka]', strtolower($message)) || preg_match('[hejo]', strtolower($message)) || preg_match('[cześć]', strtolower($message)) || preg_match('[elo]', strtolower($message))) {
        $message_to_reply = 'Witaj!'; 
    } else if (preg_match('[ustt]', strtolower($message)) && $sender == $sender_moj) { // nie zmieniac warunku
        wiad_powitalna ($access_token, $sender, $message_to_reply, $input);
        przycisk_rozpocznij ($access_token, $sender, $message_to_reply, $input);
    } else if (preg_match('[menu]', strtolower($message))) {
        $message_to_reply = 'Wybierz interesującą Cię dziedzinę';
        $przycisk_pz = array("Gastronomia", "gastronomia", "Inne - OFF", "inne");
    } else if (preg_match('[zamowic]', strtolower($message)) || preg_match('[zamówić]', strtolower($message)) || preg_match('[zjesc]', strtolower($message)) || preg_match('[zjeść]', strtolower($message))) {
        przypisz_tryb ($sender, 'gastronomia');
        $message_to_reply = 'Co masz ochotę zjeść?';
        if (preg_match('[kebab]', strtolower($message)) || preg_match('[kebaba]', strtolower($message))) {
            $message_to_reply = 'Oto lista lokali serwujących wykwintne kebabosy w Nowej Soli!\nWybierz coś dla siebie!';
            $lista_lokali = dopasuj_lokale($sender, 'fast-food');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        } else if (preg_match('[obiad]', strtolower($message))) {
            $lista_lokali = dopasuj_lokale($sender, 'restauracja');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        }
        if (preg_match('[chinszczyzne]', strtolower($message)) || preg_match('[chińszczyzne]', strtolower($message)) || preg_match('[chińszczyznę]', strtolower($message))) {
            $lista_lokali = dopasuj_lokale($sender, 'chinszczyzna');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        }
    } else if (preg_match('[nowe]', strtolower($message))) {
        usun_tryb($sender);
        $message_to_reply = 'Możesz złożyć nowe zamówienie';
        $tryb = null;
    } else {
        $message_to_reply = 'Nie rozumiem.';
    }
    
    // // Payload
    if ($payload == 'zrealizuj') {
        usun_tryb($sender);
        sleep(1);
        $message_to_reply = "Twoje zamówienie zostanie przekazane teraz do lokalu, w którym zamawiałeś. Smacznego!";
        goto wysylanie;
    }
    
    if ($tryb == "gastronomia") {
        $message_to_reply = 'Co masz ochotę zjeść? Może kebaba?';
        if (preg_match('[kebab]', strtolower($message)) || preg_match('[kebaba]', strtolower($message))) {
            $lista_lokali = dopasuj_lokale($sender, 'fast-food');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        } else if (preg_match('[chińszczyzne]', strtolower($message)) || preg_match('[chińszczyznę]', strtolower($message)) || preg_match('[chinszczyzne]', strtolower($message))) {
            $lista_lokali = dopasuj_lokale($sender, 'chinszczyzna');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        } else if (preg_match('[obiad]', strtolower($message))) {
            $lista_lokali = dopasuj_lokale($sender, 'restauracja');
            $wiadomosc_zwrotna_json = konwersja_na_json ($lista_lokali, $sender);
        }
        
        // Wybor lokali
        if ($payload == 'lokal1') {
            
            $tablica_dan = pobierz_dania ('1');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
            
        } else if ($payload == 'lokal2') {
            
            // $message_to_reply = 'Lokal 2 - Kuchnia Azjatycka';
            
            $tablica_dan = pobierz_dania ('2');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
            
        } else if ($payload == 'lokal3') {
            // $message_to_reply = 'Lokal 3 - Restauracja Polska';
            $tablica_dan = pobierz_dania ('3');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal4') {
            $tablica_dan = pobierz_dania ('4');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal5') {
            $message_to_reply = 'Lokal 5 - Doner Kebab';
            
            $tablica_dan = pobierz_dania ('5');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal6') {
            $message_to_reply = 'Lokal 6 - Hong Ha';
            $tablica_dan = pobierz_dania ('6');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal7') {
            $message_to_reply = 'Lokal 7 - Hi Heng';
            $tablica_dan = pobierz_dania ('7');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal8') {
            $message_to_reply = 'Lokal 8 - Hemp Peng';
            $tablica_dan = pobierz_dania ('8');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal9') {
            $message_to_reply = 'Lokal 9 - Restauracja Polonia';
            $tablica_dan = pobierz_dania ('9');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        } else if ($payload == 'lokal11') {
            $message_to_reply = 'Lokal 11 - Restauracja Snack';
            $tablica_dan = pobierz_dania ('11');
            $ilosc_dan = count($tablica_dan);
            
            $message_to_reply = null;
            
            $message_to_reply = wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan);
            
            usun_tryb($sender);
            przypisz_tryb ($sender, 'wybor_dania-'.$payload);
        }
        
        
        // Zamowienia
        if ($payload == 'order_keb1' || $payload == 'order_keb2') {
            $message_to_reply = 'Chcesz odebrac osobiscie czy zamawiasz dowoz?';
            $przycisk_pz = array("Odbiorę sam", "osobiscie", "Biorę dowóz", "dowoz");
        }
        
        // Dostawa
        if ($quick_payload == 'dowoz') {
            $message_to_reply = 'Wybrałeś opcję z dowozem.\nPodaj swoje dane (imię i nazwisko, numer telefonu oraz adres).\nJeśli podasz zły numer telefonu, zamówienie nie zostanie zrealizowane.';
            usun_tryb($sender);
            sleep(1);
            przypisz_tryb($sender, 'dowoz');
        }
        if ($quick_payload == 'osobiscie') {
            $message_to_reply = 'Podaj swoje dane (imię i nazwisko, numer telefonu oraz adres).\nJeśli podasz zły numer telefonu, zamówienie nie zostanie zrealizowane.';
            usun_tryb($sender);
            sleep(1);
            przypisz_tryb($sender, 'osobiscie');
        }
    }
    
    if ($tryb == 'dowoz' || $tryb == 'osobiscie') {
        $message_to_reply = 'Złożyłeś zamówienie na dane:\n'.$message;
        wysylanie_tekst ($access_token, $sender, $message_to_reply, $input, $przycisk, $przycisk_url, $przycisk_sub, $przycisk_pz, $generic);
        $message_to_reply = 'Podane przez Ciebie dane zostaną wysłane do lokalu wraz ze szczegółami zamówienia. Jeśli podane przez Ciebie dane nie są prawidłowe, zamówienie nie zostanie zrealizowane. ';
        $przycisk = array("Zrealizuj!", "zrealizuj");
    }
    
    // preg_match zwracal $tryb jako '1'
    $tryb_p = explode("-", $tryb);
    if ($tryb_p[0] == 'wybor_dania') {
        
        if (is_numeric($message)) {
            $dania = json_decode(file_get_contents("./dania/".$tryb_p[1].".json"));
            $ilosc = count($dania[0]);
            
            if($message > $ilosc || $message < 1) {
                $message_to_reply = 'Podałeś niepoprawny numerek dania!';
            } else {
                $message_to_reply = 'Zamawiasz: '.$dania[2][$message-1].' za '.$dania[3][$message-1].' PLN.\nChcesz odebrać osobiście czy wolisz opcję z dowozem?';
                $przycisk_pz = array("Osobiście", "osobiscie", "Dowóz", "dowoz");
                usun_tryb($sender);
                przypisz_tryb($sender, 'gastronomia');
            }
            
        } else {
            $message_to_reply = 'Podaj numer dania, które wybierasz.';
        }
        
    }
    
wysylanie:

if(isset($wiadomosc_zwrotna_json)) {
    wysylanie_json ($access_token, $sender, $wiadomosc_zwrotna_json, $input);
} else {
    wysylanie_tekst ($access_token, $sender, $message_to_reply, $input, $przycisk, $przycisk_url, $przycisk_sub, $przycisk_pz, $generic);
}

function wiadomosc_lista_dan ($payload, $tablica_dan, $ilosc_dan) {
    //czysci plik
    file_put_contents("./dania/".$payload.".json", "");
    
    $lista_dan = array(
        array(),
        array(),
        array(),
        array()
        );
        
    // dodawanie do tablicy
    for ($x=1;$x<=$ilosc_dan;$x++){
        
        array_push($lista_dan[0], $x);
        array_push($lista_dan[1], $tablica_dan[$x-1]['id']);
        array_push($lista_dan[2], $tablica_dan[$x-1]['nazwa']);
        array_push($lista_dan[3], $tablica_dan[$x-1]['cena']);
        // $lista_dan = array("id"=>$x, "nazwa"=>$tablica_dan[$x]['nazwa']);
        
        //wiadomosc
        $message_to_reply = $message_to_reply . $lista_dan[0][$x-1] . '. '.$lista_dan[2][$x-1] . ' - '.$lista_dan[3][$x-1] . ' PLN\n';
        
    }
    file_put_contents("./dania/".$payload.".json", json_encode($lista_dan));
    
    // wiadomosc
    // for ($x=0;$x<=$ilosc_dan-1;$x++){
    //     //$message_to_reply = $message_to_reply . $lista_dan[0][$x].'. '.$lista_dan[2][$x].'\n';
    // }
    return $message_to_reply;
}

function pobierz_dania ($lokal) {
    include 'config.php';
    
    // Tworzenie połączenia
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn, "utf8");
    
    // Sprawdzenie
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }
    
    $query = 'SELECT * FROM dania WHERE lokal = "'.$lokal.'" ;';
    $result = mysqli_query($conn, $query);
    
    $dania = array();
    
    if($result->num_rows > 0) {

        while($row = mysqli_fetch_assoc($result)) {
            // print_r($row);
            // echo '<br>';
            
            array_push($dania, $row);
        }
    }
    
    // echo '<b>Tablica cala</b><br>';
    // print_r($dania);
    $conn->close();
    return $dania;
}


function dopasuj_lokale($sender, $kategoria) {
    include 'config.php';
    
    // Tworzenie połączenia
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn, "utf8");
    
    // Sprawdzenie
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }
    
    $query = 'SELECT * FROM lokale WHERE kategoria = "'.$kategoria.'" ;';
    $result = mysqli_query($conn, $query);
    
    $lokale = array();
    
    if($result->num_rows > 0) {

        while($row = mysqli_fetch_assoc($result)) {
            // print_r($row);
            // echo '<br>';
            
            array_push($lokale, $row);
        }
    }
    
    // echo '<b>Tablica cala</b><br>';
    // print_r($lokale);
    $conn->close();
    return $lokale;
}

// print_r($lista_lokali);


function konwersja_na_json ($lokale, $sender) {
    
    // Generic template
    $ilosc_lokali = count($lokale);
    
    if ($ilosc_lokali == 3) {
        
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
                        "title":"'.$lokale[0]['nazwa'].'",
                        "image_url":"'.$lokale[0]['zdjecie'].'",
                        "subtitle":"Godziny otwarcia: '.$lokale[0]['godziny_otwarcia'].'. Numer telefonu: '.$lokale[0]['numer_telefonu'].'",
                        "buttons":[
                          {
                            "type":"postback",
                            "title":"Zamawiam tutaj",
                            "payload":"lokal'.$lokale[0]['id'].'"
                          }              
                        ]
                      },
                      {
                        "title":"'.$lokale[1]['nazwa'].'",
                        "image_url":"'.$lokale[1]['zdjecie'].'",
                        "subtitle":"Godziny otwarcia: '.$lokale[1]['godziny_otwarcia'].'. Numer telefonu: '.$lokale[1]['numer_telefonu'].'",
                        "buttons":[
                          {
                            "type":"postback",
                            "title":"Zamawiam tutaj",
                            "payload":"lokal'.$lokale[1]['id'].'"
                          }              
                        ]
                      },
                      {
                        "title":"'.$lokale[2]['nazwa'].'",
                        "image_url":"'.$lokale[2]['zdjecie'].'",
                        "subtitle":"Godziny otwarcia: '.$lokale[2]['godziny_otwarcia'].'. Numer telefonu: '.$lokale[2]['numer_telefonu'].'",
                        "buttons":[
                          {
                            "type":"postback",
                            "title":"Zamawiam tutaj",
                            "payload":"lokal'.$lokale[2]['id'].'"
                          }              
                        ]
                      }
                    ]
                  }
                }
              }
            }';
    }
    return $jsonData;
}

function usun_tryb ($sender) {
    include ('config.php');
    
    // Tworzenie połączenia
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn, "utf8");
    
    // Sprawdzenie
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    } 
    //echo "Połączono pomyślnie";
    
    // Dodawanie rekordu
    $sql = "DELETE FROM tryby WHERE senderID = ".$sender." ;";
    
    if ($conn->query($sql) === TRUE) {
        echo "Pomyślnie usunieto";
        
    } else {
        echo 'Wystapil blad';
    }
    
    $conn->close();
}

function przypisz_tryb ($sender, $tryb) {
    include ('config.php');
    
    // Tworzenie połączenia
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn, "utf8");
    
    // Sprawdzenie
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    } 
    //echo "Połączono pomyślnie";
    
    // Dodawanie rekordu
    $sql = "INSERT INTO tryby (senderID, tryb)
    VALUES ('".$sender."', '".$tryb."')";
    
    if ($conn->query($sql) === TRUE) {
        // echo "Pomyslnie";
    } else {
        // echo 'Wystąpił błąd.';
    }
    
    $conn->close();
}
?>