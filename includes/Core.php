<?php

function sanMDebug($debug){
    echo "<pre>";
    var_dump($debug);
    echo "</pre>";
}

function getSanMDFile($file){
    echo SANMD_URL.$file;
}

function newSanMD($title,$content = ""){
    global $wpdb;
    $uid = get_current_user_id();
    $checkTitle = $wpdb->get_var("Select * From $wpdb->sanmd Where title = '$title'");
    if(!$checkTitle){
        $newMD = $wpdb->query("Insert Into $wpdb->sanmd (uid,title,content) Values ($uid,'$title','$content')");
        if($newMD){
            $thisDoc = $wpdb->get_results("Select id From $wpdb->sanmd Where uid = $uid Order By time DESC");
            return $thisDoc[0]->id;
        }else{
            return false;
        }
    }else{
        return 0;
    }
}

function saveSanMD($id,$content){
    global $wpdb;
    $uid = get_current_user_id();
    $id = intval($id);
    $old = $wpdb->get_results("Select * From $wpdb->sanmd Where id = $id");
    
    if($old[0]->content != $content){
        $updateMD = $wpdb->query("Update $wpdb->sanmd Set content = '$content' Where id = $id And uid = $uid");
        if($updateMD){
            return true;
        }else{
            return false;
        }
    }else{
        return 0;
    }
    
}

function getSanMD($id){
    global $wpdb;
    $uid = get_current_user_id();
    $content = $wpdb->get_results("Select * From $wpdb->sanmd Where id = $id And uid = $uid");
    if($content != false){
        return $content;
    }else{
        return false;
    }
}

function getAllDocs(){
    global $wpdb;
    $uid = get_current_user_id();
    $docs = $wpdb->get_results("Select * From $wpdb->sanmd Where uid = $uid Order by time DESC");
    if($docs != false){
        return $docs;
    }else{
        return false;
    }
}

function delDoc($id){
    global $wpdb;
    $uid = get_current_user_id();
    $isDel = $wpdb->query("Delete From $wpdb->sanmd Where id = $id And uid = $uid");
    if($isDel != false){
        return true;
    }else{
        return false;
    }
}