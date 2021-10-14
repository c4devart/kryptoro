function _highlightNumber(new_value, element, oldvalue = null, nf = 2){



  element.unmark({
    className: 'kr-hg-up'
  });

  element.unmark({
    className: 'kr-hg-down'
  });

  let currentNumber = element.html();
  if(oldvalue != null){
    currentNumber = KRformatNumber(oldvalue, nf);
  }

  element.html(new_value);
  let changeStart = false;
  let highlightText = "";
  JsDiff.diffChars(currentNumber, new_value).forEach(function(part){
    if(part.hasOwnProperty('added')) changeStart = true;
    if(changeStart && (part.hasOwnProperty('removed') != true || part.added == true)){
      highlightText = highlightText + part.value;
    }
  });

  if(highlightText.length > 0){
    element.markRegExp(new RegExp("" + highlightText + "$", "gmi"), {className: (KRunformatNumber(currentNumber) < KRunformatNumber(new_value) ? 'kr-hg-up' : 'kr-hg-down')});
  }



}
