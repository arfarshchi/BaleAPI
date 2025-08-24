<?php
/**
 * Bale.ai Bot V1 Class .
 *
 * @farshchi Amirreza Farshchi <arfiran@gmail.com>
 * first editation: 2023/10/20
 */

#region Metods
class User{
    public $id;
    public $username;
    public $first_name;
    //public $last_name;
    public $is_bot;
    //public $Data;
    /** @var mixed|null|Balebot $Bot  */
    private $Bot;

    public function __construct($user, $Bot=Null)
    {
        $this->id=$user['id'];
        $this->username=$user['username'];
        $this->first_name=$user['first_name'];
        //$this->last_name=$user['id'];
        $this->is_bot=$user['is_bot'];
        /*$this->Data=array(
            'Id' => $this->id,
            'Un' => $this->username,
            'Fn' => $this->first_name,
            'Ib' => $this->is_bot
        );*/

        $this->Bot=$Bot;
    }

    public function status($chat_id)
    {
        if ($this->Bot) {
            return $this->Bot->getChatMember($chat_id, $this->id)->status;
        }else{return Null;}
    }

    public function invite ($chat_id)
    {
        if ($this->Bot) {
            return $this->Bot->inviteUser($chat_id,$this->id);
        }else{return Null;}
    }

    public function setBot (Balebot $bot) {
        $this->Bot=$bot;
    }
}

class Chat{
    public $id;
    public $type;
    public $title;
    public $username;
    public $first_name;
    //public $last_name;
    public $photo;
    public $link;
    //public $Data;
    /** @var mixed|null|Balebot $Bot  */
    private $Bot;

    public function __construct($chat, $Bot=Null)
    {
        $this->id=$chat['id'];
        $this->type=$chat['type'];
        $this->title=$chat['title'];
        $this->username=$chat['username'];
        $this->first_name=$chat['first_name'];
        $this->link=$chat['invite_link'];
        //$this->last_name=$chat['id'];
        $this->photo=array(
            'small' => $chat['photo']['small_file_id'],
            'big' => $chat['photo']['big_file_id']
        ) ;
        /*$this->Data=array(
            'Id' => $this->id,
            'Ty' => $this->type,
            'Ti' => $this->title,
            'Un' => $this->username,
            'Fn' => $this->first_name,
            'Photo' => $this->photo,
            'Link' => $this->link
        );*/

        $this->Bot=$Bot;
    }

    public function admins()
    {
        if ($this->Bot) {
            return $this->Bot->getChatAdmins($this->id);
        }else{return Null;}
    }

    public function memberCount ()
    {
        if ($this->Bot) {
            return $this->Bot->getChatMembersCount($this->id);
        }else{return Null;}
    }

    public function leave ()
    {
        if ($this->Bot) {
            return $this->Bot->leaveChat($this->id);
        }else{return Null;}
    }

    public function getChatByUsername ($username=null)
    {
        if (!$username){
            $username=$this->username;
        }else{
            $username=str_replace("@","",$username);
            $this->username=$username;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ble.ir/".$username,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        preg_match('/({.*})/', $response, $matches);
        $jsonInfo = $matches[0];
        $infoArray = json_decode($jsonInfo, true);

        if ($infoArray['props']['pageProps']['peer']['type']==1) {
            $this->id = $infoArray['props']['pageProps']['peer']['id'];
            $this->first_name = $infoArray['props']['pageProps']['user']['title'];
            $this->type='private';
        }else{
            $this->id = $infoArray['props']['pageProps']['peer']['id'];
            $this->title = $infoArray['props']['pageProps']['group']['title'];
            $this->type='channel';
        }
        return $this;
    }


    public function setBot (Balebot $bot) {
        $this->Bot=$bot;
    }
}

class ChatMember extends User{
    public $status;

    public $permissions;
    public function __construct($user, $Bot=Null)
    {
        parent::__construct($user['user'],$Bot);

        $this->status=$user["status"];
        //$this->Data['S']=$this->status;

        unset($user['user']);
        unset($user['status']);
        $this->permissions=$user;
    }

    public function can($permission)
    {
        if ($this->status=="creator"){
            return true;
        }else{
            $permission="can_".$permission;
            return $this->permissions[$permission];
        }


        /* Members:
         * can_be_edited": true,
         * can_change_info": true,
         * can_post_messages": true,
         * can_edit_messages": true,
         * can_delete_messages": true,
         * can_invite_users": true,
         * can_restrict_members": true,
         * can_pin_messages": true,
         * can_promote_members": true,
         * can_send_messages": true,
         * can_send_media_messages": true,
         * can_send_other_messages": true,
         * can_add_web_page_previews": true
         */

        /* Admins:
         * can_be_edited: true
            is_anonymous: true
            can_manage_chat: true
            can_delete_messages: true
            can_manage_video_chats: true
            can_restrict_members: true
            can_promote_members: true
            can_change_info: true
            can_invite_users: true
            can_pin_messages: true
            can_manage_topics: true
         */
    }
}

class File{
    public $id;
    public $type;
    public $size;
    public $name;
    public $title;
    public $mime_type;
    public $W;
    public $H;
    public $duration;
    public $path;
    //public $Data;
    /** @var mixed|null|Balebot $Bot  */
    private $Bot;

