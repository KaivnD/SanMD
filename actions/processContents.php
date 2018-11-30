<?php
//开启php.ini中的display_errors指令
ini_set('display_errors',1);
//通过error_reporting()函数设置，输出所有级别的错误报告
error_reporting(E_ALL);

require( dirname(__FILE__).'/../../../../wp-load.php' );
global $wpdb;

if(!is_user_logged_in()){
	wp_die('请先登录系统');
}
$data_json = json_decode(file_get_contents('php://input'),true);
$docID = isset($data_json['docID']) && is_numeric($data_json['docID']) ? intval($data_json['docID']) :false;
$puid = isset($data_json['uid']) && is_numeric($data_json['uid']) ? intval($data_json['uid']) :false;
$action = isset($data_json['action']) ? strval($data_json['action']) :false;
$docID = $wpdb->_escape($docID);//用于进行 SQL 查询之前的字符串处理。
$response = array(
    'stat'=>'0',
    'msg'=>'未知错误',
    "request"=>$data_json
);
if($docID){
	$user = wp_get_current_user();
	$uid = $user->ID;
	$days=get_post_meta($postID, 'down_days', true);
	if($uid){
        if($uid == $puid){
            if($action == "getContent"){
                $getContent = getSanMD($docID);
                if($getContent){
                    $response = array(
                        'stat'=>'1',
                        'msg'=>'获取文档成功',
                        'content'=>$getContent[0]->content
                    );
                }else{
                    $response = array(
                        'stat'=>'0',
                        'msg'=>'获取文档失败'
                    );
                }
            }elseif($action == "saveContent"){
                $content = $data_json['content'];
                $saveContent = saveSanMD($docID,$content);
                if($saveContent){
                    $response = array(
                        'stat'=>'1',
                        'msg'=>'保存成功'
                    );
                }elseif($saveContent == 0){
                    $response = array(
                        'stat'=>'1',
                        'msg'=>'已经保存过了'
                    );
                }else{
                    $response = array(
                        'stat'=>'0',
                        'msg'=>'保存文档失败'
                    );
                }
            }
        }else{
            $response = array(
                'stat'=>'0',
                'msg'=>'请勿窃取他人隐私'
            );
        }

	}else{
		$response = array(
			'stat'=>'0',
			'msg'=>'用户信息获取失败'
		);
    }
    
}elseif($action == "newDoc"){
    $title = $data_json['title'];
    $newDoc = newSanMD($title);
    if($newDoc){
        $response = array(
            'stat'=>'1',
            'msg'=>'创建成功',
            "id"=>$newDoc
        );
    }elseif($newDoc == 0){
        $response = array(
            'stat'=>'0',
            'msg'=>'该标题您已经使用过了'
        );
    }else{
        $response = array(
            'stat'=>'0',
            'msg'=>'创建失败'
        );
    }
}else{
	$response = array(
		'stat'=>'0',
        'msg'=>'文章ID错误',
        "docID"=>$docID
	);
}
echo json_encode($response);