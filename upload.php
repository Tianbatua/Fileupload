<?php
        require "FileUpload.class.php";

        $up=new FileUpload(array('maxsize'=>20000000, 'isRandName'=>true, 'allowType'=>array('txt', 'doc', 'jpg', 'jpeg', 'gif', 'php'), 'filepath'=>'./uploads'));
        
        echo '<pre>';
          
        if($up->uploadFile('spic')){
            print_r($up->getNewFileName()) ;
        }else{
        	print_r($up->getErrorMsg());
        }

        echo '</pre>';
    