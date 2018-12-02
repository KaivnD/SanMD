<?php 
require("../../../../wp-load.php");
$docID = htmlentities($_REQUEST['docID']);
$title = htmlentities($_REQUEST['title']);
$editorUrl = SANMD_URL."editor";
$docsUrl = SANMD_URL."docs";
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

    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/san.modal.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/style.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/font-awesome.min.css");?>">

    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/bootstrap.min.css");?>">
    <link rel="stylesheet" href="<?php getSanMDFile("statics/css/fileinput.min.css");?>">

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

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <!-- Default panel contents -->
                    <div class="panel-heading">                        
                        <button type="button" id="addDoc" data-toggle="tooltip" data-placement="bottom" title="新建" class="btn btn-default">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                        <button type="button" id="delDoc" data-toggle="tooltip" data-placement="bottom" title="删除" class="btn btn-default">
                            <i class="fa fa-minus" aria-hidden="true"></i>
                        </button>
                        <button type="button" id="editDoc" data-toggle="tooltip" data-placement="bottom" title="编辑" class="btn btn-default">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </button>
                        <button type="button" id="submitDoc" data-toggle="tooltip" data-placement="bottom" title="提审" class="btn btn-default">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </button>
                        <button type="button" id="mediaBtn" data-toggle="tooltip" data-placement="bottom" title="媒体" class="btn btn-default">
                            <i class="fa fa-th-large" aria-hidden="true"></i>
                        </button>
                        <button type="button" id="downDoc" data-toggle="tooltip" data-placement="bottom" title="下载" class="btn btn-default">
                            <i class="fa fa fa-download" aria-hidden="true"></i>
                        </button>
                        <h3 class="text-right k-panel-title">NCFZ | MarkDown</h3>
                    </div>
                    <div class="panel-body">
                        <p>选择一个文档来编辑或者创建一个吧！</p>
                    </div>

                    <table class="table table-hover">
                    <thead>
                        <tr>
                        <th>#</th>
                        <th>名字</th>
                        <th>时间</th>
                        <th>状态</th>
                        </tr>
                    </thead>
                    <tbody id="docTable">

                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php getSanMDFile("statics/js/jquery.min.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/SanRequest.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/bootstrap.min.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/fileinput.min.js");?>"></script>
    <script src="<?php getSanMDFile("statics/js/uploadimages.js");?>"></script>


    <script>
        let contentUrl = "/wp-content/plugins/SanMD/actions/processContents.php";
        let loading = $("#loadingPage");
        let modal = $(".modal-body");
        let docTable = $("#docTable");
        let selectedRow;

        let editDoc = $("#editDoc");
        let addDoc = $("#addDoc");
        let delDocBtn = $("#delDoc");
        let mediaBtn = $("#mediaBtn");
        let submitDocBtn = $("#submitDoc");

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
            let postData = {
                "action":"getDocs",
                "uid":'<?php echo get_current_user_id();?>'
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
                        setDocs(res.docs);
                        loading.css("display","none");
                    }
                }
            });
            
            editDoc.click(function(){
                if(selectedRow === undefined){
                    alert("请选择需要编辑的文档");
                }else{
                    window.location.href = '<?php echo $editorUrl;?>' + "?docID=" + selectedRow;
                }
            });

            addDoc.click(function(){
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

            delDocBtn.click(function(){
                if(selectedRow === undefined){
                    alert("请选择需要删除的文档");
                }else{
                    modal.html("");
                
                    modal.append('<div><h3>确定删除该文件？</h3><a class="fancy-btn" id="docGo">确认</a></div>');
                    openModal();
                    $('#docGo').click(function(){
                        delDoc(selectedRow);
                    }); 
                }
            });

            mediaBtn.click(function (){
                modal.html("");
                
                modal.append('该功能未完善');
                openModal();
            });

            submitDocBtn.click(function(){
                if(selectedRow === undefined){
                    alert("请选择需要提审的文档");
                }else{
                    submitDoc(selectedRow);
                }
            });
            
        };

        function setDocs(docs){
            for(let i = 0; i < docs.length; i ++){
                let doc = docs[i];

                let index = i + 1;

                let docRow = document.createElement('tr');
                docRow.setAttribute("data-doc",doc.id);

                let docIndex = document.createElement('th');
                docIndex.innerText = index;

                let docTitle = document.createElement('td');
                docTitle.innerText = doc.title;

                let docTime = document.createElement('td');
                docTime.innerText = doc.time;

                let docStat = document.createElement('td');
                console.log(doc.stat);
                if(doc.stat === "1"){
                    let previewBtn = document.createElement('a');
                    previewBtn.href = "#";
                    previewBtn.className = "btn btn-default";
                    let faEye = document.createElement('i');
                    faEye.className = "fa fa-eye";
                    previewBtn.appendChild(faEye);
                    docStat.appendChild(previewBtn);                                 
                }else{
                    docStat.innerText = doc.stat;  
                }


                docRow.appendChild(docIndex);
                docRow.appendChild(docTitle);
                docRow.appendChild(docTime);
                docRow.appendChild(docStat);
                
                docTable.append(docRow);

                docRow.onclick = function(){                   
                    checkTabRow();
                    this.className = "info";                 
                    selectedRow = this.getAttribute("data-doc");
                }
            }
        }

        function checkTabRow(){
            let docTable = $('#docTable').find("tr");
            docTable.each(function(){
                if($(this).attr("class") === "info"){
                    $(this).removeClass("info");
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
        function delDoc(id){
            let postData = {
                "action":"delDoc",
                "uid":'<?php echo get_current_user_id();?>',
                "docID":id
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
                        window.location.href = '<?php echo $docsUrl;?>';
                    }
                }
            });
        }

        function submitDoc(id){
            let postData = {
                "action":"submitDoc",
                "uid":'<?php echo get_current_user_id();?>',
                "docID":id
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
                        window.location.href = '<?php echo $docsUrl;?>';
                    }
                }
            });
        }
    </script>
</body>
</html>