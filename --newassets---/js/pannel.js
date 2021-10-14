let viewPost = null;

$(document).ready(function(){

  if($('.responsive-portrait').lenght > 0){
    if(screen.orientation.angle == 90){
      $('.responsive-portrait').hide();
    } else {
      $('.responsive-portrait').show();
    }

    window.addEventListener("orientationchange", function() {
    	if(screen.orientation.angle == 90){
        $('.responsive-portrait').hide();
      } else {
        $('.responsive-portrait').show();
      }
    }, false);
  }

  $('.kr-leftnav').find('li').click(function(){
    let args = $(this).attr('kr-args');
    if(typeof args !== typeof undefined && args !== false){
      changeView($(this).attr('kr-module'), $(this).attr('kr-view'), jQuery.parseJSON(args));
    } else {
      changeView($(this).attr('kr-module'), $(this).attr('kr-view'));
    }

  });

  $('.kr-logo').off('click').click(function(){
    changeView('dashboard', 'dashboard');
  });

  $('.kr-watching-wdsf').off('click').click(function(){
    $('.kr-leftside').addClass('kr-leftside-resp-on');
  });

  $('.kr-toggle-live-dash-trade').off('click').click(function(){
    toggleMarketLive();
  });

  $(document).mouseup(function(e)
  {
    var container = $(".kr-watching-wdsf");
    if (!container.is(e.target) && container.has(e.target).length === 0) $('.kr-leftside').removeClass('kr-leftside-resp-on');
  });

  enableTimeheader($('body').attr('kr-timestamp'));

  $('.kr-toggle-theme-white').click(function(){
    $(this).toggleClass('kr-white-theme');
    if($(this).hasClass('kr-white-theme')) {
      $('body').attr('kr-theme', 'light');
      updateUserSettings('white_mode', 'true');
    }
    else {
      $('body').attr('kr-theme', '');
      updateUserSettings('white_mode', 'false');
    }
    _reloadLogoType();
    _reloadContainerColor();

  });

  changeView('dashboard', 'dashboard');

});

let moduleConstruct = {
  'dashboard': {
    'dashboard': initDashboard,
    'custompage': initCustomPage
  },
  'marketanalysis': {
    'dashboard': initHeatmap,
    'coinlist': initCoinlist,
    'marketlist': initMarketList
  },
  'admin': {
    'dashboard': initAdmin,
    'users': initAdmin,
    'generalsettings': initAdmin,
    'coins': initAdmin,
    'currencies': initAdmin,
    'news-social': initAdmin,
    'mailsettings': initAdmin,
    'subscriptions': initAdmin,
    'payment': initAdmin,
    'intro': initAdmin,
    'trading': initAdmin,
    'withdraw': initAdmin,
    'cron': initAdmin,
    'additionalpages': initAdmin,
    'bankaccounts': initAdmin,
    'identity': initAdmin,
    'templates': initAdmin,
    'walletaddress': initAdmin,
    'autowithdrawconfigure': initAdmin
  },
  'blockfolio': {
    'blockfolio': initBlockFolio
  },
  'coin': {
    'coin': initCoinView
  },
  'manager': {
    'statistics': initManager,
    'withdraw': initManager,
    'banktransferts': initManager,
    'identity': initManager,
    'payments': initManager,
    'users': initManager,
    'userinfos': initManager,
    'orders': initManager,
    'subscriptions': initManager
  },
  'trade': {
    'balances': initBalanceView,
    'transactionsHistory': initBalanceView
  }
};