    public function __construct($document,$type, $Bot=Null)
    {
        $this->id=$document['file_id'];
        $this->size=$document['file_size'];
        /*$this->Data=array(
            'Id' => $this->id,
            'FS' => $this->size,
        );*/
        $this->newFile($document,$type);

        $this->Bot=$Bot;
    }

    public function newFile($document,$type)
    {
        $this->type=$type;//$this->Data['Type']=$this->type;
        if (array_key_exists('file_name',$document) and !is_null($document['file_name'])) {
            $this->name = $document['file_name'];
            //$this->Data['FN'] = $this->name;
        }
        if (array_key_exists('mime_type',$document) and !is_null($document['mime_type'])) {
            $this->mime_type = $document['mime_type'];
            //$this->Data['FMT'] = $this->mime_type;
        }
        if (array_key_exists('title',$document) and !is_null($document['title'])){
            $this->title=$document['title'];
            //$this->Data['FT']=$this->title;
        }
        if (array_key_exists('duration',$document) and !is_null($document['duration'])) {
            $this->duration = $document['duration'];
            //$this->Data['FD'] = $this->duration;
        }
        if (array_key_exists('width',$document) and !is_null($document['width'])) {
            $this->W = $document['width'];
            //$this->Data['FW'] = $this->W;
        }
        if (array_key_exists('height',$document) and !is_null($document['height'])){
            $this->H=$document['height'];
            //$this->Data['FH']=$this->H;
        }
    }
    public function filePath ()
    {
        if ($this->Bot) {
            $arr = array('file_id' => $this->id);
            $result = json_decode($this->Bot->sendrequest('getFile', $arr), true);
            if ($result !== null) {
                if ($result['ok']) {
                    $this->path = $result['result']['file_path'];
                    $return = $this->path;
                } else {
                    $this->error = array(
                        'Code' => $result['error_code'],
                        'Description' => $result['description']
                    );
                    $return = $this->error;
                }
            }
            return $return;
        }else{return Null;}
    }

    public function setBot (Balebot $bot) {
        $this->Bot=$bot;
    }
}

class Contact{
    public $phone;
    public $first_name;
    //public $last_name;
    public $id;
    //public $Data;

    public function __construct($contact)
    {
        $this->phone=$contact['phone_number'];
        $this->first_name=$contact['first_name'];
        //$this->last_name=$contact['last_name'];
        $this->id=$contact['user_id'];

        /*$this->Data=array(
            'Ph' => $this->phone,
            'Fn' => $this->first_name,
            //'Ln' => $this->last_name,
            'Id' => $this->id,
        );*/
    }
}

class Location{
    public $tool;
    public $arz;
    //public $Data;

    public function __construct($location)
    {
        $this->tool=$location['longitude'];
        $this->arz=$location['latitude'];

        /*$this->Data=array(
            'Tool' => $this->tool,
            'Arz' => $this->arz,
        );*/
    }
}

class Invoice{
    public $title;
    public $description;
    public $start_parameter;
    public $currency;
    public $total_amount;
    public $shenase;
    //public $Data;

    public function __construct($location)
    {
        $this->title=$location['title'];
        $this->description=$location['description'];
        $this->start_parameter=$location['start_parameter'];
        $this->currency=$location['currency'];
        $this->total_amount=$location['total_amount'];
        $this->shenase=$location['invoice_payload'];

        /*$this->Data=array(
            'Ti' => $this->title,
            'De' => $this->description,
            'Sp' => $this->start_parameter,
            'Vahed' => $this->currency,
            'Pr' => $this->total_amount,
            'Sh' => $this->shenase,
        );*/
    }
}

class Message{
    public $id;
    /** @var User $user */
    public $user;
    /** @var Chat $chat */
    public $chat;
    public $date;
    /** @var User $forward_from */
    public $forward_from;
    /** @var Chat $forward_chat */
    public $forward_chat;
    public $forward_id;
    /** @var Message $reply_message */
    public $reply_message;
    public $text;
    /** @var File $file */
    public $file;
    public $caption;
    /** @var Contact $contact */
    public $contact;
    /** @var Location $location */
    public $location;
    /** @var Invoice $invoice */
    public $invoice;
    /** @var User $new_member */
    public $new_member;
    /** @var User $left_member */
    public $left_member;
    public $type;
    //public $Data;
    /** @var mixed|null|Balebot $Bot  */
    private $Bot;

