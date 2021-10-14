$(document).ready(function(){

  // Start notifications hook
  startNotoficiationHook();

  // Show notification center
  $('[kr-action="kr-notification-center"]').click(function(){
    showNotificationCenter();
  });

  // On click outside notification center content -> hide notifiction center
  $(document).mouseup(function(e)
  {
      var container = $('[kr-action="kr-notification-center"]');
      if (!container.is(e.target) && container.has(e.target).length === 0) closeNotificationCenter();
  });
});

/**
 * Show notification center
 */
function showNotificationCenter(){
  // Check if notifiction center is not already show
  if($('.kr-notification-center-open').length == 0){
    $('.kr-notification-center').show();
    $('.kr-notification-center').addClass('kr-notification-center-open');
    loadNotifications();
  }

}

/**
 * Close notification center
 */
function closeNotificationCenter(){

  // Check if notifiction center is already show
  if($('.kr-notification-center-open').length > 0){
    $('.kr-notification-center').hide();
    $('.kr-notification-center').removeClass('kr-notification-center-open');
    showLoadingNotificationCenter();
  }
}

let hookNotificationTO = null;
/**
 * Start notification hook
 */
function startNotoficiationHook(){
  clearTimeout(hookNotificationTO); hookNotificationTO = null;
  // Get notifications not seen count
  $.get($('body').attr('hrefapp') + "/app/modules/kr-notifications/src/actions/getNumNotifNS.php").done(function(data){

    // Parse json result
    let response = jQuery.parseJSON(data);

    if(response.error == 99){
      window.location.replace($('body').attr('hrefapp'));
    } else {
      // If notification count > 0
      if(response.notifications > 0){
        // If notification not seen already defined
        if(!$('[kr-action="kr-notification-center"]').hasClass('kr-header-icon-act')){

          // Play notifcation sound
          $("#kr-notification-center-audio").trigger('play');
          $('[kr-action="kr-notification-center"]').addClass('kr-header-icon-act');
        }
      } else {
        // Set notification not seen false
        $('[kr-action="kr-notification-center"]').removeClass('kr-header-icon-act');
      }

      if(response.manager_notifications > 0){
        $('.kr-leftnav-bubble-manager-notification').show();
        $('.kr-leftnav-bubble-manager-notification').html(response.manager_notifications);
      } else {
        $('.kr-leftnav-bubble-manager-notification').hide();
      }

      if(!$('div.kr-account.kr-identity-acc').hasClass(response.identity_status.class)){
        let listIdentityStatus = ['kr-identity-verified', 'kr-identity-in-verification', 'kr-identity-not-verified'];
        $.each(listIdentityStatus, function( index, statusFetched ) {
          $('div.kr-account.kr-identity-acc').removeClass(statusFetched);
        });
        $('div.kr-account.kr-identity-acc').addClass(response.identity_status.class);
        $('div.kr-account-pic > div').html(response.identity_status.icon);
      }

      if(response.identity_status.class == 'kr-identity-verified'){
        $('.kr-heeader-btn-identity').hide();
      } else {
        $('.kr-heeader-btn-identity').show();
      }

      if(response.notifications_number_unread == 0){
        $('.kr-notification-center-icon > span').hide();
      } else {
        $('.kr-notification-center-icon > span').show();
        $('.kr-notification-center-icon > span').html(response.notifications_number_unread);
      }

      hookNotificationTO = setTimeout(function(){ startNotoficiationHook() }, 1000);
    }



  }).fail(function(){
    showAlert('Ooops', 'Fail to start notification hook', 'error');
  });
}

/**
 * Show loading notification center
 */
function showLoadingNotificationCenter(){
  $('.kr-notification-center').html('<div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div>');
}

/**
 * Load notification list
 */
function loadNotifications(){

  // Show loading
  showLoadingNotificationCenter();

  // Get list notifications
  $.get($('body').attr('hrefapp') + "/app/modules/kr-notifications/src/actions/getNotificationsList.php").done(function(data){

    // Parse json result
    let response = jQuery.parseJSON(data);

    $('[kr-action="kr-notification-center"]').removeClass('kr-header-icon-act');
    if(jQuery.parseJSON(response.notifications).length == 0){
      $('.kr-notification-center').html('<div class="kr-notification-center-empty">' +
        '<span>No notifications to display.</span>' +
      '</div>');

    } else {

      // Clean all notifications
      $('.kr-notification-center').html('<ul></ul>');

      // Check response error
      if(response.error == 0){
        $.each(jQuery.parseJSON(response.notifications), function(k, v){
          // Add notification
          addNotification(v.title, v.body, v.icon, v.since, v.status, v.action);
        });
      } else {
        showAlert('Oops', response.msg, 'error');
      }

    }

  }).fail(function(){
    showAlert('Ooops', 'Fail to get notification list', 'error');
  });
}

/**
 * Add notification notification center
 * @param {String} title  Notification title
 * @param {String} body   Notification content
 * @param {String} icon   Notification icon path
 * @param {String} since  Notification since text
 * @param {Int} status Notification status
 */
function addNotification(title, body, icon, since, status, action = null){
  let notification = $('<li onclick="' + action + '" class="' + (status == 0 ? 'kr-notification-ns' : '') + '">' +
    (icon.length > 0 ? '<div class="kr-notification-center-icon">' + icon + '</div>' : '') +
    '<div class="kr-notification-center-inf">' +
      '<div>' +
        '<label>' + title + '</label><span>' + since + '</span>' +
      '</div>' +
      '<span>' + body + '</span>' +
    '</div>' +
  '</li>');

  // Append notification to notifiction center
  $('.kr-notification-center').find('ul').prepend(notification);
}
