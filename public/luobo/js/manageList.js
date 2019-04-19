layui.use('element', function () {
    var element = layui.element;

});
var pageManage = null;
$(function () {
    pageManage = new PageManage();
});

function PageManage() {
    var node = this.node = $(".webContent");
    var content = this;
    //事件
    node.find(".contentCreat").bind("click", function () {
        node.find("#contentBox iframe").attr("src", "add");
    })
    node.find(".contentManage").bind("click", function () {
        node.find("#contentBox iframe").attr("src", "../front.show/index");
    })
    node.find(".commentManage").bind("click", function () {
        node.find("#contentBox iframe").attr("src", "../front.review/index");
    })
    node.find(".dataStatistics").bind("click", function () {
        node.find("#contentBox iframe").attr("src", "../front.data/index");
    })
    node.find(".dataLabel").bind("click", function () {
        node.find("#contentBox iframe").attr("src", "../front.label/index");
    })
}