    public function __construct($message, $Bot=Null)
    {
        $this->id=$message['message_id'];
        $this->user=new User($message['from'], $Bot);
        $this->chat=new Chat($message['chat'], $Bot);
        $this->date=array(
            'Date' => $message['date'],
            'FDate' => $message['forward_date'],
            'EDate' => $message['edit_date'],
        );
        $this->reply_message=$message['reply_to_message'];
        $this->text=$message['text'];
        $this->caption=$message['caption'];
        if (array_key_exists('forward_from',$message)) {
            $this->forward_from = new User($message['forward_from'], $Bot);
            //$this->Data['FU'] = $this->forward_from->Data;
        }
        if (array_key_exists('forward_from_chat',$message)) {
            $this->forward_chat = new Chat($message['forward_from_chat'], $Bot);
            //$this->Data['FC'] = $this->forward_chat->Data;
        }
        if (array_key_exists('forward_from_message_id',$message)) {
            $this->forward_id = $message['forward_from_message_id'];
            //$this->Data['FId'] = $this->forward_id->Data;
        }

        if (array_key_exists('photo',$message)){
            /*foreach ($message['photo'] as $item => $value){
                $this->file[$item]=new File($value,'photo', $Bot);
                //$this->Data['F'][$item] = $this->file[$item]->Data;
            }*/
            $this->file=new File($message['photo'][0],'photo', $Bot);
            //$this->Data['F'][$item] = $this->file[$item]->Data;
            $this->type='photo';
        }else if (array_key_exists('video',$message)){
            $this->file=new File($message['video'],'video', $Bot);
            //$this->Data['F'] = $this->file->Data;
            $this->type='video';
        }else if (array_key_exists('voice',$message)){
            $this->file=new File($message['voice'],'voice', $Bot);
            //$this->Data['F'] = $this->file->Data;
            $this->type='voice';
        }else if (array_key_exists('audio',$message)){
            $this->file=new File($message['audio'],'audio', $Bot);
            //$this->Data['F'] = $this->file->Data;
            $this->type='audio';
        }else if (array_key_exists('animation',$message)){
            $this->file=new File($message['animation'],'animation', $Bot);
            //$this->Data['F'] = $this->file->Data;
            $this->type='animation';
        }else if (array_key_exists('sticker',$message)){
            $this->file=new File($message['sticker'],'sticker', $Bot);
            //$this->Data['F'] = $this->file->Data;
            $this->type='animation';
        }else if (array_key_exists('contact',$message)){
            $this->contact=new Contact($message['contact']);
            //$this->Data['Co'] = $this->contact->Data;
            $this->type='contact';
        }else if (array_key_exists('location',$message)){
            $this->location=new Location($message['location']);
            //$this->Data['Lo'] = $this->location->Data;
            $this->type='location';
        }else if (array_key_exists('invoice',$message)){
            $this->invoice=new Invoice($message['invoice']);
            //$this->Data['Iv'] = $this->invoice->Data;
            $this->type='invoice';
        }else if (array_key_exists('successful_payment',$message)){
            $this->invoice=new Invoice($message['successful_payment']);
            //$this->Data['Lm'] = $this->invoice->Data;
            $this->type='successful_payment';
        }else if (array_key_exists('new_chat_members',$message)){
            foreach ($message['new_chat_members'] as $item => $value){
                $this->new_member[$item]=new User($value, $Bot);
                //$this->Data['Nm'][$item] = $this->new_member[$item]->Data;
            }
            $this->type='new_chat_members';
        }else if (array_key_exists('left_chat_member',$message)){
            $this->left_member=new User($message['left_chat_member'], $Bot);
            //$this->Data['Lm'] = $this->left_member->Data;
            $this->type='left_chat_member';
        }else{
            $this->type='text';
        }

        if (array_key_exists('document',$message)){
            if (!is_null($this->file)){
                $this->file->newFile($message['document'],$this->file->type);
            }else{
                $this->file=new File($message['document'],'document', $Bot);
                $this->type='document';
            }
            //$this->Data['F'] = $this->file->Data;
        }



        /*$this->Data=array_merge($this->Data,[
            'U' => $this->user->Data,
            'C' => $this->chat->Data,
            'D' => $this->date,
            'T' => $this->text,
            'TCa' => $this->caption,
            'Id' => $this->id,
            'Type' => $this->type,
        ]);*/

        $this->Bot=$Bot;
    }

    private function setReplyMessage()
    {
        if (!is_null($this->reply_message)) {
            $this->reply_message = new Message($this->reply_message,$this->Bot);
        }
    }

    public function getReplyMessage()
    {
        $this->setReplyMessage();
        return $this->reply_message;
    }

    public function userChatMember()
    {
        if ($this->Bot) {
            return $this->Bot->getChatMember($this->chat->id,$this->user->id);
        }else{return Null;}
    }

    public function setBot (Balebot $bot) {
        $this->Bot=$bot;
    }
}

class CallbackQuery{
    public $id;
    /** @var User $user */
    public $user;
    /** @var Message $message */
    public $message;
    //public $Data;
    public $data;
    public $chat_instance;
    //public $Data;
    /** @var mixed|null|Balebot $Bot  */
    private $Bot;

    public function __construct($callback, $Bot=Null)
    {
        $this->id=$callback['id'];
        $this->user=new User($callback['from'], $Bot);
        $this->message=new Message($callback['message'], $Bot);
        $this->data=$callback['data'];
        $this->chat_instance=$callback['chat_instance'];

        /*$this->Data=array(
            'Id' => $this->id,
            'U' => $this->user->Data,
            'M' => $this->message->Data,
            'TCb' => $this->data,
        );*/

        $this->Bot=$Bot;
    }

    public function setBot (Balebot $bot) {
        $this->Bot=$bot;
    }
}

class Edited extends Message {
    function __construct($message, $Bot=Null)
    {
        parent::__construct($message, $Bot);
    }
}
#endregion

class Balebot{
    public $token;
    public $updat_id;
    public $updat_type;
    /** @var Message $message */
    public $message;
    /** @var Message $messageAll */
    public $messageAll;
    /** @var CallbackQuery $callbackQuery */
    public $callbackQuery;
    /** @var Edited $edited */
    public $edited;
    //public $Data;
    public $apiurl;
    public $downloadApiurl;
    public $error;

    public function __construct($token)
    {
        $this->token=$token;
        //$this->Data['token']=$token;
        $this->apiurl="https://tapi.bale.ai/bot".$token;
        $this->downloadApiurl='https://tapi.bale.ai/file/bot'.$token;
    }

