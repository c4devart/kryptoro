/**
 * Init admin interface
 */
function initManager(){

  // Enable admin navigation menu
  $('.kr-manager-nav').find('li').off('click').click(function(){
    changeView($(this).attr('kr-module'), $(this).attr('kr-view'));
  });

  $('a.zoombox').zoombox();

  // Enable coin list pagination
  $('.kr-manager-pagination-coins').find('li').off('click').click(function(){
    changeView('admin', 'coins', {page:$(this).attr('kr-page')});
  });

  tippy('[title]');

  $('.btn-adm-user-c').off('click').click(function(e){
    showAccountView({adm_acc_user:$(this).attr('idu')});
    e.preventDefault();
    return false;
  });

  $('.btn-adm-user-delete').off('click').click(function(e){
    let idUserDelete = $(this).attr('idu');
    swal({
      title: 'Please comfirm',
      text: "Are you sure you want to delete the user ?",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "Yes, i'm sure"
    }).then((result) => {
      if (result.value) {
        $.post($('body').attr('hrefapp') + '/app/modules/kr-admin/src/actions/deleteUser.php', {id_user:idUserDelete}).done(function(data){
          let jsonRes = jQuery.parseJSON(data);
          if(jsonRes.error == 1){
            showAlert('Oops', jsonRes.msg, 'error');
          } else {
            showAlert('Success', 'Done !');
            changeView('manager', 'users');
          }
        }).fail(function(){
          showAlert('Oops', 'Fail to call script for delete user (404 or 500 error)', 'error');
        });
      }
    })
    e.preventDefault();
    return false;
  });

  $('.kr-admin-field').find('select').chosen();


  var start = moment().subtract(29, 'days');
  var end = moment();

  $('input[name="daterange"]').each(function(){
    let viewChange = $(this).attr('kr-view-used');
    let start = $(this).attr('start-date');

    let end = $(this).attr('end-date');
    $(this).daterangepicker({
        startDate: start,
        endDate: end,
        opens: 'left',
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end, label) {
      changeView('manager', viewChange, {startdate:start.format('YYYY-MM-DD'), enddate:end.format('YYYY-MM-DD')});
    });
  });

  $('.kr-manager-form-lst').submit(function(e){
    let idUseredChanged = $(this).attr('kr-form-callback-user');
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){

      // Decode result in JSON
      let resp = jQuery.parseJSON(data);

      // Check if result was an error
      if(resp.error == 1) showAlert('Oops', resp.msg, 'error');
      else showAlert(resp.title, resp.msg);

      showManagerUserInfos(idUseredChanged, 'orders');

    }).fail(function(){ // If fail to post (505, 404), show error message
      showAlert('Oops', 'Error : Fail to change balance (check php error log)', 'error');
    });
    e.preventDefault();
    return false;
  });


  $('.kr-manager-filter-search-f').submit(function(e){
    changeView('manager', $(this).attr('kr-manager-v'), {search:$(this).find('input[type="text"]').val()});
    e.preventDefault();
    return false;
  });

  _initStatsChartMain();

  $('.kr-adm-post-evs').off('submit').submit(function(e){

    // Post form
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){

      // Decode result in JSON
      let resp = jQuery.parseJSON(data);

      // Check if result was an error
      if(resp.error == 1) showAlert('Oops', resp.msg, 'error');
      else showAlert(resp.title, resp.msg);

      // Reload view
      changeView('manager', $('.kr-manager-nav-selected').attr('kr-view'));

    }).fail(function(){ // If fail to post (505, 404), show error message
      showAlert('Oops', 'Error : Fail to save (check php error log)', 'error');
    });

    e.preventDefault();
    return false;
  });

  $('.kr-manager-switch-opt-swtf').off('change').change(function(){
    changeView('manager', $(this).attr('kr-manager-v'), {filter:$(this).val()});
  });

}


