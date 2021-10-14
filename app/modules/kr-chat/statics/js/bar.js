let chatUserStatus = 1;
let checkActivityUserStatus = null;
$(document).ready(function(){

  _syncChatBar();

  checkActivityUserStatus = setTimeout(function(){
    _checkActivity();
  }, 10000);

  $(document).click(function(){
    _setAliveActivity();
  });

  $(window).focus(function(){
    _setAliveActivity();
  });

  $('.kr-chat-right-tgglebtn').off('click').click(function(){
    if($('.kr-chat-right').hasClass('kr-chat-right-hidded')){
      updateUserSettings('show_bar_chat', 'true');
      $('.kr-chat-right').removeClass('kr-chat-right-hidded');
      $(this).html('<svg class="lnr lnr-chevron-right"><use xlink:href="#lnr-chevron-right"></use></svg>');
    } else {
      updateUserSettings('show_bar_chat', 'false');
      $('.kr-chat-right').addClass('kr-chat-right-hidded');
      $(this).html('<svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg>');
    }
    checkGraphResize();
  });

});

function _setAliveActivity(){
  if($('.kr-chat-right').length == 0) return false;
  clearTimeout(checkActivityUserStatus); checkActivityUserStatus = null;
  chatUserStatus = 1;
  checkActivityUserStatus = setTimeout(function(){
    _checkActivity();
  }, 10000);
}

function _checkActivity(){
  if($('.kr-chat-right').length == 0) return false;
  clearTimeout(checkActivityUserStatus); checkActivityUserStatus = null;
  chatUserStatus = 2;
  checkActivityUserStatus = setTimeout(function(){
    _checkActivity();
  }, 10000);
}

let syncBarTimeout = null;
function _syncChatBar(){
  clearTimeout(syncBarTimeout); syncBarTimeout = null;

  if($('.kr-chat-right').length == 0) return false;

  $.get($('body').attr('hrefapp') + '/app/modules/kr-chat/src/actions/syncRightBar.php', {chat_user_status:chatUserStatus}).done(function(data){

    let dataJson = jQuery.parseJSON(data);
    if(dataJson.error == 1){

      showAlert('Oops', dataJson.msg, 'error');

    } else {

      $.each(dataJson.user_status, function(room_id, infos){
        if(infos.type == "room" || infos.status == 0 || infos.status == 3){
          $('[kr-chat-rid="' + room_id + '"]').find('.kr-chat-status').hide();
        } else {
          $('[kr-chat-rid="' + room_id + '"]').find('.kr-chat-status').show();
          $('[kr-chat-rid="' + room_id + '"]').find('.kr-chat-status').attr('class', 'kr-chat-status kr-chat-status-' + infos.status);
        }

      });

      $.each(dataJson.room_list, function(room_id, infos_room){
        if($('.kr-chat-right').find('[kr-chat-rid="' + room_id + '"]').length == 0){
          $('.kr-chat-right > ul').append('<li kr-chat-lastmsg="' + infos_room.last_msg + '" kr-chat-rid="' + room_id + '" class="" style="background-color:' + infos_room.color + '; background-image:url(\'' + infos_room.picture + '\')">' +
          '</li');
          _initPopupControllers();
        }
      });

      $.each(dataJson.last_msg, function(chat_id, last_msg){
        if($('[kr-chat-rid="' + chat_id + '"]').attr('kr-chat-lastmsg') != last_msg){
          if($('input[name="room_id"]').length == 0 && $('input[name="room_id"]').val() != chat_id){
            if(!$('[kr-chat-rid="' + chat_id + '"]').hasClass('kr-chat-nmessage')){
              $("#kr-notification-center-audio").trigger('play');
            }
            $('[kr-chat-rid="' + chat_id + '"]').addClass('kr-chat-nmessage');
          }
          $('[kr-chat-rid="' + chat_id + '"]').attr('kr-chat-lastmsg', last_msg);
          $('[kr-change-chat-rid="' + chat_id + '"]').attr('kr-chat-lastmsg', last_msg);
        }
      });


      $.each(dataJson.list_msg, function(room_id, msg_list){
        if($('.kr-chat-room-listmsg[kr-chat-room-id="' + room_id + '"]').length > 0){
          $.each(msg_list, function(msg_id, msg_data){
            addMessageRoom(room_id, msg_data, msg_data.me, msg_data.user_data);
            //console.log(msg_data);
          });
        }

        if($('[kr-change-chat-rid="' + room_id + '"]').length > 0){
          $('[kr-change-chat-rid="' + room_id + '"]').find('.kr-chat-rs-lmsg').html(msg_list[Object.keys(msg_list)[Object.keys(msg_list).length - 1]]['date_formated_lm']);
        }

      });



      //console.log(dataJson.room_list);



      var contacts = $('.kr-chat-right > ul'), cont = contacts.children('li');
      cont.detach().sort(function(a, b) {
        let astts = $(a).attr('kr-chat-lastmsg');
        let bstts = $(b).attr('kr-chat-lastmsg');
        return (astts > bstts) ? (astts > bstts) ? -1 : 0 : 1;
      });
      contacts.append(cont);

      // if($('ul.kr-chat-ulist').length > 0){
      //   let itemSorted = $('ul.kr-chat-ulist > li');
      //   itemSorted.detach().sort(function(a, b){
      //     console.log($(a).attr('kr-chat-lastmsg') + ' - ' + $(b).attr('kr-chat-lastmsg'));
      //       return +$(b).attr('kr-chat-lastmsg') - +$(a).attr('kr-chat-lastmsg');
      //   });
      //   itemSorted.append('ul.kr-chat-ulist');
      //   console.log('add');
      // }

      //console.log(dataJson.list_msg);
    }

    syncBarTimeout = setTimeout(function(){
      _syncChatBar();
    }, 3000);

  }).fail(function(){
    showAlert('Oops', 'Fail to sync chat bar', 'error');
  });




}
