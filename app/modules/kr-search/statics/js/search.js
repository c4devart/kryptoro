$(document).ready(function(){
  loadStaticSearchBox();
  loadSearchBox();

  $(document).mouseup(function(e)
  {
    var container = $(".kr-search-field-content");
    if (!container.is(e.target) && container.has(e.target).length === 0) hideSearchZone();
  });

});

function hideSearchZone(){
  setTimeout(function(){
    $('.kr-search').removeClass('kr-search-active');
  }, 100);
}

function loadStaticSearchBox(){
  $('.kr-search-field-content').each(function(){
    if($(this).hasClass('kr-search-field-content-inited')) return true;
    $(this).addClass('kr-search-field-content-inited');
    let searchID = Math.floor((Math.random() * 999999) + 1);
    $(this).attr('id', searchID);
    let searchFieldContent = $(this);

    let hideOnEmptySearch = false;
    if(searchFieldContent.attr('kr-search-hide-empty') !== typeof undefined && searchFieldContent.attr('kr-search-hide-empty') !== false && searchFieldContent.attr('kr-search-hide-empty') == "false") hideOnEmptySearch = true;

    let searchBox = $('<section class="kr-search" kr-search-fnc="' + $(this).attr('kr-search-callback') + '">' +
        '<header>' +
          '<input type="text" name="" value="">' +
        '</header>' +
        '<ul>' +
        '</ul>' +
      '</section>');
    $(this).append(searchBox);
    $(this).find('input[type="text"]').off('keyup').keyup(function(){
      if(SearchServiceRequest != null) SearchServiceRequest.abort();
      clearTimeout(SearchServiceTime); SearchServiceTime = null;
      let valSearchProcessing = $(this).val();
      if(valSearchProcessing.length > 0 || hideOnEmptySearch){
        searchBox.addClass('kr-search-active');
        SearchServiceTime = setTimeout(function(){
          processSearch(searchBox, valSearchProcessing, searchID);
        }, 50);
      } else {
        searchBox.removeClass('kr-search-active');
      }

    });

    // $('.kr-search-field-content').find('input[type="text"]').off('focusout').focusout(function(){
    //   searchBox.removeClass('kr-search-active');
    // });
    //

    $(this).find('input[type="text"]').off('focus').focus(function(){
      if($(this).val().length > 0 || hideOnEmptySearch){
        searchBox.addClass('kr-search-active');
      }
    });

  });
}

let oldSearchBoxValue = null;
let SearchServiceTime = null;
let SearchServiceRequest = null;
function loadSearchBox(){
  $('.kr-search').each(function(){
    let searchBox = $(this);
    $(this).find('input[type="text"]').off('focus').focus(function(){
      oldSearchBoxValue = $(this).val();
    });

    $(this).find('input[type="text"]').off('focusout').focusout(function(){
      if($(this).val().length == 0){
        $(this).val(oldSearchBoxValue);
        oldSearchBoxValue = null;
      }
    });

    $(this).find('input[type="text"]').off('keyup').keyup(function(){
      if(SearchServiceRequest != null) SearchServiceRequest.abort();
      clearTimeout(SearchServiceTime); SearchServiceTime = null;
      let valSearchProcessing = $(this).val();
      SearchServiceTime = setTimeout(function(){
        processSearch(searchBox, valSearchProcessing);
      }, 50);

    });
  });
}

function processSearch(searchBox, request, searhcfieldcontent = null){
  SearchServiceRequest = $.get($('body').attr('hrefapp') + '/app/modules/kr-search/src/actions/searchQuery.php', {request:request}).done(function(data){
    searchBox.find('ul').html('');
    let requestData = jQuery.parseJSON(data);
    if(requestData.error == 0){
      let fncCallBack = $('#' + searhcfieldcontent).attr('kr-search-callback');
      $.each(requestData.coinlist, function(k, item){
        if(item.currency_longname == null && item.currency_crypto_longname == null) return true;
        let itemSearch = $('<li>' +
          '<div>' +
            '<span>' + item.symbol_exchanges + item.currency_exchanges + '</span>' +
          '</div>' +
          '<div>' +
            '<span>' + (item.symbol_longname == null ? item.symbol_exchanges : item.symbol_longname) + ' / ' + (item.currency_longname == null ? item.currency_crypto_longname : item.currency_longname) + '</span>' +
          '</div>' +
          '<div>' +
            '<span>' + (requestData.native == 0 ? item.market_exchanges : '') + '</span>' +
          '</div>' +
        '</li>');
        itemSearch.click(function(e){
          window[fncCallBack](item.symbol_exchanges, item.currency_exchanges, item.market_exchanges);
          setTimeout(function(){
            hideSearchZone();
          }, 100);
          e.preventDefault();
          return false;
        });
        searchBox.find('ul').append(itemSearch);
      });
      searchBox.find('ul').mark(request);
    } else {
      showAlert('Oops', requestData.msg, 'error');
    }
  }).fail(function(){
    //showAlert('Oops', 'Fail to start search process', 'error');
  });

}

function showBigSearch(callback = null){

  $('.kr-searchpop > .kr-search-field-content').attr('kr-search-callback', callback);
  $('body').addClass('kr-nblr');
  $('section.kr-searchpop').addClass('kr-searchpop-show');
  $('#kr-search-field-searchpop').focus();

}

function closeBigSearch(){
  $('body').removeClass('kr-nblr');
  $('section.kr-searchpop').removeClass('kr-searchpop-show');
  $('#kr-search-field-searchpop').val('');
}
