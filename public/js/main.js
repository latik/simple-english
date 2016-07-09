$(document).ready(function(){
    item = 0
    $.getJSON("/list", function(data) {
      items = data
      $('#pair').removeAttr("disabled")
      $('#rand').removeAttr("disabled")
      $('.button_start').removeAttr("disabled")
    })

    // checkbox menu
    $('#checkbox_menu_button_go').on('click', function () {
        $(this).button('toggle');
    });
    $('#checkbox_menu_button_random ').on('click', function () {
        $(this).button('toggle')
    });
    $('#checkbox_menu_button_category ').on('click', function () {
        $(this).button('toggle')
    });

    $(".container_words").fadeIn([2000, ]);
    $(".block_img").fadeIn([5000, ]);
    $(".block_img").fadeOut([5000, ]);
})

$('#button_next').on('click', function(){
    $('.button_start').addClass('hidden')
    if ( $("#checkbox_menu_button_random").prop("checked") ) {
          var randoms = Math.floor((Math.random() * items.length) + 1)
          ShowAndSpeak(randoms) } else if ( $("#checkbox_menu_button_go").prop("checked") ) {
       ShowAndSpeak(item++)
    }else {
            ShowAndSpeak(item++)
          }
})

$('.button_start').on('click', function(){
    ShowAndSpeak(item++)
    $('#button_next').removeClass('hidden')
    $('#button_next').show()
    $('.button_start').addClass('hidden')
})

$('#pair').on('click', function(){
    ShowAndSpeak(item++)
    $('.button_start').addClass('hidden')
    $('#button_next').removeClass('hidden')
    $('#listWords').empty()
    $('#button_next').show()
})

$('#rand').on('click', function(){
    $('.button_start').addClass('hidden')
    $('#button_next').removeClass('hidden')
    $('#button_next').show()
    var rand = Math.floor((Math.random() * items.length) + 1)
    ShowAndSpeak(rand)
    $('#listWords').empty()
})
$('#show-list').on('click', function(){
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
    var block_img = $('<img src="' + items[i].image + '" alt="' + items[i].english + '">')
    var words_eng = $('<p class="word words_eng">' + items[i].english + '</p>')
    var words_rus = $('<p class="words_rus">' + items[i].russian + '</p>')
    var words_transcription = $('<p class="words_transcription"> ' + items[i].transcription + '</p>')

    $('#show-pair').empty()
    $('#show-pair').append(block_img)
    $('#show-pair').append(words_eng)

    speak(items[i].english)

    setTimeout (function(){
        $('#show-pair').append(words_rus)
    }, 5000);

    /*$('#show-pair').append(words_transcription)*/

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