function enableTimeheader(timestamp){
  if($('.kr-current-time').length == 0) return false;
  let date = new Date(timestamp * 1000);
  let listMonth = $('.kr-current-time').attr('mlist').split(',');
  let listDay = $('.kr-current-time').attr('dlist').split(',');
  let dayNumber = date.getDay();
  if(dayNumber == 0) dayNumber = 7;
  $('.kr-current-time').find('span').html(listDay[dayNumber - 1] + ' ' + date.getDate() + ', ' + listMonth[date.getMonth()] + '  ' + (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ' : ' +
                                              (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ' : ' +
                                              (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds()));
  setTimeout(function(){
    enableTimeheader(parseFloat(timestamp) + 1);
  }, 1000);
}


function changeView(mod, view, args = {}, callback = null, forcehidewatching = false){
  if(mod == undefined || view == undefined) return false;

  $('.kr-leftnav').find('li[kr-view].kr-leftnav-select').removeClass('kr-leftnav-select');


  if($('body').attr('mbill') == "false"){
    if($('.kr-leftnav').find('li[kr-module="' + mod + '"]').attr('kr-view-allowed') == '*'){
      $('.kr-leftnav').find('li[kr-module="' + mod + '"]').addClass('kr-leftnav-select');
      if($('.kr-leftnav').find('li[kr-module="' + mod + '"]').attr('kr-modules-hleft') == "true" || forcehidewatching){
        $('.kr-leftside').hide();
      } else {
        $('.kr-leftside').show();
      }
    } else {
      $('.kr-leftnav').find('li[kr-module="' + mod + '"][kr-view="' + view + '"]').addClass('kr-leftnav-select');
      if($('.kr-leftnav').find('li[kr-module="' + mod + '"][kr-view="' + view + '"]').attr('kr-modules-hleft') == "true" || forcehidewatching){
        $('.kr-leftside').hide();
      } else {
        $('.kr-leftside').show();
      }
    }

    if(forcehidewatching){
      $('.kr-leftside').hide();
    }
  }


  if(viewPost != null) viewPost.abort();

  $('.kr-dashboard').html('');

  showDashboardLoading();

  $('.kr-dashboard').removeClass('kr-orderlist-shown-graph');

  viewPost = $.post($('body').attr('hrefapp') + '/app/modules/kr-' + mod + '/views/' + view + '.php', args).done(function(data){
    $.when($('.kr-dashboard').append(data)).then(function(){
      hideDashboardLoading();
      moduleConstruct[mod][view]();
      if(callback != null) callback.call();
    });
  }).fail(function(){
    //showAlert('Ooops', 'Fail to change view : ' + view, 'error');
  });;
}

function showDashboardLoading(){
  $('.kr-dashboard').prepend('<div class="kr-dashboard-loading"><div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div></div>');
}

function hideDashboardLoading(){
  $('.kr-dashboard-loading').fadeOut();
}

function appendPageTitle(subtitle){

  $(document).find('title').html(subtitle + ' — ' + $(document).find('title').attr('static-title'));

}

function KRformatNumber(value, decimal = 2){
  let infosFormat = $('body').attr('kr-numformat').split(':');
  return $.number(value, decimal, infosFormat[0], infosFormat[1]);
}

function KRunformatNumber(value){
  let infosFormat = $('body').attr('kr-numformat').split(':');
  value = value.replace(infosFormat[0], '.');
  value = value.replace(new RegExp(infosFormat[1], 'gi'), '');
  return value;
}

function updateUserSettings(k, v){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/changeUserSettings.php', {k:k, v:v}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to access to change settings script (404, 505)', 'error');
  })
}

function toggleMarketLive(){
  if(!$('.kr-live-dash-trade').hasClass('kr-trade-hide')){
    $('.kr-live-dash-trade').addClass('kr-trade-hide');
    $('.kr-live-dash-trade').find('.lnr-chevron-down').removeClass('lnr-chevron-down').addClass('lnr-chevron-up');
    $('.kr-live-dash-trade').find('.lnr-chevron-up').html('<use xlink:href="#lnr-chevron-up"></use>');
    updateUserSettings('hide_market', 'true');
  } else {
    $('.kr-live-dash-trade').removeClass('kr-trade-hide');
    $('.kr-live-dash-trade').find('.lnr-chevron-up').removeClass('lnr-chevron-up').addClass('lnr-chevron-down');
    $('.kr-live-dash-trade').find('.lnr-chevron-down').html('<use xlink:href="#lnr-chevron-down"></use>');
    updateUserSettings('hide_market', 'false');
  }
}

function _reloadLogoType(){
  if($('body').attr('kr-theme') == "light"){
    $('img').each(function(){
      let path = $(this).attr('src');
      if (path.indexOf("logo") >= 0){
        $(this).attr('src', $('body').attr('hrefapp') + $('body').attr('logopath-black'));
      }
    });
  } else {
    $('img').each(function(){
      let path = $(this).attr('src');
      if (path.indexOf("logo") >= 0){
        $(this).attr('src', $('body').attr('hrefapp') + $('body').attr('logopath'));
      }
    });
  }
}

function closeUpdateNewFeature(){
  $('.kr-adm-notif-popup').remove();
}


function _showDonationPopup(){
  $('body').addClass('kr-nblr');
  $('.kr-donation-list').css('display', 'flex');
  $('.kr-donation-sqrc').off('click').click(function(){
    $('.kr-donation-sqrc-img').hide();
    $(this).parent().parent().find('> section').css('display', 'flex');
  });

}

function _hideDonationPopup(){
  $('body').removeClass('kr-nblr');
  $('.kr-donation-list').hide();
  $('.kr-donation-sqrc-img').hide();
}

let ClipBoard = null;
function _initCopyClipboard(){
  if(ClipBoard != null) ClipBoard.destroy();
  ClipBoard = new ClipboardJS('[data-clipboard-target]');
  ClipBoard.on('success', function(e) {
    showAlert('Copied !', '');
    e.clearSelection();
  });
}

function _initFastSearch(){
  $('.fst-srch').fastselect();
}
