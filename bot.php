<?php
header('Content-Type: text/html; charset=utf-8');

include ('config.php'); // WAZNE
include ('wysylanie.php');
//include ('konf_curl.php');


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
var_dump($row);

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
        $message_to_reply = 'Co masz ochotę zjeść? Może kebaba?';
        if (preg_match('[kebab]', strtolower($message)) || preg_match('[kebaba]', strtolower($message))) {
            $message_to_reply = 'Oto lista lokali serwujących wykwintne kebabosy w Nowej Soli!\nWybierz coś dla siebie!';
            $generic = 'lista_kebabow';
        } 
    } else if (preg_match('[nowe]', strtolower($message))) {
        usun_tryb($sender);
        $message_to_reply = 'Możesz złożyć nowe zamówienie';
        $tryb = null;
    } else {
        // $message_to_reply = $message;
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
            $message_to_reply = 'Oto lista lokali serwujących wykwintne kebabosy w Nowej Soli!\nWybierz coś dla siebie!';
            $generic = 'lista_kebabow';
        } 
        if (preg_match('[menu_keb1]', strtolower($payload))) {
            $generic = 'kebab1';
        } else if (preg_match('[menu_keb2]', strtolower($payload))) {
            $generic = 'kebab2';
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
        $message_to_reply = 'Złożyłeś zamówienie na dane:\n'.$message.'\nDo zapłaty: 10 PLN';
        wysylanie_tekst ($access_token, $sender, $message_to_reply, $input, $przycisk, $przycisk_url, $przycisk_sub, $przycisk_pz, $generic);
        $message_to_reply = 'Podane przez Ciebie dane zostaną wysłane do lokalu wraz ze szczegółami zamówienia. Jeśli podane przez Ciebie dane nie są prawidłowe, zamówienie nie zostanie zrealizowane. ';
        $przycisk = array("Zrealizuj!", "zrealizuj");
    }
    
wysylanie:

wysylanie_tekst ($access_token, $sender, $message_to_reply, $input, $przycisk, $przycisk_url, $przycisk_sub, $przycisk_pz, $generic);

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
        echo "Pomyslnie";
    } else {
        echo 'Wystąpił błąd.';
    }
    
    $conn->close();
}
?>