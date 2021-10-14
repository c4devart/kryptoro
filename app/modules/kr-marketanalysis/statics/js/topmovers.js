// /**
//  * Init topmover function
//  */
function initTopmovers(){
  //loadListCoinMovers();
}
//
// /**
//  * Load list coins list available
//  */
// function loadListCoinMovers(){
//   return false;
//   $.get($('body').attr('hrefapp') + '/app/modules/kr-marketanalysis/actions/getCoinsList.php').done(function(data){
//
//     // Parse coin list
//     let coinsList = jQuery.parseJSON(data);
//
//     // Delete subscription
//     $.each(coinsList.coins, function(k, symbol){
//       deleteSubscription(symbol, coinsList.currency);
//     });
//
//     // Add subscription for each coins list
//     $.each(coinsList.coins, function(k, symbol){
//       addSubscribtion(symbol, coinsList.currency);
//     });
//   }).fail(function(){
//     showAlert('Ooops', 'Fail to get coins list', 'error');
//   });
// }
//
// /**
//  * Update top movers view
//  * @param  {Array} dataCoin Data coin
//  */
// function updateTopMovers(dataCoin){
//
//   return false;
//
//     // Check validty data coin given
//     if(isNaN(dataCoin.CHANGE24HOURPCT)) return false;
//
//     // If top mover for this coin is not added
//     if($('.r-marketmover-lst').find('[symbol="' + dataCoin.FROMSYMBOL + '"]').length == 0){
//
//       // Create to mover element
//       $('.r-marketmover-lst').append('<li symbol="' + dataCoin.FROMSYMBOL + '" pos="' + dataCoin.CHANGE24HOURPCT + '">' +
//         '<div class="r-marketmover-lst-symb kr-mono">' + dataCoin.FROMSYMBOL + '</div>' +
//         '<div class="r-marketmover-lst-dt">' +
//           '<div class="' + (dataCoin.CHANGE24HOURPCT < 0 ? 'r-marketmover-lst-dt-neg' : '') + '" style="width:' + (Math.abs(Math.ceil(dataCoin.CHANGE24HOURPCT)) * 2) + '%"></div>' +
//           '<span class="r-marketmover-lst-dt-evl">' + dataCoin.CHANGE24HOURPCT + '%</span>' +
//         '</div>' +
//       '</li>');
//     } else {
//
//       // Update top mover element
//       let item = $('.r-marketmover-lst').find('[symbol="' + dataCoin.FROMSYMBOL + '"]');
//       item.attr('pos', dataCoin.CHANGE24HOURPCT);
//       item.find('.r-marketmover-lst-dt-evl').html(dataCoin.CHANGE24HOURPCT + '%');
//       item.find('.r-marketmover-lst-dt').find('div').css('width', (Math.abs(Math.ceil(dataCoin.CHANGE24HOURPCT)) * 2) + '%');
//       if(dataCoin.CHANGE24HOURPCT > 0){ item.find('.r-marketmover-lst-dt').find('div').removeClass('r-marketmover-lst-dt-neg'); }
//       else item.find('.r-marketmover-lst-dt').find('div').addClass('r-marketmover-lst-dt-neg');
//     }
//
//     // Sort top movers
//     var moverItem = $('.r-marketmover-lst').children('li');
//
//     moverItem.detach().sort(function(a, b) {
//                 var astts = parseFloat($(a).attr('pos'));
//                 var bstts = parseFloat($(b).attr('pos'));
//                 if(astts > bstts) return -1;
//                 else if(astts < bstts) return 1;
//                 else {
//                   return $(a).attr('symbol') < $(b).attr('symbol');
//                 }
//             });
//
//     $('.r-marketmover-lst').append(moverItem);
// }
