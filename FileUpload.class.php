<?php
       /**
       * 
       */
       class FileUpload
       {
       	private $filepath;
       	private $allowtype=array('gif', 'jpg', 'png', 'jpeg');
       	private $maxsize=5000000;
       	private $israndomname=true;
       	private $originName;
       	private $tmpFileName;
       	private $fileType;
       	private $fileSize;
       	private $newFileName;
       	private $errorNum=0;  // no error
       	private $errorMess="";


       	// initial upload file
       	// 1 path of file 2 size of file 3 type of file 4 random file name?
       	function __construct($options=array())
       	{
       		foreach ($options as $key => $value) {
       			$key=strtolower($key);
       			//check if arguements match attributes
       			if(!in_array($key, get_class_vars(get_class($this)))){
       				continue;
       			}

       			$this->setOption($key, $value);
       		}
       		

       	}

        private function getError(){
        	$str="There is fault when upload<font color='red'> {$this->originName} </font>";

        	switch ($this->errorNum) {
        		case 4:
        			$str.="no file is uploaded";
        			break;
        		case 3:
        			$str.="only part of file is uploaded";
        			break;
        		case 2:
        			$str.="over max file size";
        			break;
        		case 1:
        			$str.="over php.ini upload_max_filesize";
        			break;
        		case -1:
        			$str.="unallowed file";
        			break;
        		case -2:
        			$str.="have to give a path";
        			break;
        		case -3:
        			$str.="There is a fault with upload dir, Please choose another dir";
        			break;
        		case -4:
        			$str.="size ove maxsize";
        			break;
        		case -5:
        			$str.="upload failed";
        			break;

                
        		
        		default:
        			$str.="unknown fault";
        			break;
        	}


        	return $str.'<br>';
        }

       	private function checkFilePath(){
       		if (empty($this->filepath)) {
       			$this->setOption('errorNum', -2);
       			return false;
       		}

       		if(!file_exists($this->filepath)||!is_writable($this->filepath)){
       			if(!@mkdir($this->filepath, 0777)){
       				$this->setOption('errorNum', -3);
       				return false;
       			}
       		}
       		return true;

       	}

       	private function checkFileType(){
       		if(in_array(strtolower($this->fileType), $this->allowtype)){
       			return true;
       		}else{
       			$this->setOption('errorNum', -1);
       			return false;
       		}

       	}

       	private function checkFileSize(){
       		if($this->fileSize > $this->maxsize){
       			$this->setOption('errorNum', -4);
       			return false;
       		}else{
       			return true;
       		}

       	}

       	//Set new file name
       	private function setNewFileName(){
       		if($this->israndname){
       		       $this->setOption('newFileName', $this->proRandName());
       	   }else{
       	   	       $this->setOption('newFileName', $this->originName);
       	   }
       	}

       	private function proRandName(){
       		$fileName=date("YmdHis").rand(100,999);
       		return $fileName.'.'.$this->fileType;

       	}

        private function setOption($key, $value){
       		$this->$key=$value;
       	}

       	function uploadFile($fileField){
       		$return=true;
       		if(!$this->checkFilePath()){
       			$this->errorMess=$this->getError();
       			return false;
       		}

            //Check filepath
       		$name=$_FILES[$fileField]['name'];
       		$tmp_name=$_FILES[$fileField]['tmp_name'];
       		$size=$_FILES[$fileField]['size'];
       		$error=$_FILES[$fileField]['error'];

       		if(is_array($name)){
                $errors=array();

                for($i=0; $i<count($name); $i++){
                	if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])){
	                      if(!$this->checkFileSize() || !$this->checkFileType()){
	                      	$errors[]=$this->getError();
	                      	$return=false;
	                      }
                    }else{
                    	$error[]=$this->getError();
                    	$return=false;
                    }
                    if(!$return){
                    	$this->setFiles();
                    }
                }

                if($return){
                	$fileNames=array();

                	for($i=0; $i<count($name); $i++){
                		if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])){
                			$this->setNewFileName();
                			if(!$this->copyFile()){
                				$errors=$this->getError();
                				$return=false;
                			}else{
                				$fileNames[]=$this->newFileName;
                			}
                		}
                	}

                	$this->newFileName=$fileNames;
                }

                $this->errorMess=$errors;
                return $return;

       		}else{

          
       		  if($this->setFiles($name, $tmp_name, $size, $error)){
       		  	if($this->checkFileSize() && $this->checkFileType()){
       		  		$this->setNewFileName();

       		  	    if($this->copyFile()){
       		  	    	return true;
       		  	    }else{
       		  	    	return false;
       		  	    }

       		  	}else{
       		  		$return=false;
       		  	}

       		  }else{
       		  	$return=false;
       		  }

       		  if(!$return){
       		  	$this->errorMess=$this->getError();
       		  }

       		  return $return;
            }
       	}

       	private function copyFile(){
       		if(!$this->errorNum){
                $filepath=rtrim($this->filepath, '/').'/';
                $filepath.=$this->newFileName;

                if(@move_uploaded_file($this->tmpFileName, $filepath)){
                	return true;
                }else{
                    $this->setOption('errorNum', -5);
                    return false;

                }
       		}else{
       			return false;
       		}
       	}

       	private function setFiles($name="", $tmp_name="", $size=0, $error=0){
       		
       		$this->setOption('errorNum', $error);

       		if($error){
       			return false;
       		}

       		$this->setOption('originName', $name);
       		$this->setOption('tmpFileName', $tmp_name);
            $arrStr=explode('.', $name);
       		$this->setOption('fileType', strtolower($arrStr[count($arrStr)-1]));
       		$this->setOption('fileSize', $size);

            
            return true;
       	}
        
       	function getNewFileName(){
       		return $this->newFileName;

       	}

       	function getErrorMsg(){
       		return $this->errorMess;
            
       	}
       }