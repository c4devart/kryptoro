$(document).ready(function(){

  // View account
  $('.kr-account').click(function() {
    showAccountView();
  });

  // On click outside accout view --> close account view
  $(document).mouseup(function(e) {
    // Check element clicked
    var container = $(".kr-user, .kr-account, .btn-adm-user-c");
    if (!container.is(e.target) && container.has(e.target).length === 0) closeAccountView();
  });

});

/**
 * Show account view
 */
function showAccountView(args = {}, pageview = 'profile') {

  // Load account view
  $('body').prepend('<section class="kr-user animated slideInRight"><div class="kr-user-global-loading"><div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div></div></section>');

  // Get account view
  $.get($('body').attr('hrefapp') + '/app/modules/kr-user/views/account.php', args).done(function(data) {
    $.when($('.kr-user').html(data)).then(function() {
      // Change profile view content
      changeUserProfileView(pageview);
    });
  }).fail(function(){
    showAlert('Ooops', 'Fail to load account view', 'error');
  });
}

let closeAccountTimeout = null;

/**
 * Close account view
 * @param  {Boolean} [animated=true] Enable close animation
 */
function closeAccountView(animated = true) {

  // Reset timeout account
  clearTimeout(closeAccountTimeout);
  closeAccountTimeout = null;

  // If animation needed
  if (animated) {
    // Close account view
    $('.kr-user').removeClass('slideInRight').addClass('slideOutRight');
    closeAccountTimeout = setTimeout(function() {
      closeAccountView(false);
    }, 100);
  } else {
    $('.kr-user').remove();
  }

}

/**
 * Change profile view
 * @param  {String} newview            View
 * @param  {Function} [callback=null]  Callback
 */
function changeUserProfileView(newview, callback = null) {

  // Load user loading
  showUserloading();

  // Get new view content
  $.get($('body').attr('hrefapp') + '/app/modules/kr-user/views/' + newview + '.php').done(function(data) {
    // Show new view
    $.when($('.kr-user-content').html(data)).then(function() {
      // Init user controllers

      initUsercontrollers();

      // Run callback if given
      if (callback != null) callback();
    });
  }).fail(function(){
    showAlert('Ooops', 'Fail to change account view : ' + newview, 'error');
  });
}

/**
 * Init user controllers
 */
