$(document).on('keyup', 'input.autocomplete[type="text"]',function(){
    var searchText = $(this).val().toLowerCase();

    $(this).parent().siblings('ul').find('li').each(function(){
        var currentLiText = $(this).text().toLowerCase(),
            showCurrentLi = currentLiText.indexOf(searchText) !== -1;

        $(this).toggle(showCurrentLi);
    });     
});

$(document).on('click','.see_more', function(){
    $(this).hide();
    $(this).siblings('ul').find('li').each(function(){
        $(this).removeClass('hide');
    })
})

$(document).on('click','.back-home', function(){
    window.location.href = "index.php";
}) 