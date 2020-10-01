<?php
{
    class whatsAppBot{
    // Especifica la instancia de las variables URL y Token
    var $APIurl = 'https://api.chat-api.com/instanceYYYYY/';
    var $token = '**************************';

    public function __construct(){
    //Envia el JSON con la instancia
    $json = file_get_contents('php://input');
    $decoded = json_decode($json,true);

    ob_start();
    var_dump($decoded);
    $input = ob_get_contents();
    ob_end_clean();
    file_put_contents('input_requests.log',$input.PHP_EOL,FILE_APPEND);

    if(isset($decoded['messages'])){
    //Chequea cada nuevo mensaje
    foreach($decoded['messages'] as $message){
    // elimina los espacios sobrantes y divide el mensaje en espacios. La primera palabra del mensaje es un comando, las otras palabras son parámetros
    $text = explode(' ',trim($message['body']));
    // el mensaje actual no debe enviarse desde su bot, porque llama a la recursividad
    if(!$message['fromMe']){
    switch(mb_strtolower($text[0],'UTF-8')){
        // verifica qué comando contiene la primera palabra y llama a la función
    case 'hi':  {$this->welcome($message['chatId'],false); break;}
        case 'chatId': {$this->showchatId($message['chatId']); break;}
        case 'time':   {$this->time($message['chatId']); break;}
        case 'me':     {$this->me($message['chatId'],$message['senderName']); break;}
        case 'file':   {$this->file($message['chatId'],$text[1]); break;}
        case 'ptt':     {$this->ptt($message['chatId']); break;}
        case 'geo':    {$this->geo($message['chatId']); break;}
        case 'group':  {$this->group($message['author']); break;}
        default:        {$this->welcome($message['chatId'],true); break;}
        }}}}}

  // esta función llama a la función sendRequest para enviar un mensaje simple
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    // @ param $ text [string] [required] - texto del mensaje
    public function welcome($chatId, $noWelcome = false){
    $welcomeString = ($noWelcome) ? "Comando Incorrecto\n" : "WhatsApp Demo Bot PHP\n";
    $this->sendMessage($chatId,
    $welcomeString.
    "Commands:\n".
    "1. chatId -Muestra el ID del chat\n".
    "2. time - Muestra la hora del servidor\n".
    "3. me - Muestra tú nombre de Usuario\n".
    "4. file [format] - envia un archivo. Los formatos disponibles son: doc/gif/jpg/png/pdf/mp3/mp4\n".
    "5. ptt - Envía un msj de voz de ejemplo\n".
    "6. geo - Envía una localización\n".
    "7. group - Crea un grupo con el bot"
    );
    }
// envía Id del chat actual. se llama cuando el bot obtiene el comando "chatId"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    public function showchatId($chatId){
    $this->sendMessage($chatId,'chatId: '.$chatId);
    }

// envía la hora actual del servidor. se llama cuando el bot recibe el comando "tiempo"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    public function time($chatId){
    $this->sendMessage($chatId,date('d.m.Y H:i:s'));
    }

// envía su apodo. se llama cuando el bot recibe el comando "yo"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    // @ param $ name [string] [required] - la propiedad "senderName" del mensaje
    public function me($chatId,$name){
    $this->sendMessage($chatId,$name);
    }
 // envía un archivo. se llama cuando el bot obtiene el comando "archivo"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    // @ param $ formato [cadena] [obligatorio] - formato de archivo, de los parámetros en el cuerpo del mensaje (texto [1], etc.)
    public function file($chatId,$format){
    $availableFiles = array(
    'doc' => 'document.doc',
    'gif' => 'gifka.gif',
    'jpg' => 'jpgfile.jpg',
    'png' => 'pngfile.png',
    'pdf' => 'presentation.pdf',
    'mp4' => 'video.mp4',
    'mp3' => 'mp3file.mp3'
    );

    if(isset($availableFiles[$format])){
    $data = array(
    'chatId'=>$chatId,
    'body'=>'https://domain.com/PHP/'.$availableFiles[$format],
    'filename'=>$availableFiles[$format],
    'caption'=>'Get your file '.$availableFiles[$format]
    );
    $this->sendRequest('sendFile',$data);}}

// envía un mensaje de voz. se llama cuando el bot recibe el comando "ptt"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    public function ptt($chatId){
    $data = array(
    'audio'=>'https://domain.com/PHP/ptt.ogg',
    'chatId'=>$chatId
    );
    $this->sendRequest('sendAudio',$data);}

 
    // envía una ubicación. se llama cuando el bot recibe el comando "geo"
    // @ param $ chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    public function geo($chatId){
    $data = array(
    'lat'=>51.51916,
    'lng'=>-0.139214,
    'address'=>'Ваш адрес',
    'chatId'=>$chatId
    );
    $this->sendRequest('sendLocation',$data);}

    
    // crea un grupo. se llama cuando el bot recibe el comando "grupo"
    // @ param chatId [cadena] [obligatorio]: el ID del chat al que enviamos un mensaje
    // @ param autor [cadena] [obligatorio] - propiedad "autor" del mensaje
    public function group($author){
    $phone = str_replace('@c.us','',$author);
    $data = array(
    'groupName'=>'Group with the bot PHP',
    'phones'=>array($phone),
    'messageText'=>'It is your group. Enjoy'
    );
    $this->sendRequest('group',$data);}

    public function sendMessage($chatId, $text){
    $data = array('chatId'=>$chatId,'body'=>$text);
    $this->sendRequest('message',$data);}

    public function sendRequest($method,$data){
    $url = $this->APIurl.$method.'?token='.$this->token;
    if(is_array($data)){ $data = json_encode($data);}
    $options = stream_context_create(['http' => [
    'method'  => 'POST',
    'header'  => 'Content-type: application/json',
    'content' => $data]]);
    $response = file_get_contents($url,false,$options);
    file_put_contents('requests.log',$response.PHP_EOL,FILE_APPEND);}}
// ejecuta la clase cuando la instancia solicita este archivo
    new whatsAppBot();}
?>
