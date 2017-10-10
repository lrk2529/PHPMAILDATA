<?php


Class PHPMailData{
	
    private $mail_protocal="{imap.gmail.com:993/imap/ssl}INBOX";

    public $from_mail_id;

    private $passwd;

    public $receive_mail_id="ALL";

    private $connection;

    private $start_date ;

    private $end_date;

    private $fixed_date; 

    private $num_of_msg;

    private $header_info;

    private $pos_content_type;

    private $pos_subject;

    private $pos_from;

    private $pos_to ;

	private $pos_cc ;

	private	$pos_bcc ;

	private	$pos_date ;

	private $arr_of_msg_no;





   
    public function __construct(){
        $this->start_date= date("Y-m-d");
        $this->end_date=date("Y-m-d H:i:s");
        $this->arr_of_msg_no= array();

    }


 	// Setter method 

 	public function mailProtocol($ma){
 	      $this->mail_protocal = $ma;
 	}

 	public function from($mail_id){
 		$this->from_mail_id =$mail_id;
 	}

 	public function password($password){
 		$this->passwd =$password;
 	}

 	public function setRecieve($recieve_email_id){
 		 $this->receive_mail_id =$recieve_email_id;
 		 $from = $recieve_email_id;
 		 $data = imap_search ($this->connection, ' FROM "'.$from.'"');
 		 $this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));

 	}

 	public function setStartDate($recieve_email_id){
 		$this->start_date =$recieve_email_id;
 	}

 	public function setEndDate($recieve_email_id){
 		$this->end_date=$recieve_email_id;
 	}
    
    public function setIntervalDate($start_date,$end_date){
    	$date = explode("/",$start_date);
 		$date = date('d F Y', mktime(0, 0, 0, $date[1], $date[0]+1, $date[2]));
 		$date1 = imap_search ($this->connection, ' before "'.$date.'"');
 		$date = explode("/",$end_date);
 		$date = date('d F Y', mktime(0, 0, 0, $date[1], $date[0]+1, $date[2]));
 		$date2 = imap_search ($this->connection, ' before "'.$date.'"');
 		$data = array_diff($date2, $date1);
 		$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));

    }

    public function setSubject($text){

    	$data = imap_search ($this->connection, 'SUBJECT "'.$text.'"');
 		$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));

    }

 	public function setFixedDate($fixed_date){
 		$date = explode("/",$fixed_date);
 		$date = date('d F Y', mktime(0, 0, 0, $date[1], $date[0], $date[2]));
 		$this->fixed_date = $fixed_date; 
 		$data= imap_search ($this->connection, 'ON "'.$date.'"');
 		$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));
 	}

 	public function setDateReceive($fixed_date,$recieve_email_id){
 		$date = explode("/",$fixed_date);
 		$date = date('d F Y', mktime(0, 0, 0, $date[1], $date[0], $date[2]));
 		$data = imap_search ($this->connection, 'ON "'.$date.'"');
 		$data1 = imap_search ($this->connection, ' FROM "'.$recieve_email_id.'"');
 		$data = array_intersect($data,$data1);
 		$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));
 	}
    
    public function setBcc($email_id){
    	$data = imap_search ($this->connection, ' BCC "'.$email_id.'"');
    	$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));
    }
      public function setCc($email_id){
    	$data = imap_search ($this->connection, ' CC "'.$email_id.'"');
    	$this->arr_of_msg_no = array_unique(array_merge($data,$this->arr_of_msg_no));
    }
 	
 	

 	// Getter Method
 	private function getProtocol(){
 		return $this->mail_protocal;
 	}

 	private function getPasswd(){
 		return $this->passwd;
 	}


   


 	// implimentation method

    public function connect(){
    	$this->connection = imap_open($this->getProtocol(),$this->from_mail_id,$this->getPasswd());
    	$this->num_of_msg = imap_num_msg($this->connection); 
    	$this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	$this->setPositionForHeader();
    }
	
	public function checkConnection(){
		if($this->connection){
			return true;
		}else{
			return false;
		}
	}

	private function setPositionForHeader(){
		$this->pos_content_type = strrpos($this->header_info,"Content-Type:");
		$this->pos_subject = strrpos($this->header_info,"Subject:");
		$this->pos_from = strrpos($this->header_info,"From:");
		$this->pos_to = strrpos($this->header_info,"To:");
		$this->pos_cc = strrpos($this->header_info,"Cc:");
		$this->pos_bcc = strrpos($this->header_info,"Bcc:");
		$this->pos_date =  strrpos($this->header_info,"Date:");	

	}
   
	public function getMessage(){
		if($this->connection){
			 if(strpos(substr($this->header_info,$this->pos_content_type),"alternative"))
			 	  return imap_fetchbody($this->connection, $this->num_of_msg, "1");
			  else
			  	  return  imap_fetchbody($this->connection, $this->num_of_msg, "1.1");  	    
		}else{
			return "Please call the connect method before";
		}
	}

    public function getSubject(){
    	if($this->connection){
			return substr($this->header_info,$this->pos_subject,$this->pos_from-$this->pos_subject);	    
		}else{
			return "Please call the connect method before";
		}

    }

    public function getTo(){
    	$pos = $this->pos_cc;
    	if(!$pos)
    		$pos = $this->pos_content_type;
    	$data= substr($this->header_info, $this->pos_to,$pos-$this->pos_to);
    	return $data;
    }

     public function getCc(){
    	if($this->pos_cc){
    		$data = substr($this->header_info, $this->pos_cc,$this->pos_content_type-$this->pos_cc);
    		return $data;
    	}
    	

    }

    public function getBcc(){
      if($this->pos_bcc){
    		$data = substr($this->header_info, $this->pos_bcc ,$this->pos_date-$this->pos_bcc);
    		return $data;
    	}
      
    }

    public function getAllAttachment(){
        $structure =  imap_fetchstructure ( $this->connection , $this->num_of_msg );
        foreach ($structure->parts as $key => $value) {
        	if($value->ifdisposition){
        		 $filename= date("Yhmids").$value->dparameters[0]->value;
        		 $whandle = fopen("./$filename",'w');
				 stream_filter_append($whandle,'convert.base64-decode',STREAM_FILTER_WRITE);
				 imap_savebody ($this->connection, $whandle, $this->num_of_msg,$key+1);
				 fclose($whandle); 
        	}
        }
    }

    public function getAllCsv(){
    	$structure =  imap_fetchstructure ( $this->connection , $this->num_of_msg );
    	foreach ($structure->parts as $key => $value) {
        	if($value->ifdisposition){
        		if($value->subtype=="CSV"){
	        		 $filename= date("Yhmids").$value->dparameters[0]->value;
	        		 $whandle = fopen("./$filename",'w');
					 stream_filter_append($whandle,'convert.base64-decode',STREAM_FILTER_WRITE);
					 imap_savebody ($this->connection, $whandle, $this->num_of_msg,$key+1);
					 fclose($whandle);
					}  
        	}
        }
    }

    public function getAnyTypeFile($type){
    	$structure =  imap_fetchstructure ( $this->connection , $this->num_of_msg );
    	foreach ($structure->parts as $key => $value) {
        	if($value->ifdisposition){
        		if($value->subtype==strtoupper($type)){
	        		 $filename= date("Yhmids").$value->dparameters[0]->value;
	        		 $whandle = fopen("./$filename",'w');
					 stream_filter_append($whandle,'convert.base64-decode',STREAM_FILTER_WRITE);
					 imap_savebody ($this->connection, $whandle, $this->num_of_msg,$key+1);
					 fclose($whandle);
					}  
        	}
        }
    }

    public function getAnySpecificFile($type){
    	$structure =  imap_fetchstructure ( $this->connection , $this->num_of_msg );
    	foreach ($structure->parts as $key => $value) {
        	if($value->ifdisposition){
        		if(strtoupper($value->dparameters[0]->value)==strtoupper($type)){
	        		 $filename= date("Yhmids").$value->dparameters[0]->value;
	        		 $whandle = fopen("./$filename",'w');
					 stream_filter_append($whandle,'convert.base64-decode',STREAM_FILTER_WRITE);
					 imap_savebody ($this->connection, $whandle, $this->num_of_msg,$key+1);
					 fclose($whandle);
					}  
        	}
        }
    }


    public function getMessages(){  
    	$all_messages=array();
    	foreach ($this->arr_of_msg_no as  $value) {
    		$this->num_of_msg = $value; 
    	    $this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	    $this->setPositionForHeader();
			$all_messages[]= $this->getMessage();	
    	}
    	$this->connect();
    	return $all_messages;
    	
    }

    public function getTos(){
    	$all_tos=array();
    	foreach ($this->arr_of_msg_no as  $value) {
    		$this->num_of_msg = $value; 
    	    $this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	    $this->setPositionForHeader();
			$all_tos[]= $this->getTo();	
    	}
    	$this->connect();
    	return $all_tos;
    }

     public function getBccs(){
    	$all_bccs=array();
    	foreach ($this->arr_of_msg_no as  $value) {
    		$this->num_of_msg = $value; 
    	    $this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	    $this->setPositionForHeader();
			$all_bccs[]=$this->getBcc();	
    	}
    	$this->connect();
    	return $all_bccs;
    }

    public function getSubjects(){
    	$all_subjects=array();
    	foreach ($this->arr_of_msg_no as  $value) {
    		$this->num_of_msg = $value; 
    	    $this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	    $this->setPositionForHeader();
			$all_subjects[]=$this->getSubject();	
    	}
    	$this->connect();
    	return $all_subjects;
    }

    public function getCcs(){
    	$all_ccs=array();
    	foreach ($this->arr_of_msg_no as  $value) {
    		$this->num_of_msg = $value; 
    	    $this->header_info= imap_fetchbody($this->connection, $this->num_of_msg, "0");  
    	    $this->setPositionForHeader();
			$all_ccs[]=$this->getCc();	
    	}
    	$this->connect();
    	return $all_ccs;
    }

    public function getAllAttachments(){
    	foreach ($this->arr_of_msg_no as  $val) {
    		$this->num_of_msg = $val;
    		$this->getAllAttachment();
    	}
        $this->connect();
    }
    
    public function getAllCsvs(){
    	foreach ($this->arr_of_msg_no as  $val) {
    		$this->num_of_msg = $val;
    		$this->getAllCsv();
    	}
        $this->connect();
    }

    public function getAnyTypeFiles($ext){
    	foreach ($this->arr_of_msg_no as  $val) {
    		$this->num_of_msg = $val;
    		$this->getAnyTypeFile($ext);
    	}
        $this->connect();
    }

     public function getAnySpecificFiles($ext){
    	foreach ($this->arr_of_msg_no as  $val) {
    		$this->num_of_msg = $val;
    		$this->getAnySpecificFile($ext);
    	}
        $this->connect();
    }

    public function test(){
    	echo "test is going here";
    	//var_dump(imap_search ($this->connection, '  FROM "raman22bca@gmail.com" '));
          var_dump($this->arr_of_msg_no);
    }




    


}





