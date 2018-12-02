var imgSrc = []; //图片路径
var imgFile = []; //文件流
var imgName = []; //图片名字
var callback = '';
var request;
//选择图片
function imgUpload(obj) {
    var oInput = '#' + obj.inputId;
   // var imgBox = '#' + obj.imgBox;
    var btn = '#' + obj.buttonId;
    $(oInput).on("change", function() {
        var fileImg = $(oInput)[0];
        var fileList = fileImg.files;
        for(var i = 0; i < fileList.length; i++) {
            var imgSrcI = getObjectURL(fileList[i]);
            imgName.push(fileList[i].name);
            imgSrc.push(imgSrcI);
            imgFile.push(fileList[i]);
        }
        //addNewContent(imgBox);
    })
    $(btn).on('click', function() {
        if(!limitNum(obj.num)){
            alert("超过限制");
            return false;
        }

        if($("#theme").find("option:selected").text() == "=请选择主题="){
            alert("请选择主题")
        }else{
            //用formDate对象上传
            var fd = new FormData($('#upBox')[0]);
            for(var i=0;i<imgFile.length;i++){
                fd.append(obj.data+"[]",imgFile[i]);
            }
            submitPicture(obj.upUrl, fd);
        }

    })
}


//限制图片个数
function limitNum(num){
    if(!num){
        return true;
    }else if(imgFile.length>num){
        return false;
    }else{
        return true;
    }
}

//上传(将文件流数组传到后台)
function submitPicture(url,data) {
    for (var p of data) {
        console.log(p);
    }
    if(url&&data){
        request = $.ajax({
            type: "post",
            url: url,
            async: true,
            data: data,
            processData: false,
            contentType: false,
            success: function(dat) {
                callback = JSON.parse(dat);
                console.log(callback);
                if(callback["process"]){
                   OnRequestDone(callback["sessionkey"],$("#theme").find("option:selected").text()); //TODO 加上载入圈

                }else {
                    alert(callback["message"]);
                }
            }
        });
    }else{
        alert('请打开控制台查看传递参数！');
    }
}
function OnRequestDone(SessionKey,Theme) {
    $("#myModal").modal('hide');
    $("#start-btn").css("display","none");
    var editor_url = 'editor?theme='+Theme+"&session_key="+SessionKey;
    window.location.href = editor_url;
}
//图片预览路径
function getObjectURL(file) {
    var url = null;
    if(window.createObjectURL != undefined) { // basic
        url = window.createObjectURL(file);
    } else if(window.URL != undefined) { // mozilla(firefox)
        url = window.URL.createObjectURL(file);
    } else if(window.webkitURL != undefined) { // webkit or chrome
        url = window.webkitURL.createObjectURL(file);
    }
    return url;
}