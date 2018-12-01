<?php 
require("../../../../wp-load.php");
$docID = htmlentities($_REQUEST['docID']);
$title = htmlentities($_REQUEST['title']);
$editorUrl = SANMD_URL."editor";
if(!is_user_logged_in()){
	header("Location: ".wp_login_url($editorUrl));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo bloginfo('name');?> | MarkDown编辑器</title>
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/simplemde.min.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/san.modal.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/style.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/font-awesome.min.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/github.min.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/bootstrap.min.css");?>">
</head>
<body>
    <!--loading page-->
    <div id="loadingPage">
        <i class="fa fa-spinner fa-spin loadingIcon" aria-hidden="true"></i>
    </div>
    <div id="md1" class="modal-frame">
            <div class="modal">
                <div class="modal-inset">
                    <div class="close"><i class="fa fa-close"></i></div>

                    <div class="modal-body">

                    </div>
                </div>
            </div>
        </div>
    <div class="modal-overlay"></div>

    <script src="<?php getSanMDFile("statics/js/jquery.min.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/SanRequest.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/bootstrap.min.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/highlight.min.js");?>"></script>

    <script src="<?php getSanMDFile("statics/js/simplemde.min.js");?>"></script>
    <script>
        let contentUrl = "/wp-content/plugins/SanMD/actions/processContents.php";
        let loading = $("#loadingPage");
        let modal = $(".modal-body");
        $modal = $('#md1');
        $overlay = $('.modal-overlay');
        function closeModal(){
            $overlay.removeClass('state-show');
            $modal.removeClass('state-appear').addClass('state-leave');
        }
        function openModal(){
            $overlay.addClass('state-show');
            $modal.removeClass('state-leave').addClass('state-appear');
        }
        $modal.bind('webkitAnimationEnd oanimationend msAnimationEnd animationend', function(e){
            if($modal.hasClass('state-leave')) {
                $modal.removeClass('state-leave');
            }
        });

        $('.close').on('click', function(){
            closeModal();
        });

        window.onload = function (){

            let body = $("body");
            body.append('<textarea style="display:none;"></textarea>');

            let postData = {
                "action":"getContent",
                "uid":'<?php echo get_current_user_id();?>',
                "docID":'<?php echo $docID;?>'
            };
            new SanRequest({
                type: "post",
                url: contentUrl,
                param: JSON.stringify(postData),
                isShowLoader: false,
                dataType: "json",
                callBack: function(res){
                    if (res.stat === 0) {
                        alert(res.msg);
                    }else{
                        editorHandle(res.content);
                    }
                }
            });                     
        };

        function editorHandle(content){
            var simplemde = new SimpleMDE({
                autoDownloadFontAwesome:false,
                autofocus: true,
                placeholder: "整点动静...",
                spellChecker:false,
                initialValue:content,
                renderingConfig:{
                    codeSyntaxHighlighting:true
                },
                hideIcons: ["guide","unordered-list","ordered-list","quote","heading"],    
                showIcons: ["code"]                
            });
            simplemde.toggleFullScreen();
            let editorToolbar = $(".editor-toolbar");
            editorToolbar.prepend('<i class="separator">|</i>');
            editorToolbar.prepend('<a title="保存" id="saveMD" tabindex="-1" class="fa fa-save"></a>');
            editorToolbar.prepend('<a title="新建" id="newMD" tabindex="-1" class="fa fa-star"></a>');
            editorToolbar.prepend('<a title="返回" id="goBack" tabindex="-1" class="fa fa-arrow-left"></a>');
            let saveBtn = $("#saveMD");
            let newBtn = $("#newMD");
            saveBtn.click(function (){
                saveContent(simplemde.value());
            });
            newBtn.click(function(){
                modal.html("");
                let userName = document.createElement("input");
                userName.type = "username";
                userName.className = "form-control";
                userName.id = "docName";
                userName.placeholder = "新文档名字";
                modal.append(userName);
                modal.append('<a class="fancy-btn" id="docGo">确认</a>');
                openModal();
                $('#docGo').click(function(){
                    let titleVal = $("#docName").val();
                    console.log(titleVal);
                    newDoc(titleVal);
                });
            });
            loading.css("display","none");
        }

        function saveContent(content){
            let postData = {
                "action":"saveContent",
                "uid":'<?php echo get_current_user_id();?>',
                "content":content,
                "docID":'<?php echo $docID;?>'
            };
            new SanRequest({
                type: "post",
                url: contentUrl,
                param: JSON.stringify(postData),
                isShowLoader: false,
                dataType: "json",
                callBack: function(res){
                    if (res.stat === 0) {
                        alert(res.msg);
                    }else{
                        alert(res.msg);
                    }
                }
            });
        }

        function newDoc(title){
            let postData = {
                "action":"newDoc",
                "uid":'<?php echo get_current_user_id();?>',
                "title":title
            };
            console.log(postData);
            new SanRequest({
                type: "post",
                url: contentUrl,
                param: JSON.stringify(postData),
                isShowLoader: false,
                dataType: "json",
                callBack: function(res){
                    if (res.stat === 0) {
                        alert(res.msg);
                    }else{
                        alert(res.msg);
                        window.location.href = '<?php echo $editorUrl;?>' + "?docID=" + res.id;
                    }
                }
            });
        }

    </script>
</body>
</html>