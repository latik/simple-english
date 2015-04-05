$(document).ready(function(){
    item = 0
    $.getJSON("/list", function(data) {
      items = data
      $('#pair').removeAttr("disabled")
      $('#rand').removeAttr("disabled")
    })

})
$('#pair').click(function(){
    ShowAndSpeak(item++)
})
$('#rand').click(function(){
    var rand = Math.floor((Math.random() * items.length) + 1)
    ShowAndSpeak(rand)
})
$('#show-list').click(function(){
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
    var text = $.trim($(this).text())
    speak(text)
    return false
});

// Create a new utterance for the specified text and add it to the queue.
function speak(text) {
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
    $('#show-pair').html('<h3><span class="word">'+items[i].english+'</span> - '+items[i].russian + '</h3>')
    speak(items[i].english)
}
