$(document).ready(function () {
    less = {
        env: "development",
        async: false,
        fileAsync: false,
        poll: 1000,
        functions: {},
        dumpLineNumbers: "comments",
        relativeUrls: false,
        rootpath: ":/a.com/"
    };
});

// 显示hud
function showHud() {
    $(".main-hud").css("display","block");
};

// 隐藏hud
function hideHud() {
    $(".main-hud").css("display","none");
}

/**
 * 显示提示信息
 * @param message
 */
function toast(message,success=true) {
    layui.use('layer',function () {
        var layer = layui.layer;
        if (typeof message == 'string'){
            var time = message.length * 0.3 * 1000;
            var skin = success ? "" : "toast-fail-style";
            layer.msg(message, {
                // 多长事件后自动关闭(ms)
                time: time,
                offset: 'auto',
                skin: skin
            });
        }
    });
}

// 发送GET网络请求
function get(url, complete=null,withHud=true,showToast=false,timeOut=10000,failFunc=null) {
    request(url,null,complete,withHud,showToast,timeOut,failFunc);
}

// 发送post网络请求
function post(url, data=null, complete=null,withHud=true,showToast=false,timeOut=10000) {
    request(url,data,complete,withHud,showToast,timeOut);
}

// 发送请求
function request(url, data=null, complete=null,withHud=true,showToast=false,timeOut=10000,failFunc=null) {
    if (withHud){
        showHud();
    }

    var method = data == null ? "get" : "post";
    console.log("方法:" + method);

    $.ajax({
        type : method,
        url : url,
        data: data,
        cache : false,
        async : true,
        timeout: timeOut,
        dataType : "json",
        beforeSend:function (jqxhr,settings) {
            jqxhr.requestURL = url;
        }
        ,complete: function () {
            console.log("加载完成");
            if (withHud) {
                hideHud();
            }
        }
        ,success: function (data,state,xhr) {
            console.log("请求成功:" + xhr.requestURL);
            var message = typeof(data.data) == 'string' ? data.data : data.message;
            if (data.code == 200){
                showToast ? toast(message) : "";
                complete ? complete(data) : "";

            }else if(data.code != 200){
                // 提示失败信息
                toast(message,false);
            }
        },
        error: function (xhr,textStatus,errorMessage) {
            if (withHud) {
                hideHud();
            }
            if (showToast){
                toast("请求服务器失败");
            }
            console.log("请求失败:" + errorMessage);
            if (failFunc){
                failFunc();
            }
        }
    });
}

// 提示框
function showAlert(message,btns=null) {
    var btnTitles = [];
    var btnFunctions = [];
    if (btns){
        for (var i = 0; i < btns.length; i++){
            var btn = btns[i];
            btnTitles[i] = btn.title;
            btnFunctions[i] = btn.func;
        }
    }else {
        btnTitles = ["好的"]
        btnFunctions = [function () {}]
    }

    layui.use('layer',function () {
        var layer = layui.layer;
        layer.open({
            type: 1
            ,title: false //不显示标题栏
            ,closeBtn: false
            ,area: '300px;'
            ,shade: 0.8
            ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
            ,btn: btnTitles
            ,btnAlign: 'c'
            ,moveType: 1 //拖拽模式，0或者1
            ,content: '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">'+message+'</div>'
            ,yes: function(index, layero){
                var btnIndex = 0;
                // 按钮1回调
                if (btnIndex <= btnFunctions.length){
                    btnFunctions[btnIndex](btnIndex);
                }
                // 关闭layer
                layer.close(index);
            }
            ,btn2:function (index,layero) {
                var btnIndex = 1;
                if (btnIndex<btnFunctions.length){
                    btnFunctions[btnIndex](btnIndex);
                }
            }
        });
    });
}

layui.use('form',function () {
    var form = layui.form;
    form.render();
});

// 复制输入框文本到剪切板
function copyInputContent(inputDom,message="复制成功") {
    inputDom.select();
    document.execCommand("Copy");
    toast(message);
}

// 确认提示 操作前提示确认，防止误操作
function confirmAlert(confirmFunction,message="确定删除？") {
    var btns = [
        {
            title: "确定",
            func: function (index) {
                if (confirmFunction){
                    confirmFunction();
                }
            }
        },
        {
            title: "取消",
            func: function () {}
        }
    ];
    showAlert(message,btns);
}