//$test = new PHPMailData();
//$test->mailProtocol("{imap.gmail.com:993/imap/ssl}INBOX");
/*$test->from("testramanumar@gmail.com");
$test->password("8730852612");*/
//$test->from("nnergix.reconnect@gmail.com");
//$test->password("nnergix_rec123");
//$test->connect();


//$test->setFixedDate("08/08/2017");
//$test->getAllAttachments();
//$test->getAnySpecificFiles("20170808_02.zip");


// condition area 

	/*$test->setRecieve("raman22bca@gmail.com");
	$test->setFixedDate("01/08/2017");*/
	//$test->setDateReceive("03/08/2017","raman22bca@gmail.com");
	//$test->setIntervalDate("02/08/2017","05/08/2017");
 	// $test->setSubject("ohm");
  	// $test->setBcc("rajkamal90@gmail.com");
  	//$test->setCc("lrk2529@gmail.com");
 	//$test->setFixedDate();

/*echo $test->getMessage();
  echo $test->getSubject();
  echo $test->getTo();
  echo $test->getCc();*/
 //echo $test->getMessage();
  //echo $test->->getAllCsv();
  // echo  $test->getAnyTypeFile("extension_of_files")
  // echo $test->getAllCsv()
   // echo $test->getAllAttachment();
 // var_dump($test->test());

   //var_dump($test->getSubjects()); 

  // var_dump($test->getMessages());

  //var_dump($test->getAllAttachments());
  // var_dump($test->getAllCsvs());
  // var_dump($test->getAnyTypeFiles("jpeg"));
  // $test
  // $test->test();


?>

