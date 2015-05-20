$(document).ready(function(){
    item = 0
    $.getJSON("/list", function(data) {
      items = data
      $('#pair').removeAttr("disabled")
      $('#rand').removeAttr("disabled")
      $('.button_start').removeAttr("disabled")
    })

    // checkbox menu
    $('#checkbox_menu_button_go').click(function () {
        $(this).button('toggle');
        $('.button_start').addClass('hidden')
    });
    $('#checkbox_menu_button_random ').click(function () {
        $(this).button('toggle')
        $('.button_start').addClass('hidden')
    });
    $('#checkbox_menu_button_category ').click(function () {
        $(this).button('toggle')
        $('.button_start').addClass('hidden')
    });

})
$('#button_next').on('click', function(){
    $('.button_start').addClass('hidden')
    ShowAndSpeak(item++)
    if ( $("#checkbox_menu_button_random").prop("checked") ) {
          var randoms = Math.floor((Math.random() * items.length) + 1)
          ShowAndSpeak(randoms)
    }
    if ( $("#checkbox_menu_button_go").prop("checked") ) {
       ShowAndSpeak(item++)
    }
})

$('.button_start').click(function(){
    ShowAndSpeak(item++)
    $('#button_next').removeClass('hidden')
    $('#button_next').show()
    $('.button_start').addClass('hidden')
})

$('#pair').click(function(){
    $('#listWords').empty()
    $('#button_next').show()
    ShowAndSpeak(item++)
    $('#button_next').removeClass('hidden')
})

$('#rand').click(function(){
    $('#listWords').empty()
    $('#button_next').show()
    var rand = Math.floor((Math.random() * items.length) + 1)
    ShowAndSpeak(rand)
    $('#button_next').removeClass('hidden')
})
$('#show-list').click(function(){
    $('.button_start').addClass('hidden')
    $('.container_words').empty()
    $('#button_next').hide()
    $.ajax({
        url: "/listByCategory",
        success: function(data) {
            $('#listWords').empty()
            $('#listWords').append(data)
        }
    })
})
$('#myModal').on('show.bs.modal', function (event) {
    var modal = $(this)
    $.ajax({
        url: "/form",
        success: function(data) {
            modal.empty()
            modal.append(data)
            categories = []
            $.getJSON("/categories")
                .done(function(data) {
                    categories = data
                    $('#form_category').typeahead({
                        hint: true,
                        highlight: true,
                        minLength: 1
                      },
                      {
                        name: 'categories',
                        displayKey: 'value',
                        source: substringMatcher(categories)
                    })
                })
        }
    })
})
$('#mySettings').on('show.bs.modal', function (event) {
    var modal = $(this)
    $.ajax({
        url: "/settings",
        success: function(data) {
            modal.empty()
            modal.append(data)
        }
    })
})
$(document).on('click', '.word', function(){
    speak($(this).text())
    return false
})
function speak(text) {
    text = $.trim(text)
    if (text == '') return false
    var msg = new SpeechSynthesisUtterance()
    msg.text = text
    msg.volume = localStorage["volume"]
    msg.rate   = localStorage["rate"]
    msg.lang   = localStorage["lang"]
    window.speechSynthesis.speak(msg)
}
function ShowAndSpeak(i) {
    $('#show-pair').empty()
    $('#show-pair').html('<p class="block_img"><img src="'+items[i].image+'" alt="'+items[i].russian + '"></p> <p class="word words_eng">'+items[i].english+'</p> <p class="words_rus"> '+items[i].russian + '</p> <p class="words_transcription"> '+items[i].transcription + '</p>')
    speak(items[i].english)
}
function substringMatcher(strs) {
    return function findMatches(q, cb) {
        var matches, substrRegex;
        matches = [];
        substrRegex = new RegExp(q, 'i');
        $.each(strs, function(i, str) {
            if (substrRegex.test(str)) {
                matches.push({ value: str });
            }
        });
        cb(matches);
    }
}
