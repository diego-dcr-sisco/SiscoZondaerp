var appMethods_selected = [];
var pests_selected = [];

$(() => {
    setAppMethods();
    setPests();
});

function setAppMethods() {
    appMethods_selected = $(".appMethod:checked")
        .map(function () {
            return parseInt($(this).val());
        })
        .get();
    $('#appMethods-selected').val(JSON.stringify(appMethods_selected));
}

function setPests() {
    pests_selected = $(".pest:checked")
        .map(function () {
            return parseInt($(this).val());
        })
        .get();

    $("#pests-selected").val(JSON.stringify(pests_selected));
}
