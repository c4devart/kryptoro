$(document).ready(function(){
  _initPopupControllers();
});

function _initPopupControllers(){
  $('[kr-chat-rid]').off('click').click(function(){
    let roomid = $(this).attr('kr-chat-rid');
    _loadPopup(function(){
      _changeRoomView(roomid);
    });
  });

  $('.kr-chat-right-openchat').click(function(){
    _loadPopup(function(){

    });
  });


}

let searchTimeout = null;

function _initChatPopupController(){

  $('[kr-change-chat-rid]').off('click').click(function(){
    _changeRoomView($(this).attr('kr-change-chat-rid'));
  });


  $('.kr-chat-close').off('click').click(function(){
    $('.kr-chat').remove();
    $('body').removeClass('kr-nblr');
  });



  $('#kr-chat-search-user').keyup(function(){
    clearTimeout(searchTimeout); searchTimeout = null;
    let textSearched = $(this).val().toLowerCase();

    searchTimeout = setTimeout(function(){
      $.post($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/searchUser.php', {search_query:textSearched}).done(function(data){
        let jsonRes = jQuery.parseJSON(data);
        $('.kr-chat-ulist').find('[kr-chat-room-tmp="true"]').remove();
        $.each(jsonRes.list, function(k, util){
          if($('.kr-chat-active-room[kr-chat-type="single"][kr-chat-us="' + util.id_encrypted + '"]').length <= 0){
            let ChatItemMenu = $('<li kr-chat-room-tmp="true" class="kr-chat-active-room" kr-chat-type="single" kr-chat-us="' + util.id_encrypted + '">' +
            '<div class="kr-chat-ulist-picture" style="background-color:' + util.color + ';background-image:url("' + util.picture + '")"></div>' +
            '<div class="kr-chat-ulist-infos">' +
              '<div>' +
                '<span>' + util.name + '</span>' +
              '</div>' +
            '</div>' +
            '</li>');
            ChatItemMenu.off('click').click(function(){
              _createNewRoom($(this).attr('kr-chat-us'));
            });
          
            $('.kr-chat-ulist').prepend(ChatItemMenu);
          }
        });
      }).fail(function(){

      });
    }, 800);

    $('.kr-chat-ulist').find('li.kr-chat-active-room').each(function(){
      let name = $(this).find('div.kr-chat-ulist-infos').find('span:first-child').html().toLowerCase();
      if (name.indexOf(textSearched) >= 0){
        $(this).show();
      } else {
        $(this).hide();
      }

    });

  });

  // $('#kr-chat-search-user').focusout(function(){
  //   $('.kr-chat-ulist').find('li.kr-chat-active-room').show();
  // });

}

function _loadPopup(callback = null){
  $('body').addClass('kr-nblr');
  $.get($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/loadChat.php').done(function(data){
    $.when($('body').prepend(data)).then(function(){
      _initChatPopupController();
      checkScrollDownPop();
      if(callback != null) callback();
    });
  });
}

function _createNewRoom(uid){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/createRoom.php', {uid:uid}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    } else {
      _changeRoomView(jsonRes.room.enc_id_room);
      $('.kr-chat-ulist').find('[kr-chat-room-tmp="true"]').remove();
      $('.kr-chat-ulist > li').show();
      $('#kr-chat-search-user').val('');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to create room (404, 505)', 'error');
  });
}

function _changeRoomView(roomid){
  $('#kr-chat-room-content').html('<div class="spinner"></div>');
  $.get($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/loadRoom.php', {room:roomid}).done(function(data){
    $.when($('#kr-chat-room-content').removeClass('kr-chat-room-content-nl').html(data)).then(function(){
      $('.kr-chat-right').find('[kr-chat-rid="' + roomid + '"]').removeClass('kr-chat-nmessage');
      initChatRoomController();
      checkScrollDownPop();
      focusWriteField();
    });
  });
}

function checkScrollDownPop(){
  // List popup available
  if($('.kr-chat-room-listmsg').length > 0) $('.kr-chat-room-listmsg').scrollTop($('.kr-chat-room-listmsg')[0].scrollHeight); // Scroll down
}

function reloadCurrentRoom(){
  let rid = $('form.chat-room-form').find('[name="room_id"]').val();
  _changeRoomView(rid);
}

function focusWriteField(){
  $('[name="room_msg"]').focus();
}
