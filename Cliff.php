<?php
	class CliffJob{
		 public $ID;
		 public $name;
		 public $file;
		 public $description;
		 public $performCASS;
		 public $performPresort;
		 public $performMOVE;
		 public $mapping;
		 public $PresortSettings = NULL;
		 public $CASSSettings = NULL;
		 public $MOVESettings = NULL;
		 private $documents;
		 private $status;
		 private $curl;
		 private $username;
		 private $password;
		 private $cookie;
		 
		 public function __construct($username, $password){
		 	$data_string = "username={$username}&password={$password}";
		 	
		 	$this->curl = curl_init();     
		 	curl_setopt($this->curl, CURLOPT_URL, 'manager.cliffdelivers.com/authenticate');                                                                                                                                   
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_HEADER, true);

			$result = curl_exec($this->curl);
			$pattern = "#connect.sid=.*?;#"; 
			preg_match_all($pattern, $result, $matches); 
			$this->cookie = rtrim($matches[0][0], ';');
			
			curl_setopt($this->curl, CURLOPT_COOKIE, $this->cookie);
			curl_setopt($this->curl, CURLOPT_HEADER, false);
		 }
		 
		 public function create(){                                                                     
			curl_setopt($this->curl, CURLOPT_URL, 'manager.cliffdelivers.com/newjob');
			curl_setopt($this->curl, CURLOPT_POST, true);  
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, NULL);                                                                                                                                   
			$result = curl_exec($this->curl);
			
			$json = json_decode($result);
			
			$this->ID = $json->jobID;
		 }
		 
		 public function get($id){
		 	curl_setopt($this->curl, CURLOPT_URL, "manager.cliffdelivers.com/job?jobID={$id}");                                                                      
			curl_setopt($this->curl, CURLOPT_POST, false);                                                                                                                                  
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
			$result = curl_exec($this->curl);
			
			print_r(json_decode($result));
		 }
		 
		 public function update(){                                                                   
			
			$job_props = array('name','performCASS','performPresort','performMOVE');                                                                                 
 			foreach($job_props as $val){
				$data_string = "jobID={$this->ID}&value={$this->$val}";

		 		curl_setopt($this->curl, CURLOPT_URL, "manager.cliffdelivers.com/job/{$val}");                                                                                                                                           
				curl_setopt($this->curl, CURLOPT_POST, true);
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
				$result = curl_exec($this->curl);
				
				print_r($result);
			}
			
			/* Update mapping */
			$data_string = "jobID={$this->ID}&value=" . json_encode($this->mapping);

			curl_setopt($this->curl, CURLOPT_URL, "manager.cliffdelivers.com/job/mapping");                                                                                                                                          
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
			$result = curl_exec($this->curl);
			
			print_r($result);
			
			foreach($this->PresortSettings as $key=>$val){
				$data_string = "jobID={$this->ID}&value={$val}";
		 
		 		echo $data_string . PHP_EOL;
		 
		 		curl_setopt($this->curl, CURLOPT_URL, "manager.cliffdelivers.com/job/Presort/{$key}");                                                                    
				curl_setopt($this->curl, CURLOPT_POST, true);
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
				$result = curl_exec($this->curl);
				
				print_r($result);
			}                                                                                                           
 
			/* Send the file */
			if ($this->file != ''){
				/* Base 64 encode the file */
				//$file_contents = file_get_contents($this->file);
				//$based_contents = base64_encode($file_contents);
				
				$stream = fopen($this->file, "r");
				
				echo "Uploading {$this->file} ";
				
				curl_setopt($this->curl, CURLOPT_URL, "manager.cliffdelivers.com/job/file/{$this->ID}");                                                                    
				curl_setopt($this->curl, CURLOPT_PUT, true);
				//curl_setopt($this->curl, CURLOPT_HTTPHEADER, "Content-Type: text/csv");
				curl_setopt($this->curl, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($this->curl, CURLOPT_TIMEOUT, 60);
				// Let curl know that we are sending an entity body
				curl_setopt($this->curl, CURLOPT_UPLOAD, true);
				// Let curl know that we are using a chunked transfer encoding
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Transfer-Encoding: chunked'));
				// Use a callback to provide curl with data to transmit from the stream
				curl_setopt($this->curl, CURLOPT_READFUNCTION, function($ch, $fd, $length) use ($stream) {
					echo ".";
					return ($string = fread($stream, $length)) ? $string : false;
				});                                                               
				$result = curl_exec($this->curl);
				
				echo PHP_EOL;
				
				print_r($result);
				
				curl_setopt($this->curl, CURLOPT_UPLOAD, false);
				curl_setopt($this->curl, CURLOPT_BINARYTRANSFER, false);
			}
		 }
		 
		 public function start(){
		 	$data_string = "jobID={$this->ID}";
		 	curl_setopt($this->curl, CURLOPT_URL, 'manager.cliffdelivers.com/start');                                                                   
			curl_setopt($this->curl, CURLOPT_POST, true);                                                                                                                                     
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
			$result = curl_exec($this->curl);
				
			print_r($result);
		 }
		 
		 public function getStatus(){
		 	curl_setopt($this->curl, CURLOPT_URL, 'manager.cliffdelivers.com/job/status/' . $this->ID);                                                                      
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                                     
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
			$result = curl_exec($this->curl);
			
			$json = json_decode($result);

			return $json->status;
		 }
		 
		 public function delete(){
		 
		 }

		 public function wait(){
			echo "\n";
			$status = '';
			while($status != "complete"){
				echo "\rWaiting...({$status})";
				sleep(5);
				$status = $this->getStatus();
			}	
		 }
		 
		 public function downloadArchive($path, $filename = false, $unzip = false){ 
			if ($filename === false) {
				 $filename = "Archive.zip";
			}
			
			$curl = curl_init();
		 	curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);
			curl_setopt($curl, CURLOPT_HEADER, false);		
		 	curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		 	curl_setopt($curl, CURLOPT_TIMEOUT, 120);	 		
		 	curl_setopt($curl, CURLOPT_URL, "manager.cliffdelivers.com/job/file/{$this->ID}");
			curl_setopt($curl, CURLOPT_POST, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 	
			file_put_contents($path . '/' . $filename, curl_exec($curl));
			
			if ($unzip){
				exec("unzip {$path}/{$filename} -d {$path}");
			}
			
			return true;
		 }
	}
	
	class CliffPresortSettings{
		public $mailClass;
		public $mailWidth;
		public $mailHeight;
		public $mailWeight;
		public $mailThickness;
		public $mailDate;
		public $mailOwnerCompanyName;
		public $mailOwnerContactName;
		public $mailOwnerAddress;
		public $mailOwnerCity;
		public $mailOwnerState;
		public $mailOwnerZip;
		public $mailOwnerEmail;
		public $mailOwnerDUNS;
		public $mailOwnerCRID;
		public $mailOwnerMailerID;
		public $mailOwnerPermitNumber;
		public $permitHolderCompanyName;
		public $permitHolderContactName;
		public $permitHolderAddress;
		public $permitHolderCity;
		public $permitHolderState;
		public $permitHolderZip;
		public $permitHolderEmail;
		public $permitHolderDUNS;
		public $permitHolderCRID;
		public $permitHolderMailerID;
		public $permitHolderPermitNumber;
		public $mailPreparerCompanyName;
		public $mailPreparerContactName;
		public $mailPreparerAddress;
		public $mailPreparerCity;
		public $mailPreparerState;
		public $mailPreparerZip;
		public $mailPreparerEmail;
		public $mailPreparerDUNS;
		public $mailPreparerCRID;
		public $mailPreparerMailerID;
		public $mailPreparerPermitNumber;
		public $mailerCompanyName;
		public $mailerCity;
		public $mailerState;
		public $mailerZip;
		public $firstSortLevel;
		public $secondSortLevel;
		public $discountNonProfit;
		public $discountMailDigitalPersonalization;
		public $discountLimitedCirculation;
	}
	
	class CliffCASS{
	
	}
	
	class CliffMOVE{
		
	}
?>
