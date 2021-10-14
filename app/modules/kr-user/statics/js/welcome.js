let numGraphToComplete = parseInt($('.kr-wlcm-overlay-crypt-left').find('i').html());
$(document).ready(function(){
  if($('.kr-wlcm-overlay').length > 0){
    startWelcome();
    initWelcomeControllers();
  }
});

function initWelcomeControllers(){
  $('.kr-wlcm-overlay-es').each(function(){
    let tag = $(this).attr('kr-wlcm-f');
    $(this).find('li').click(function(){
      let data = $(this).attr('kr-wlcm-v');
      changeTagData(tag, data);
    });
  });

  $('.kr-wlcm-overlay-favcr').find('li').each(function(){
    $(this).click(function(){
      $(this).off('click');
      $(this).find('div').addClass('kr-wlcm-selecteditem');
      if($('.kr-wlcm-selecteditem').length == numGraphToComplete){
        $('.kr-wlcm-overlay-favcr').find('li').off('click');
        $('.kr-wlcm-overlay-crypt-left').hide();
        changeWelcomeView();
        setTimeout(function(){
          location.reload();
        }, 6000);
      } else {
        $('.kr-wlcm-overlay-crypt-left').find('i').html((numGraphToComplete - $('.kr-wlcm-selecteditem').length));
      }
    });
  });

}

function startWelcome(){

  setTimeout(function(){
    changeWelcomeView();
  }, 3000);

}

function changeTagData(tag, data){
  let args = {};

  if(tag == "crypto"){
    let associateGraph = $('.kr-dash-pan-cry[chart-init="false"]').first();
    associateGraph.attr('chart-init', 'true');
    args = {'symbol': data, 'container': associateGraph.attr('id')};
    $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/addTopList.php', args).done(function(data){
      let response = jQuery.parseJSON(data);
      if(response.error == 1) showAlert('Oops', response.msg, 'error');
    }).fail(function(){
      showAlert('Oops', 'Fail to update user profile (404, 505)', 'error');
    });

    args = {'symb': data, t:'add'};
    $.post($('body').attr('hrefapp') + '/app/modules/kr-watchinglist/src/actions/getWatchingItem.php', args).done(function(data){
      try {
        response = jQuery.parseJSON(data);
        if(response.error == 1) showAlert('Oops', response.msg, 'error');
      } catch (e) { }
    }).fail(function(){
      showAlert('Oops', 'Fail to update user profile (404, 505)', 'error');
    });
  } else {
    if(tag == "language") args = {'kr-user-language': data, kr_prof_u:$('.kr-wlcm-overlay').attr('kusr')};
    if(tag == "currency") args = {'kr-user-currency': data, kr_prof_u:$('.kr-wlcm-overlay').attr('kusr')};

    changeWelcomeView();

    $.post($('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/updateUserprofile.php', args).done(function(data){
      let response = jQuery.parseJSON(data);
      if(response.error == 1){
        showAlert('Oops', response.msg, 'error');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to update user profile (404, 505)', 'error');
    });
  }
}

function changeWelcomeView(){
  let current = parseInt($('.kr-wlcm-overlay > section').attr('nwlcm'));
  $('.kr-wlcm-overlay > section').attr('nwlcm', current + 1);
  $('.kr-wlcm-overlay > section').attr('class', 'kr-wlcm-nv' + (current + 1));
  if(current + 1 == $('.kr-wlcm-overlay').find('> section > section').length){
    setTimeout(function(){
      location.reload();
    }, 6000);
  }
}