function initUsercontrollers() {
  // Init select object
  $('.kr-user-f-l').find('select').chosen();

  // Init user navigation
  $('.kr-user-nav').find('li').off('click').click(function() {

    // Logout button
    if ($(this).attr('kr-user-v') == "logout") {
      // Redirect to logout page
      window.location.replace($('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/logout.php');
      return false;
    }

    // Remove navigation selected item
    $('.kr-user-nav-selected').removeClass('kr-user-nav-selected');

    // Add selection to clicked view
    $(this).addClass('kr-user-nav-selected');

    // Change user view
    changeUserProfileView($(this).attr('kr-user-v'));
  });

  // Init pushbullet form
  $('.kr-user-notifi-steup-pb').off('submit').submit(function(e) {
    // Init pushbullet action
    initPushbullet($(this).serialize());
    e.preventDefault();
    return false;
  });

  // Remove pushbullet action
  $('.kr-user-notifi-steup-rmv-pb').off('submit').submit(function(e) {
    // Remove pushbullet
    removePushbullet($(this).serialize());
    e.preventDefault();
    return false;
  });

  // User back action
  $('.kr-user-back').off('click').click(function() {
    // Close account view
    closeAccountView(true);
  });

  $('.kr-user-update').off('submit').submit(function(e){
    $(this).find('submit').hide();
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      showUserloading();
      let response = jQuery.parseJSON(data);
      if(response.error == 0){
        if(response.reload == true) location.reload();
        else changeUserProfileView('profile');
      } else {
        changeUserProfileView('profile');
        showAlert('Oops', response.msg, 'error');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to change account infos', 'error');
      $('.kr-user-update').find('submit').show();
    });
    e.preventDefault();
    return false;
  });

  $('.kr-gogoletfs-check').off('submit').submit(function(e){
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let result = jQuery.parseJSON(data);
      if(result.error != 0){
        $('.kr-gogoletfs-check').find('span').html(result.msg).show();
      } else {
        changeUserProfileView('security');
      }

    }).fail(function(){
      showAlert('Oops', 'Fail to access to validator', 'error');
    });
    e.preventDefault();
    return false;
  });

  $('.kr-user-gdax').off('submit').submit(function(e){
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let result = jQuery.parseJSON(data);
      if(result.error != 0){
        showAlert('Oops', result.msg, 'error');
      } else {
        changeUserProfileView('gdax');
      }

    }).fail(function(){
      showAlert('Oops', 'Fail to remove GDAX Link', 'error');
    });
    e.preventDefault();
    return false;
  });

  $('.kr-gogletfs-remove').off('submit').submit(function(e) {
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let result = jQuery.parseJSON(data);
      if(result.error != 0){
        showAlert('Oops', result.msg, 'error');
      } else {
        changeUserProfileView('security');
      }

    }).fail(function(){
      showAlert('Oops', 'Fail to remove Google Authenticator', 'error');
    });
    e.preventDefault();
    return false;
  });

  $('.btn-showdetails-widthdraw').off('click').click(function(){
    $('.detailswidthraw-' + $(this).attr('kr-widthdraw-details')).toggle();
  });

  $('.widthdraw-method-remove').off('click').click(function(){
    let idWidthdrawMethod = $(this).attr('kr-widthdraw-idr');
    $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/removeWithdrawAccount.php', {id:idWidthdrawMethod}).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
      } else {
        showAlert('Success !', jsonRes.msg);
        changeUserProfileView('widthdraw');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to remove withdraw account', 'error');
    });
  });

  // User change picture object
  $('.kr-user-pic').dropzone({
    autoDiscover: true,
    url: $('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/changeUserPicture.php', // Drop file action
    uploadprogress(data, progress) { // Check upload progress
      $('.kr-user-pic-loading').fadeIn();
      $('.kr-user-pic-loading').css('width', progress + '%');
    },
    success: function(data, response) { // On upload success
      $.when($('.kr-user-pic-loading').fadeOut()).then(function() {
        $('.kr-user-pic-loading').css('width', '0%');
      });
      // Parse json error
      let resp = jQuery.parseJSON(response);
      if (resp.error == 0) {
        if(resp.reload){
          $('.kr-user-pic-s').css('background-image', 'url("' + resp.picture + '")');
        } else {
          $('.kr-user-pic-s-u').css('background-image', 'url("' + resp.picture + '")');
        }

      } else { // Error detected
        showAlert('Oops', resp.msg, 'error');
      }
    }
  });


}

/**
 * Show user loading
 */
function showUserloading() {
  $('.kr-user-content').html('<div class="kr-user-loading"><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div>');
}

/**
 * Init pushbullet notifications
 * @param  {Array} data  pushbullet arguments
 */
function initPushbullet(data) {
  // Show user loading
  showUserloading();

  // Init pushbullet action
  $.post($('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/initPushbullet.php', data).done(function(data) {
    // Parse json result
    let result = jQuery.parseJSON(data);
    if (result.error == 0) { // No error detected
      // Reload notifications view
      changeUserProfileView('notifications');
    } else { // Error detected
      changeUserProfileView('notifications', function() {
        $('.kr-user-content').find('.kr-msg').html(result.msg).show();
      });
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to init pushbullet', 'error');
  });
}

/**
 * Remove pushbullet action
 * @param  {Array} data  pushbullet args
 */
function removePushbullet(data) {
  // Show user loading
  showUserloading();

  // Init pushbullet remover action
  $.post($('body').attr('hrefapp') + '/app/modules/kr-user/src/actions/removePushbullet.php', data).done(function(data) {
    // Parse json result
    let result = jQuery.parseJSON(data);
    if (result.error == 0) { // No error detected
      // Reload notification view
      changeUserProfileView('notifications', function() {
        $('.kr-user-content').find('.kr-msg').removeClass('kr-msg-error').html(result.msg).show();
      });
    } else { // Error detected
      changeUserProfileView('notifications', function() {
        $('.kr-user-content').find('.kr-msg').addClass('kr-msg-error').html(result.msg).show();
      });
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to remove pushbullet', 'error');
  });

}
