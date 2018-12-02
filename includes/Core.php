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
    $checkTitle = $wpdb->get_var("Select * From $wpdb->sanmd Where title = '$title' And uid = $uid");
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
    if(count($docs) != 0){
        return $docs;
    }elseif(count($docs) == 0){
        $title = "Markdown 入门";
        $getStartDoc = newSanMD($title,file_get_contents("../Readme.md"));
        if($getStartDoc){
            $docs = $wpdb->get_results("Select * From $wpdb->sanmd Where uid = $uid Order by time DESC");
            return $docs;
        }else{
            return false;
        }
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

function subDoc($id){
    global $wpdb;
    $uid = get_current_user_id();
    $doc = getSanMD($id)[0];
    if($doc){
        $title = $doc->title;
        $content = $doc->content;
        $parentID = $doc->parentID;
        if($parentID == 0){
            $post = array(
                'post_title' => $title,        
                'post_content' => $content,        
                'post_status' => 'pending',        
                'post_author' => $uid        
            );    
        }else{
            $post = array(
                'ID'=>$parentID,
                'post_title' => $title,        
                'post_content' => $content,        
                'post_status' => 'pending',        
                'post_author' => $uid        
            );    
        }
    
        $postID = wp_insert_post($post);
        if($postID != false){
            if($parentID == 0){
                $updateStat = $wpdb->query("Update $wpdb->sanmd Set stat = 1 , parentID = $postID Where id = $id");
                if($updateStat){
                    return $postID;
                }else{
                    return false;
                }  
            }else{
                return 0;
            }          
        }else{
            return false;
        }

    }else{
        return false;
    }

}