function _showTransfertWizard(transfert_id){

  $.post($('body').attr('hrefapp') + '/app/modules/kr-manager/src/actions/wizardValidateBanktransfert.php', {transfert_id:transfert_id}).done(function(data){
    $.when($('body').prepend(data)).then(function(){
      _wizardTransfertController();
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load transfert payment wizard', 'error');
  });

}

function _wizardTransfertController(){
  $('.content_bank_transfert_wizard_line_mlc_amount_wf').off('keyup').keyup(function(){
    let fees = $('.content_bank_transfert_wizard_line_mlc_amount_fe').attr('kr-bk-fees') / 100;
    let amount = parseFloat($(this).val());
    if(!$.isNumeric(amount)) amount = 0;
    $('.content_bank_transfert_wizard_line_mlc_amount_fe').val(KRformatNumber(amount * parseFloat(fees), 8));
    $('.content_bank_transfert_wizard_line_mlc_amount_total').html(KRformatNumber(amount - (amount * parseFloat(fees)), 8))
  });

  $('[name="bt_vcs_accountreceived"]').off('change').change(function(){
    $('.content_bank_transfert_wizard_line_mlc_amount_symbol').html($(this).val());
  });

  $('.content_bank_transfert_wizard').off('submit').submit(function(e){
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let response = jQuery.parseJSON(data);
      if(response.error == 0){
        showAlert('Success', response.msg, 'success');
        _closeBankTransfert();
        changeView('manager', 'banktransferts');
      } else {
        showAlert('Oops', response.msg, 'error');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to process bank transfert (404, 505)', 'error');
    });
    e.preventDefault();
    return false;
  });
}

function _processTransfert(transfert_id){
  swal({
    title: 'Approve bank transfert',
    text: "Are you sure to approve the bank transfert ?",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, approve now'
  }).then((result) => {
    if (result.value) {
      $.post($('body').attr('hrefapp') + '/app/modules/kr-manager/src/actions/processBankTransfert.php', {transfert_id:transfert_id}).done(function(data){

      }).fail(function(){
        showAlert('Oops', 'Fail to process bank transfert (404, 505)', 'error');
      });
    }
  })
}

function _closeBankTransfert(){
  $('.bank_transfert_wizard').remove();
}

function _actionPaymentManager(action, type = 'approve'){

  if(type == "askproof"){
    swal({
      title: 'Ask a proof',
      text: 'Write below the reason for the proof',
      input: 'textarea',
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      confirmButtonText: 'Confirm',
    }).then(function(result) {
      if(!result.hasOwnProperty('dismiss')) {
        _actionPaymentManagerController(action, result.value);
        showAlert('Success', 'Proof ask to the user');
        changeView('manager', 'payments');
      }
    });
  } else if(type == "cancel"){
    swal({
      title: 'Cancel the payment',
      text: 'Write below the reason for the cancellation of payment',
      input: 'textarea',
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      confirmButtonText: 'Confirm',
    }).then(function(result) {
      if(!result.hasOwnProperty('dismiss')) {
        _actionPaymentManagerController(action, result.value);
        showAlert('Success', 'Cancellation of the payment done');
        changeView('manager', 'payments');
      }
    });
  } else {
    swal({
      title: 'Are you sure ?',
      text: "The payment will be processed",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, approve !'
    }).then((result) => {
      if (result.value) {
        _actionPaymentManagerController(action, result.value);
        showAlert('Success', 'Payment approved');
        changeView('manager', 'payments');
      }
    });
  }



}

function _actionPaymentManagerController(action, args = null){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-manager/src/actions/actionPaymentManager.php', {act:action, args:args}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);

  }).fail(function(){
    showAlert('Oops', 'Fail to do action on payment (script error or not found)', 'error');
  });
}

function _initStatsChartMain(){

  if($('#kr-stats-manager-chart').length == 0) return false;

  let axisList = $('.kr-manager-stats-graph').attr('kr-stats-mananger-xlist').split(',');

  let dataSetList = $('.kr-manager-stats-graph').attr('kr-stats-mananger-datasetlist').split(';');
  let dataSetTagList = $('.kr-manager-stats-graph').attr('kr-stats-mananger-datesettag').split(';');

  let dataSetFormated = [];
  let k = 0;
  let colorList = $('.kr-manager-stats-graph').attr('kr-stats-mananger-color').split(',');

  for (dataSet of dataSetList) {
    dataSetFormated.push({
      label: dataSetTagList[k],
      backgroundColor: colorList[k],
      borderColor: colorList[k],
      data: dataSet.split(','),
      fill: false
    });
    k++;
  }


		var config = {
			type: 'line',
			data: {
				labels: axisList,
				datasets: dataSetFormated
			},
			options: {
        layout: {
            padding: {
                left: 5,
                right: 5,
                top: 25,
                bottom: 0
            }
        },
				responsive: true,
        maintainAspectRatio: false,
				title: {
					display: false,
					text: 'Chart.js Line Chart'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
        legend: {
            display: true,
            position: 'left',
            labels: {
                fontColor: '#252525'
            }
        },
				scales: {
					xAxes: [{
						display: true,
            ticks: {
                display: false
            },
						scaleLabel: {
							display: false,
							labelString: 'Month'
						}
					}],
					yAxes: [{
						display: false,
            ticks: {
                display: false
            },
						scaleLabel: {
							display: false,
							labelString: 'Value'
						}
					}]
				}
			}
		};

  var ctx = document.getElementById('kr-stats-manager-chart').getContext('2d');
	window.myLine = new Chart(ctx, config);

}

function _showWithdrawMethod(id){

  $.get($('body').attr('hrefapp') + '/app/modules/kr-manager/src/actions/viewWitdhrawMethod.php', {id:id}).done(function(data){
    try {
      let infosJson = jQuery.parseJSON(data);
      if(infosJson.error == 1){
        showAlert('Oops', infosJson.msg, 'error');
      }
    } catch (e) {
      $.when($('body').prepend(data)).then(function(){
        _initWithdrawMethodViewController();
      });
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to load view method withdraw popup')
  });

}

function _initWithdrawMethodViewController(){

}

function _changeStatusIdentity(identiy, status, args = ""){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-identity/src/actions/changeIdentityStatus.php', {ididentity:identiy, status:status, args:args}).done(function(data){
  
  }).fail(function(){
    showAlert('Oops', 'Fail to change identity status (505)', 'error');
  });
}

function _declineIdentity(identity){
  swal({
    title: 'Decline identity',
    text: 'Write below the reason for rejection',
    input: 'textarea',
    showCancelButton: true,
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Confirm',
  }).then(function(result) {
    if(!result.hasOwnProperty('dismiss')) {
      _changeStatusIdentity(identity, 1, result.value);
      showAlert('Success', 'Identity declined');
      changeView('manager', 'identity');
    }
  });

};

function _approveIdentity(identity){
  swal({
    title: 'Approve identity',
    text: "Are you sure to approve the identity ?",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, approve now'
  }).then((result) => {
    if (result.value) {
      _changeStatusIdentity(identity, 2, '');
      showAlert('Success', 'Identity approved');
      changeView('manager', 'identity');
    }
  })
}

function showManagerUserInfos(iduser, page = 'card'){

  changeView('manager', 'userinfos', {idu:iduser, np:page});

}
