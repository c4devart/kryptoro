$(document).ready(function(){

  // Init news controllers
  initNewsControllers();

  // Show news & social clickable nav
  $('[kr-side-part]').off('click').click(function(){
    if($(this).attr('kr-side-part') == "kr-newsside"){
      toggleNews();
    } else if($(this).attr('kr-side-part') == "kr-calculator"){
      toggleCalculator();
    }
  });

  initNewsSideControllers();

});

function initNewsSideControllers(){

  $('.kr-newsside > header').find('[kr-news-tab]').off('hover').hover(function(){
    $('.kr-newsside > header > div > span').html($(this).attr('kr-news-tab-n'));
  });
  $('.kr-newsside > header').find('[kr-news-tab]').off('mouseleave').mouseleave(function(){
    $('.kr-newsside > header > div > span').html($('.kr-newsside > header').find('[kr-news-tab].kr-newsinfos-selected').attr('kr-news-tab-n'));
  });

  $('.kr-newsside > header').find('.lnr-cross').off('click').click(function(){
    toggleNews();
  });

  $('.kr-newsside > header').find('[kr-news-tab]').off('click').click(function(){
    $('.kr-newsside > header').find('.kr-newsinfos-selected').removeClass('kr-newsinfos-selected');
    $(this).addClass('kr-newsinfos-selected');
    loadNewsSideContent($(this).attr('kr-news-tab'));
  });

}

let loadNewsSideContentRequest = null;
function loadNewsSideContent(content){
  if(loadNewsSideContentRequest != null) loadNewsSideContentRequest.abort();
  $('.kr-newsinfos-content').html('<div class="spinner"></div>');
  loadNewsSideContentRequest = $.get($('body').attr('hrefapp') + '/app/modules/kr-news/src/actions/loadSide' + content + '.php').done(function(data){
    $('.kr-newsinfos-content').html(data);
    initNewsControllers();
  }).fail(function(){
    //showAlert('Oops', 'Fail to load : ' + content + ' news content', 'error');
  });
}

/**
 * Init news controllers
 * @return {[type]} [description]
 */
function initNewsControllers(){

  // On click on news, show detailed news
  $('[kr-news]').off('click').click(function(){
    loadNews($(this).attr('kr-news'));
  });

  // On click outside news content -> close detailed news
  $(document).mouseup(function(e)
  {
        var container = $(".kr-news-detailed");
        if (!container.is(e.target) && container.has(e.target).length === 0) closeNews();

        container = $(".kr-newsside");
        if (!container.is(e.target) && container.has(e.target).length === 0) $('.kr-newsside').removeClass('kr-leftnav-resp');

  });
}

/**
 * Load news detailed
 * @param  {String} uniq News uniq id
 */
function loadNews(uniq){

  // Hide news
  $('.kr-infosside').removeClass('kr-leftnav-resp');

  // Close actual news
  closeNews(false);

  // Add news detailed content
  $('body').prepend('<section class="kr-news-detailed animated slideInRight"><div class="kr-news-loading"><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div></section>');

  // Get news object
  $.post($('body').attr('hrefapp') + "/app/modules/kr-news/src/actions/loadNews.php", {uniqnews:uniq}).done(function(data){
    try { // Try to parse result as json --> error
      let response = jQuery.parseJSON(data);
      showAlert('Oops', response.msg, 'error');
    } catch (e) {
      $('.kr-news-detailed').html(data);
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to load news', 'error');
  });
}

/**
 * Close news timeout
 * @type {Object}
 */
let closeNewsTimeout = null;

/**
 * Close news detailed
 * @param  {Boolean} [animated=true] Animate close news
 */
function closeNews(animated = true){
  clearTimeout(closeNewsTimeout); closeNewsTimeout = null;
  if(animated){
    // Animate news close
    $('.kr-news-detailed').removeClass('slideInRight').addClass('slideOutRight');
    closeNewsTimeout = setTimeout(function(){
      closeNews(false);
    }, 1000);
  } else {
    // Remove news content
    $('.kr-news-detailed').remove();
  }

}

/**
 * Toggle news view
 */
function toggleNews(){

  // If news side already show -> hide
  if($('[kr-side-part="kr-newsside"]').hasClass('kr-leftnav-select')){
    $('[kr-side-part="kr-newsside"]').removeClass('kr-leftnav-select');
    $('.kr-newsside').removeClass('kr-leftnav-resp');
    checkInfosSide();
  } else { // Show news die
    $('.kr-newsside').addClass('kr-leftnav-resp');
    $('[kr-side-part="kr-newsside"]').addClass('kr-leftnav-select');
    if($('.kr-newsinfos-selected').length == 0){
      $('.kr-newsside > header > ul > li:first-child').addClass('kr-newsinfos-selected');
      loadNewsSideContent($('.kr-newsinfos-selected').attr('kr-news-tab'));
    }
    initNewsControllers();
  }

  // Reload graph size
  checkGraphResize();
}

function toggleCalculator(){
  if($('.kr-calculatorside').hasClass('kr-calculatorside-shown')){
    $('.kr-calculatorside').removeClass('kr-calculatorside-shown');
    $('[kr-side-part="kr-calculator"]').removeClass('kr-leftnav-select');
  } else { // Show news die
    $('.kr-calculatorside').addClass('kr-calculatorside-shown');
    $('[kr-side-part="kr-calculator"]').addClass('kr-leftnav-select');
    calculate($('.kr-calculatorside-lcsc').find('input[type="text"]').val());
  }

  // Reload graph size
  checkGraphResize();
}

/**
 * Check infos side
 */
function checkInfosSide(){
  // If news & social not show -> hide left side
  if(!$('[kr-side-part="kr-infosside"]').hasClass('kr-leftnav-select') && !$('[kr-side-part="kr-newsside"]').hasClass('kr-leftnav-select')){
    $('.kr-infosside').hide();
  }
}

let loadCalendarItemOut = null;
function loadCalendarItem(item){
  if(loadCalendarItemOut != null) loadCalendarItemOut.abort();
  $('.kr-calendar-item-opened').removeClass('kr-calendar-item-opened');
  $('.kr-calendar-item[kr-calendar-item="' + item + '"]').addClass('kr-calendar-item-opened');
  $('.kr-calendareventitem').html('<div class="spinner"></div>');
  $('.kr-calendareventitem').css('display', 'flex');
  loadCalendarItemOut = $.post($('body').attr('hrefapp') + '/app/modules/kr-news/src/actions/loadSideCalendarItem.php', {itemid:item}).done(function(data){
    $('.kr-calendareventitem').html(data);
  }).fail(function(){
    //showAlert('Oops', 'Fail to load calendar item view', 'error');
  });
  checkGraphResize();

}

function closeCalendarItemView(){
  $('.kr-calendareventitem').hide();
  $('.kr-calendar-item-opened').removeClass('kr-calendar-item-opened');
  checkGraphResize();
}