    //***********************************(Updates)***************************************
    public function getUpdate($Update_id=Null)
    {
        /**
         * Update_id
         * -1 to -n:آپدیت های قبلی
         * 0: آپدیت های فعلی
         * 1: آپدیت های تایید نشده
         * Null: وب‌هووک
         */
        if ($Update_id)
        {
            $rawData = file_get_contents($this->apiurl.'/getupdates?offset='.$Update_id.'&limit=1');
            $rawData =json_decode($rawData, true);
            if ($rawData['ok']) {
                if (!is_null($rawData['result'])) {
                    return $this->setUpdate($rawData['result'][0]);
                }else{
                    return null;
                }
            } else {
                $this->error = array(
                    'Code' =>$rawData['error_code'],
                    'Description' => $rawData['description']
                );
                return $this->error;
            }

        }

        else
        {
            $rawData = file_get_contents('php://input');
            $rawData = json_decode($rawData, true);
            return $this->setUpdate($rawData);
        }
    }
    private function setUpdate ($update)
    {
        $this->updat_id=$update['update_id'];
        //$this->Data['upId'] = $this->updat_id;
        if (array_key_exists('message',$update)) {
            $this->message=new Message($update['message'],$this);
            $this->messageAll=$this->message;
            $this->updat_type="M";
            //$this->Data['M'] = $this->message->Data;
            return $this->message;
        }else if (array_key_exists('callback_query',$update)) {
            $this->callbackQuery=new CallbackQuery($update['callback_query'],$this);
            $this->messageAll=$this->callbackQuery->message;
            $this->updat_type="Cb";
            //$this->Data['Cb'] = $this->callbackQuery->Data;
            return $this->callbackQuery;
        }else if (array_key_exists('edited_message',$update)) {
            $this->edited=new Edited($update['edited_message'],$this);
            $this->messageAll=$this->edited;
            $this->updat_type="E";
            //$this->Data['E'] = $this->edited->Data;
            return $this->edited;
        }else{
            return null;
        }
    }
    public function setWebhook($url)
    {
        $rawData = file_get_contents($this->apiurl.'/setwebhook?url='.$url);
        $rawData =json_decode($rawData, true);
        if ($rawData['ok']) {
            return $rawData['result'];
        }else{
            $this->error = array(
                'Code' =>$rawData['error_code'],
                'Description' => $rawData['description']
            );
            return $this->error;
        }

    }
    public function deleteWebhook()
    {
        $rawData = file_get_contents($this->apiurl.'/deletewebhook');
        $rawData =json_decode($rawData, true);
        if ($rawData['ok']) {
            return $rawData['result'];
        }else{
            $this->error = array(
                'Code' =>$rawData['error_code'],
                'Description' => $rawData['description']
            );
            return $this->error;
        }

    }
    public function getWebhookInfo()
    {
        $rawData = file_get_contents($this->apiurl.'/getwebhookinfo');
        $rawData =json_decode($rawData, true);
        if ($rawData['ok']) {
            return array($rawData['result']['url'],$rawData['result']['pending_update_count']);
        }else{
            $this->error = array(
                'Code' =>$rawData['error_code'],
                'Description' => $rawData['description']
            );
            return $this->error;
        }

    }

