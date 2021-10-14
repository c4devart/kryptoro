function initChatRoomController(){



  $(".kr-chat-body-room").dropzone({
    url: $('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/roomSendMessage.php',
    clickable: '.kr-chat-body-room .lnr-file-add',
    params: {
      room_id:$('input[name="room_id"]').val()
    },
    uploadprogress: function(file, progress, bytesSent){
      $('.kr-chat-upload-progress > div').css('opacity', '1');
      $('.kr-chat-upload-progress > div').css('width', progress + '%');
      if(progress == 100){
        $('.kr-chat-upload-progress > div').css('opacity', '0');
        setTimeout(function(){
          $('.kr-chat-upload-progress > div').css('width', '0%');
        }, 500);
      }
    },
  });

  $(".kr-chat-body-room").find('img').one("load", function() {
    checkScrollDownPop();
  }).each(function() {
    if(this.complete) $(this).load();
  });



  $('[kr-chat-blockuser]').off('click').click(function(){
    toggleBlockUser($(this).attr('kr-chat-blockuser'));
  });

  $('.kr-chat-sendmsg-btn').click(function(){
    $('.chat-room-form').submit();
  });

  $('.chat-room-form').off('submit').submit(function(e){
    if($('[name="room_msg"]').val().length == 0) return false;
    let sendData = $(this).serialize();
    $('[name="room_msg"]').val('');
    $.post($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/roomSendMessage.php', sendData).done(function(data){
      let response = jQuery.parseJSON(data);
      if(response.error == 1){
        showAlert('Oops', response.msg, 'error');
      }
      addMessageRoom($(this).find('[name="room_id"]').val(), response.msg, true);
      $(this).find('[name="room_msg"]').val('');
    }).fail(function(){
      showAlert('Oops', 'Fail to send message (505, 404)', 'error');
    });
    e.preventDefault();
    return false;
  });

}

function toggleBlockUser(idu){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/toggleBlockUser.php', {idu:idu}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    } else {
      reloadCurrentRoom();
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to toggle block user (404, 505)', 'error');
  });
}



function addMessageRoom(roomid, msg_data, me, user_data){

  if($('[kr-chat-msg-id="' + msg_data.id_encrypted + '"]').length > 0) return false;

  if(msg_data.type_msg_room_chat == "init_room") return true;

  //console.log(msg_data);

  let addGlobalMsg = false;
  let msgContent = '<div kr-chat-msg-id="' + msg_data.id_encrypted + '"><div>' + msg_data.value_msg_room_chat + '</div></div>';
  if(msg_data.type_msg_room_chat == "picture"){
    addGlobalMsg = true;
    msgContent = '<div kr-chat-msg-id="' + msg_data.id_encrypted + '"><img src="' + msg_data.value_msg_room_chat.replace('{APP_URL}', $('body').attr('hrefapp')) + '"/></div>';
  }

  if(msg_data.type_msg_room_chat == "file"){

    msgContent = '<div><div class="kr-chat-msg-file" onclick="window.open(\'' + msg_data.url_download + '\', \'_blank\');">' +
                  '<div>' +
                    '<div class="file-icon" data-type="' + msg_data.extension + '"></div>' +
                  '</div>' +
                  '<span>' + msg_data.file_name + '</span>' +
                '</div></div>';

    //console.log(msgContent);
  }


  let showMessagePicture = true;
  if($('.kr-chat-msg').length > 0){
    let lastMsgSended = $('.kr-chat-msg').last();
    if(lastMsgSended.attr('kr-chat-msg-idu') == msg_data.id_user){
      showMessagePicture = false;
      addGlobalMsg = true;
    } else {
      addGlobalMsg = true;
    }
  } else {
    addGlobalMsg = true;
  }

  if(addGlobalMsg && msg_data.hasOwnProperty('user_data')){
    let elem = $('<div kr-chat-msg-id="' + msg_data.id_encrypted + '" kr-chat-msg-idu="' + msg_data.id_user + '" kr-chat-msg-time="' + msg_data.date_msg_room_chat + '" class="kr-chat-msg ' + (me ? 'kr-chat-msg-me' : '') + '">' +
               '<div class="kr-chat-msg-picture">' + (showMessagePicture ?
                '<div style="background-color:' + (msg_data.user_data.hasOwnProperty('associate_color') ? msg_data.user_data.associate_color : '') + ';background-image:url(\'' + msg_data.user_data.picture + '\')"></div>' : '') +
               '</div>' +
               '<div class="kr-chat-msg-content">' +
                  '<span>' + (me ? '' : user_data.name + ', ') + '' + msg_data.hours + '</span>' + msgContent +
               '</div>' +
               '</div>'
              );

    $('.kr-chat-room-listmsg').append(elem);
  }

  checkScrollDownPop();

}