    //***************************************(Chat User)*******************************
    public function getMe(){
        $result=json_decode($this->sendrequest('getMe',""),true);
        //$result=$this->sendrequest('getMe',"");
        //return $result;
        if ($result['ok']){
            $return=new User($result['result'],$this);
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function getChat($chat_id){
        $arr = array("chat_id" => $chat_id);
        $result=json_decode($this->sendrequest('getChat',$arr),true);
        //$result=$this->sendrequest('getChat',$arr);
        //return $result;
        if ($result['ok']){
            $return=new Chat($result['result'],$this);
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function getChatMember($chat_id,$user_id){
        $arr = array("chat_id" => $chat_id, 'user_id' => $user_id);
        $result=json_decode($this->sendrequest('getChatMember',$arr),true);
        //$result=$this->sendrequest('getChatMember',$arr);
        //return $result;
        if ($result['ok']){
            $return=new ChatMember($result['result'],$this);
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function getChatAdmins($chat_id){
        $arr = array("chat_id" => $chat_id);
        $result=json_decode($this->sendrequest('GetChatAdministrators',$arr),true);
        //$result=$this->sendrequest('GetChatAdministrators',$arr);
        //return $result;
        if ($result['ok']){
            foreach ($result['result'] as $item => $value){
                $return[$item]=new ChatMember($value,$this);
            }
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function getChatMembersCount($chat_id){
        $arr = array("chat_id" => $chat_id);
        $result=json_decode($this->sendrequest('getChatMembersCount',$arr),true);
        //$result=$this->sendrequest('getChat',$arr);
        //return $result;
        if ($result['ok']){
            $return=$result['result'];
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function inviteUser($chat_id,$user_id){
        $arr = array("chat_id" => $chat_id, 'user_id' => $user_id);
        $result=json_decode($this->sendrequest('inviteUser',$arr),true);
        //$result=$this->sendrequest('inviteUser',$arr);
        //return $result;
        if ($result['ok']){
            $return=$result['result'];
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function leaveChat($chat_id){
        $arr = array("chat_id" => $chat_id);
        $result=json_decode($this->sendrequest('leaveChat',$arr),true);
        //$result=$this->sendrequest('getChat',$arr);
        //return $result;
        if ($result['ok']){
            $return=$result['result'];
        }else{
            $this->error = array(
                'Code' =>$result['error_code'],
                'Description' => $result['description']
            );
            $return=$this->error;
        }
        return $return;
    }
    public function promoteChatMember(){

    }

    //******************************************(Keyboard)**********************************************
    public function sansur($keyb,$words){
        foreach ($words as $word){
            $key=array_search($word,$keyb);
            if ($key !== false) {unset($keyb[$key]); unset($keyb[$key+1]); $keyb=array_slice($keyb,0);}
        }
        return $keyb;
    }

    private function buildKeyBoard2($key){
        $tedad=count($key);

        for ($i=0;$i< $tedad;$i++){
            if (is_array($key[$i])){
                for ($j=0;$j<=count($key[$i])-1;$j++){
                    $option2[] = $this->buildKeyboardButton($key[$i][$j]);
                }
                $option[$i] = $option2;
                $option2=array();
            }else{
                $option[$i] = array($this->buildKeyboardButton($key[$i]));
            }
        }
        return $this->buildKeyBoard($option,false,true);
    }
    public function buildKeyboardButton($text, $request_contact = false, $request_location = false)
    {
        $replyMarkup = [
            'text'             => $text,
            'request_contact'  => $request_contact,
            'request_location' => $request_location,

        ];
        //$replyMarkup = json_encode($replyMarkup, true);
        return $replyMarkup;
    }
    public function buildKeyBoard(array $options, $onetime = false, $resize = false, $selective = true)
    {
        $replyMarkup = [
            'keyboard'          => $options,
            'resize_keyboard'   => $resize,
            'one_time_keyboard' => $onetime,
            //'input_field_placeholder' => "",
            'selective'         => $selective,
        ];
        return json_encode($replyMarkup, true);
    }

    private function buildInlineKeyUrl($key){
        $tedad=count($key);
        for ($i=0;$i<=$tedad-1;$i++){
            if (is_array($key[$i])){
                for ($j=0;$j<=count($key[$i])-1;$j++){
                    if ($j%2==0){
                        $key_name=$key[$i][$j];
                    }else{
                        //$a=($j+1)/2-1;
                        $option2[] = $this->buildInlineKeyBoardButton($key_name, $key[$i][$j]);
                    }
                }
                $i++;
                $a=($i+1)/2-1;
                $option[$a]=$option2;
                $option2=array();
            }else{
                if ($i%2==0){
                    $key_name=$key[$i];
                }else{
                    $a=($i+1)/2-1;
                    $option[$a] = array($this->buildInlineKeyBoardButton($key_name,  $key[$i]));
                }
            }
        }
        return $this->buildInlineKeyBoard($option);
    }

    private function buildInlineKey($key,$urlRow = null){
        $tedad=count($key);
        for ($i=0;$i<=$tedad-1;$i++){
            if (is_array($key[$i])){
                for ($j=0;$j<=count($key[$i])-1;$j++){
                    if ($j%2==0){
                        $key_name=$key[$i][$j];
                    }else{
                        $option2[] = $this->buildInlineKeyBoardButton($key_name, "", $key[$i][$j]);
                    }
                }
                $i++;
                $a=($i+1)/2-1;
                $option[$a]=$option2;
                $option2=array();
            }else{
                if ($i%2==0){
                    $key_name=$key[$i];
                }else{
                    $a=($i+1)/2-1;
                    if ($urlRow[$a]!='url') {
                        $option[$a] = array($this->buildInlineKeyBoardButton($key_name, "", $key[$i]));
                    }else{
                        $option[$a] = array($this->buildInlineKeyBoardButton($key_name, $key[$i]));
                    }
                }
            }
        }
        return $this->buildInlineKeyBoard($option);
    }
    public function buildInlineKeyBoardButton($text, $url = '', $callback_data = '', $switch_inline_query = null, $switch_inline_query_current_chat = null, $callback_game = '', $pay = '')
    {
        $replyMarkup = [
            'text' => $text,
        ];
        if ($url != '') {
            $replyMarkup['url'] = $url;
        } elseif ($callback_data != '') {
            $replyMarkup['callback_data'] = $callback_data;
        } elseif (!is_null($switch_inline_query)) {
            $replyMarkup['switch_inline_query'] = $switch_inline_query;
        } elseif (!is_null($switch_inline_query_current_chat)) {
            $replyMarkup['switch_inline_query_current_chat'] = $switch_inline_query_current_chat;
        } elseif ($callback_game != '') {
            $replyMarkup['callback_game'] = $callback_game;
        } elseif ($pay != '') {
            $replyMarkup['pay'] = $pay;
        }

        return $replyMarkup;
    }
    public function buildInlineKeyBoard(array $options)
    {
        $replyMarkup = [
            'inline_keyboard' => $options,
        ];
        return json_encode($replyMarkup, true);
    }


    //********************************************(SEND)*****************************************
    public function sendText($chat_id,$text="",$keyb="",$reply_to_message_id="", $keyBoard=false,$keyUrl=null){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'text' => $text);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb,$keyUrl);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendMessage',$arr),true);
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["text"])){
                $arr['text']=trim($arr['text']);
                $result=json_decode($this->sendrequest('sendMessage',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendPhoto($chat_id,$photo="",$caption="",$keyb="",$reply_to_message_id="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "photo" => $photo ,'caption' => $caption);;
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendPhoto',$arr),true);
            //$result=$this->sendrequest('sendPhoto',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["photo"])){
                $arr['photo']=trim($arr['photo']);
                $result=json_decode($this->sendrequest('sendPhoto',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendVideo($chat_id,$video="",$caption="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "video" => $video ,'caption' => $caption);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendVideo',$arr),true);
//			$result=$this->sendrequest('sendVideo',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["video"])){
                $arr['video']=trim($arr['video']);
                $result=json_decode($this->sendrequest('sendVideo',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendAudio($chat_id,$audio="",$caption="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "audio" => $audio ,'caption' => $caption);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendAudio',$arr),true);
//			$result=$this->sendrequest('sendAudio',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["audio"])){
                $arr['audio']=trim($arr['audio']);
                $result=json_decode($this->sendrequest('sendAudio',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendVoice($chat_id,$voice="",$caption="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "voice" => $voice ,'caption' => $caption);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendVoice',$arr),true);
//			$result=$this->sendrequest('sendVoice',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["voice"])){
                $arr['voice']=trim($arr['voice']);
                $result=json_decode($this->sendrequest('sendVoice',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendDocument($chat_id,$document="",$caption="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "document" => $document ,'caption' => $caption);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendDocument',$arr),true);
//			$result=$this->sendrequest('sendDocument',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["document"])){
                $arr['document']=trim($arr['document']);
                $result=json_decode($this->sendrequest('sendDocument',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendAnimation($chat_id,$animation="",$caption="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "animation" => $animation ,'caption' => $caption);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendAnimation',$arr),true);
//			$result=$this->sendrequest('sendAnimation',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["animation"])){
                $arr['animation']=trim($arr['animation']);
                $result=json_decode($this->sendrequest('sendAnimation',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }


    public function sendMediaGroup($chat_id,$medias=[],$type=[],$caption="",$reply_to_message_id="")
    {
        /*
     * $type=
     * photo
     * video
     * ausio
     * document
     */
        $result=null;
        if(!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id);
            $arr['media']=array_map(function ($path,$type) {
                return ['type' => $type, 'media' => $path];
            }, $medias, $type);
            if ($caption!=""){
                $arr['media'][0]['caption']=$caption;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendMediaGroup',$arr),true);
            //$result=$this->sendrequest('sendMediaGroup',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["media"])){
                $result=json_decode($this->sendrequest('sendMediaGroup',$arr),true);
            }else{
                $return="undefined chat id or photo";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }

    public function sendContact($chat_id,$phone="",$first_name="",/*$last_name="",*/$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "phone_number" => $phone ,'first_name' => $first_name);//,'$last_name' => $last_name);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendContact',$arr),true);
//			$result=$this->sendrequest('sendContact',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["photo"])){
                $arr['phone_number']=trim($arr['phone_number']);
                $arr['first_name']=trim($arr['first_name']);
                $result=json_decode($this->sendrequest('sendContact',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendLocation($chat_id,$tool="",$arz="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, "longitude" => $tool ,'latitude' => $arz);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendLocation',$arr),true);
//			$result=$this->sendrequest('sendLocation',$arr);
//			return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["latitude"]) and isset($arr["longitude"])){
                $arr['latitude']=trim($arr['latitude']);
                $arr['longitude']=trim($arr['longitude']);
                $result=json_decode($this->sendrequest('sendLocation',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }

    //**********************************(Edit Delete Forward Download)***************************************************
    public function editText($chat_id,$message_id="",$text="",$keyb="",$reply_to_message_id="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id,"message_id" => $message_id, 'text' => $text);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            //$result=json_decode($this->sendrequest('editMessageText',$arr),true);
            $result=$this->sendrequest('editMessageText',$arr);
            return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["message_id"]) and isset($arr["text"])){
                $arr['text']=trim($arr['text']);
                $result=json_decode($this->sendrequest('editMessageText',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function delete($chat_id,$message_id=""){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id,"message_id" => $message_id);
            $result=json_decode($this->sendrequest('deleteMessage',$arr),true);
            //$result=$this->sendrequest('deleteMessage',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["message_id"])){
                $result=json_decode($this->sendrequest('deleteMessage',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=$result['result'];
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function forward($chat_id,$fromChat_id,$message_id){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'from_chat_id' => $fromChat_id, 'message_id' => $message_id);
            $result=json_decode($this->sendrequest('forwardMessage',$arr),true);
            //$result=$this->sendrequest('forwardMessage',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["from_chat_id"]) and isset($arr["message_id"])){
                $arr['from_chat_id']=trim($arr['from_chat_id']);
                $arr['message_id']=trim($arr['message_id']);
                $result=json_decode($this->sendrequest('forwardMessage',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function copyM($chat_id,$fromChat_id,$message_id,$reply_to_message_id="",$caption=false){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'from_chat_id' => $fromChat_id, 'message_id' => $message_id);
            if ($caption===false){
                $arr = array("chat_id" => $chat_id, 'from_chat_id' => $fromChat_id, 'message_id' => $message_id);
            }else{
                $arr = array("chat_id" => $chat_id, 'from_chat_id' => $fromChat_id, 'message_id' => $message_id, 'caption' => $caption);
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('copyMessage',$arr),true);
            //$result=$this->sendrequest('forwardMessage',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["from_chat_id"]) and isset($arr["message_id"])){
                $arr['from_chat_id']=trim($arr['from_chat_id']);
                $arr['message_id']=trim($arr['message_id']);
                $result=json_decode($this->sendrequest('copyMessage',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function download(File $file, $url){
        $file_path = $file->filePath($this);
        $file_address=$this->downloadApiurl.'/'.$file_path;
        return copy($file_address, $url);
    }

    //********************************************(Pay)*************************************************
    public function Invoice($chat_id,$title='',$description='',$provider_token='',$payload='',$price="",$photo_url="",$reply_to_message_id="",$keyb="", $keyBoard=false){
        $result=null;
        if (!is_array($chat_id)){
            if(is_string($price) or is_int($price) or is_integer($price)  or is_double($price)  or is_float($price) )
            {$price=array(array("label" => "قیمت", "amount" => $price));}//,array("label" => "قیمت", "amount" => "$price")); //برای دادن لیست قیمت
            $arr = array("chat_id" =>$chat_id,"title" =>$title,"description" =>$description,"provider_token" =>$provider_token,"payload" => $payload, "prices" => $price,"photo_url"=>$photo_url,);
            if ($keyb != "") {
                if (!$keyBoard) {
                    $keyb = $this->buildInlineKey($keyb);
                }else{
                    $keyb = $this->buildKeyBoard2($keyb);
                }
                $arr['reply_markup'] = $keyb;
            }
            if ($reply_to_message_id != "") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            $result=json_decode($this->sendrequest('sendInvoice',$arr),true);
            //$result=$this->sendrequest('sendInvoice',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["title"]) and isset($arr["description"]) and isset($arr["provider_token"]) and isset($arr["prices"]) and isset($arr["payload"])){
                $arr['photo_url']=trim($arr['photo_url']);
                $arr['description']=trim($arr['description']);
                $arr['provider_token']=trim($arr['provider_token']);
                $arr['payload']=trim($arr['payload']);
                if(is_string($arr['prices']) or is_int($arr['prices']) or is_integer($arr['prices'])  or is_double($arr['prices'])  or is_float($arr['prices']) )
                {$arr['prices']=array(array("label" => "قیمت", "amount" => $arr['prices']));}//,array("label" => "قیمت", "amount" => "$arr['prices']")); //برای دادن لیست قیمت}
                $result=json_decode($this->sendrequest('sendInvoice',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }

        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }

//**************************************(Actions)*************************************
/**
 * typing: ربات در حال نوشتن یک پیام متنی است
 * upload_photo: ربات در حال بارگذاری (آپلود) یک عکس است
 * record_video: ربات در حال ضبط یک ویدئو است
 * upload_video: ربات در حال بارگذاری یک ویدئو است
 * record_voice: ربات در حال ضبط یک پیام صوتی است
 * upload_voice: ربات در حال بارگذاری یک پیام صوتی است
 * upload_document: ربات در حال بارگذاری یک فایل (مانند PDF یا هر نوع فایل دیگر) است
 * choose_sticker: ربات در حال انتخاب یک استیکر برای ارسال است
 * find_location: ربات در حال جستجوی موقعیت مکانی(Location) 
 * record_video_note: ربات در حال ضبط یک ویدئو نوت (پیام تصویری) است
 * upload_video_note: ربات در حال بارگذاری یک ویدئو نوت است.
 */
public function action($chat_id,$action){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'action' => $action);
            $result=json_decode($this->sendrequest('sendChatAction',$arr),true);
            //$result=$this->sendrequest('sendChatAction',$arr);
            //return $result;
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["action"])){
                $arr['action']=trim($arr['action']);
                $result=json_decode($this->sendrequest('action',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=$result['result'];
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    //********************************************(API Send Request)***********************************************************
    public function sendRequest($metod,$arr)
    {
        $command=$metod;
        $data_json=json_encode($arr);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiurl."/$command",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_json,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
            // CURLOPT_MAXREDIRS => 10,
            //CURLOPT_TIMEOUT => 30,
            //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    //****************************************(Handlers And Conditions)*************************************************
    public function MessageHandler($FunctionName, ...$Conditions)
    {
        if ($this->updat_type=="M"){
            $result=true;
            foreach ($Conditions as $Condition){
                if (!$this->ConditionCheck($Condition)){$result=false;}
            }
            if ($result){
                $FunctionName();
                return true;
            }else{
                return false;
            }
        }

    }

    public function CallbackHandler($FunctionName, ...$Conditions)
    {
        if ($this->updat_type=="Cb"){
            $result=true;
            foreach ($Conditions as $Condition){
                if (!$this->ConditionCheck($Condition)){$result=false;}
            }
            if ($result){
                $FunctionName();
                return true;
            }else{
                return false;
            }
        }

    }

    public function EditedHandler($FunctionName, ...$Conditions)
    {
        if ($this->updat_type=="E"){
            $result=true;
            foreach ($Conditions as $Condition){
                if (!$this->ConditionCheck($Condition)){$result=false;}
            }
            if ($result){
                $FunctionName();
                return true;
            }else{
                return false;
            }
        }

    }

    public function CommandHandler($FunctionName, $CommandName, ...$Conditions)
    {
        if ($this->updat_type=="M" and substr_count($this->message->text, $CommandName) != 0){
            $result=true;
            foreach ($Conditions as $Condition){
                if (!$this->ConditionCheck($Condition)){$result=false;}
            }
            if ($result){
                $CommandName .= " ";
                $CommandData=str_replace($CommandName, "", $this->message->text);
                $FunctionName($CommandData);
                return true;
            }else{
                return false;
            }
        }

    }

    public function EventHandler($FunctionName, ...$Conditions)
    {
        $result=true;
        foreach ($Conditions as $Condition){
            if (!$this->ConditionCheck($Condition)){$result=false;}
        }
        if ($result){
            $FunctionName();
            return true;
        }else{
            return false;
        }

    }

    public function ConditionCheck($Condition){
        if ($Condition!='All') {
            $result = false;
            if (is_array($Condition)) {
                if ($Condition[1]=="="){
                    for ($i = 2; $i < count($Condition); $i++) {
                        if ($Condition[$i] == $Condition[0]) {
                            $result = true;
                        }
                    }
                }elseif ($Condition[1]=="!"){
                    for ($i = 2; $i < count($Condition); $i++) {
                        if ($Condition[$i] != $Condition[0]) {
                            $result = true;
                        }else{
                            $result = false;
                            break;
                        }
                    }
                }elseif ($Condition[1]==">"){
                    if ($Condition[2] < $Condition[0]) {
                        $result = true;
                    }
                }elseif ($Condition[1]=="<"){
                    if ($Condition[2] > $Condition[0]) {
                        $result = true;
                    }
                }else{
                    for ($i = 1; $i < count($Condition); $i++) {
                        if ($Condition[$i] == $Condition[0]) {
                            $result = true;
                        }
                    }
                }
            } else {
                if ($this->messageAll->chat->type == $Condition) {
                    $result = true;
                }
                if ($this->messageAll->type == $Condition) {
                    $result = true;
                }
                if ($Condition == 'forward') {
                    if (!is_null($this->messageAll->forward_from)) {
                        $result = true;
                    }
                } elseif ($Condition == 'reply') {
                    if (!is_null($this->messageAll->reply_message)) {
                        $result = true;
                    }
                } elseif ($Condition == 'pay_ok') {
                    if ($this->messageAll->type == 'successful_payment') {
                        $result = true;
                    }
                } elseif ($Condition == 'is_joined') {
                    if ($this->messageAll->type == 'new_chat_members') {
                        $result = true;
                    }
                } elseif ($Condition == 'is-lefted') {
                    if ($this->messageAll->type == 'left_chat_member') {
                        $result = true;
                    }
                }
            }
        }else{$result = true;}

        return $result;
    }
}

class Eitaabot{
    public $token;
    /** @var Message $message */
    public $message;
    /** @var Message $messageAll */
    public $apiurl;
    public $error;

    public function __construct($token)
    {
        $this->token=$token;
        $this->apiurl="https://eitaayar.ir/api/".$token;
    }

    public function sendText($chat_id,$text="",$title="",$reply_to_message_id="", $disable_notification=false,$pin=false,$date="",$viewCountForDelete=null){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'text' => $text);
            if ($title != "") {
                $arr['title'] = $title;
            }
            if ($reply_to_message_id !="") {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            if ($disable_notification){
                $arr['disable_notification'] = 1;
            }
            if ($pin){
                $arr['pin'] = 1;
            }
            if ($date) {
                $arr['date'] = $date;
            }
            if ($viewCountForDelete) {
                $arr['viewCountForDelete'] = $viewCountForDelete;
            }

            $result=json_decode($this->sendRequest('sendMessage',$arr),true);
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["text"])){
                $arr['text']=trim($arr['text']);
                $result=json_decode($this->sendRequest('sendMessage',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendFile($chat_id,$file="",$caption="",$title="",$reply_to_message_id="", $disable_notification=false,$pin=false,$date="",$viewCountForDelete=null){
        $result=null;
        if (!is_array($chat_id)){
            $arr = array("chat_id" => $chat_id, 'file' => $file);

            if ($caption) {
                $arr['caption'] = $caption;
            }
            if ($title) {
                $arr['title'] = $title;
            }
            if ($reply_to_message_id) {
                $arr['reply_to_message_id'] = $reply_to_message_id;
            }
            if ($disable_notification){
                $arr['disable_notification'] = 1;
            }
            if ($pin){
                $arr['pin'] = 1;
            }
            if ($date) {
                $arr['date'] = $date;
            }
            if ($viewCountForDelete) {
                $arr['viewCountForDelete'] = $viewCountForDelete;
            }
            $result=json_decode($this->sendRequest('sendFile',$arr),true);
        }else{
            $arr=$chat_id;
            if(isset($arr["chat_id"]) and isset($arr["file"])){
                //$arr['file']=trim($arr['file']);
                $result=json_decode($this->sendRequest('sendFile',$arr),true);
            }else{
                $return="undefined chat_id or text";
            }
        }
        if ($result !== null) {
            if ($result['ok']){
                $return=new Message($result['result'],$this);
            }else{
                $this->error = array(
                    'Code' =>$result['error_code'],
                    'Description' => $result['description']
                );
                $return=$this->error;
            }
        }
        return $return;
    }
    public function sendRequest($metod,$arr)
    {
        $command=$metod;
        //$data_json=json_encode($arr);
        $curl = curl_init();
        if(array_key_exists('file',$arr)){
            $arr['file']=new CurlFile($arr['file']);
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiurl."/$command",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $arr,//$data_json, //***
            CURLOPT_SSL_VERIFYHOST => 0, //***
            CURLOPT_SSL_VERIFYPEER => false, //***
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
?>