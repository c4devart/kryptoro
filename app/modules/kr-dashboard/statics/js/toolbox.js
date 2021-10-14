function updateToolboxElementGraphZoom() {

      // for(container in chartList){
      //   chartList[container]['graph'].setOption({
      //       graphic: echarts.util.map(chartList[container]['graphic'], function (item, dataIndex) {
      //           return {
      //               position: chartList[container]['graph'].convertToPixel('grid', item)
      //           };
      //       })
      //   });
      // };

}

function initToolboxChartControler(){
  $('[kr-drawingtool]').off('click').click(function(){
    let container = $(this).parent().attr('container');
    let typeDrawingTool = $(this).attr('kr-drawingtool');
    selectAddTextObject(container, typeDrawingTool);
  });

  $('.kr-graph-tooledit').draggable({
    handle: ".kr-graph-tooledit-draggble"
  });

  $(document).off('keyup').keyup(function(e){
      if(e.keyCode == 46) {
        deleteSelectedGraphic();
      }
  });
}

function updateToolboxElementGraphZoomSingle(container) {

  let newGraphicList = [];
  let i = 0;
  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic != null){
      newGraphicList[i] = configgraphic;
      i++;
    }
  });

  chartList[container]['graphic'] = newGraphicList;

  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
      if(configgraphic.type == undefined) return true;
      if(configgraphic.type != "line"){
        chartList[container]['option']['graphic'][ngraphic]['position'] = chartList[container]['graph'].convertToPixel('grid', configgraphic.position);
      } else {
        chartList[container]['option']['graphic'][ngraphic]['shape'] = {
          x1: chartList[container]['graph'].convertToPixel('grid', configgraphic.shape.x1)[0],
          x2: chartList[container]['graph'].convertToPixel('grid', configgraphic.shape.x2)[0],
          y1: chartList[container]['graph'].convertToPixel('grid', configgraphic.shape.y1)[1],
          y2: chartList[container]['graph'].convertToPixel('grid', configgraphic.shape.y2)[1]
        }
      }
  });

  reloadContainerGraphic(container);

}

function generateIDGraphic(container){
  return chartList[container]['graphic'].length;
}

function addTrendLine(container, startDate, startValue){

  let idtrendline = generateIDGraphic(container);

  chartList[container]['graphic'].push({
    type: 'circle',
    id:'trendline-' + idtrendline + '-p1',
    shape: {
      cx: 0,
      cy: 0,
      r: 7
    },
    position: [startDate, startValue],
    invisible: false,
    silent: false,
    style: {
      fill: '#131722',
      stroke: '#f3f3f3',
      lineWidth:1
    },
    ondrag: echarts.util.curry(changeTrendLineByPoint, idtrendline, container, 1)
  });

  chartList[container]['graphic'].push({
    type: 'circle',
    id:'trendline-' + idtrendline + '-p2',
    shape: {
      cx: 0,
      cy: 0,
      r: 7
    },

    position: [startDate, startValue],
    invisible: false,
    silent: false,
    style: {
      fill: '#131722',
      stroke: '#f3f3f3',
      lineWidth:1
    },
    ondrag: echarts.util.curry(changeTrendLineByPoint, idtrendline, container, 2)
  });

  chartList[container]['graphic'].push({
    type: 'line',
    id:'trendline-' + idtrendline + '-line',
    shape: {
      x1: [startDate, startValue],
      x2: [startDate, startValue],
      y1: [startDate, startValue],
      y2: [startDate, startValue]
    },
    position: null,
    draggable: true,
    silent: false,
    invisible: false,
    style: {
      stroke: '#ff0000',
      lineWidth:2
    },
    ondrag: echarts.util.curry(TrendLineMoved, idtrendline, container),
    onmouseover: echarts.util.curry(TrendLineOvered, idtrendline, container),
    onclick: echarts.util.curry(TrendLineClicked, idtrendline, container),
    onmouseout: echarts.util.curry(TrendLineOuted, idtrendline, container),
  });

  reloadContainerGraphic(container);

}

let objectContainerSelected = null;
let containerObjectSelected = null;
let typeObjectSelected = null;



function TrendLineOvered(idtrendLine, container) {
  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic.id == "trendline-" + idtrendLine + "-p1" || configgraphic.id == "trendline-" + idtrendLine + "-p2"){
      chartList[container]['graphic'][ngraphic]['invisible'] = false;
    }
  });
  reloadContainerGraphic(container);
}

function TrendLineOuted(idtrendLine, container){
  if(objectContainerSelected != idtrendLine || containerObjectSelected != container){
    $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
      if(configgraphic.id == "trendline-" + idtrendLine + "-p1" || configgraphic.id == "trendline-" + idtrendLine + "-p2"){
        chartList[container]['graphic'][ngraphic]['invisible'] = true;
      }
    });
  }
  reloadContainerGraphic(container);
}

function TrendLineClicked(idtrendLine, container){

  unselectSelectedGraphic();
  containerObjectSelected = container;
  objectContainerSelected = idtrendLine;
  typeObjectSelected = 'trendline';
  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic.id == "trendline-" + idtrendLine + "-p1" || configgraphic.id == "trendline-" + idtrendLine + "-p2"){
      chartList[container]['graphic'][ngraphic]['style']['lineWidth'] = 1.5;
      chartList[container]['graphic'][ngraphic]['invisible'] = false;
    }
  });
  reloadToolBoxBar(container, idtrendLine, "line");
  reloadContainerGraphic(container);
}

function TrendLineMoved(idtrendline, container, npoint){

}

function changeTrendLineByPoint(idtrendline, container, npoint, dx, dy){

  let positionMoved = this.position;

  let positionPMoved = null;
  let positionBroth = null;

  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic.id == "trendline-" + idtrendline + "-p" + npoint){
      chartList[container]['graphic'][ngraphic]['position'] = chartList[container]['graph'].convertFromPixel('grid', positionMoved);
      positionPMoved = chartList[container]['graph'].convertFromPixel('grid', positionMoved);
    }

    if(configgraphic.id == "trendline-" + idtrendline + "-p" + (npoint == 2 ? 1 : 2)){
      positionBroth = chartList[container]['graphic'][ngraphic]['position'];
    }

    if(configgraphic.id == "trendline-" + idtrendline + "-line"){
      chartList[container]['graphic'][ngraphic]['shape'] = {
        x1: positionPMoved,
        y1: positionPMoved,
        x2: positionBroth,
        y2: positionBroth
      }
    }

  });



  reloadContainerGraphic(container);
}

let objectClick = null;
function selectAddTextObject(container, type = "text"){
  if(type == "text"){
    objectClick = function(){
      addText(container, currentDateSelected, currentValueSelected);
    }
  } else if(type == "trendline"){
    objectClick = function(){
      addTrendLine(container, currentDateSelected, currentValueSelected);
    }
  }

}

function addText(container, date, value, textValue = "Text", color = "#fff"){
  let idText = generateIDGraphic(container);

  chartList[container]['graphic'].push({
    type: 'text',
    id:'text-' + idText,
    position: [date, value],
    style: {
      text: textValue,
      fill: color
    },
    //ondrag: echarts.util.curry(changePositionText, idText, container),
    onclick: echarts.util.curry(TextClicked, idText, container)
  });

  reloadContainerGraphic(container);
}

function changePositionText(idText, container, dx, dy){
  let positionMoved = this.position;
  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic.id == "text-" + idText){
      chartList[container]['graphic'][ngraphic]['position'] = chartList[container]['graph'].convertFromPixel('grid', positionMoved);
    }
  });
  reloadContainerGraphic(container);
}

function TextClicked(idText, container){
  unselectSelectedGraphic();
  containerObjectSelected = container;
  objectContainerSelected = idText;
  typeObjectSelected = 'text';
  reloadToolBoxBar(container, idText, "text");
  reloadContainerGraphic(container);
}

function addIcon(container){
  let idIcon = generateIDGraphic(container);
  chartList[container]['graphic'].push({
    type: 'image',
    id:'icon-' + idIcon,
    position: ['10/06/2018 07:00:00', 7680],
    draggable: true,
    rotation: 30,
    style: {
      image:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiIHZpZXdCb3g9IjAgMCA4IDgiPgogIDxwYXRoIGQ9Ik0zIDB2MWg0djVoLTR2MWg1di03aC01em0xIDJ2MWgtNHYxaDR2MWwyLTEuNS0yLTEuNXoiIC8+Cjwvc3ZnPg==",
      width: '30',
      height: '30',
      fill:'#ff0000',
      stroke: '#ff0000'
    },
    ondrag: echarts.util.curry(changePositionIcon, idIcon, container)
  });
  reloadContainerGraphic(container);
}

function changePositionIcon(idIcon, container, dx, dy){
  let positionMoved = this.position;
  $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
    if(configgraphic.id == "icon-" + idIcon){
      chartList[container]['graphic'][ngraphic]['position'] = chartList[container]['graph'].convertFromPixel('grid', positionMoved);
    }
  });
  reloadContainerGraphic(container);
}

function reloadContainerGraphic(container){
      chartList[container]['option']['graphic'] = [];
      chartList[container]['option']['graphic'] = echarts.util.map(chartList[container]['graphic'], function (item, dataIndex) {

        if(item.type == 'line'){
          return {
              type: item.type,
              shape: {
                x1 : chartList[container]['graph'].convertToPixel('grid', item.shape.x1)[0],
                y1 : chartList[container]['graph'].convertToPixel('grid', item.shape.y1)[1],
                x2 : chartList[container]['graph'].convertToPixel('grid', item.shape.x2)[0],
                y2 : chartList[container]['graph'].convertToPixel('grid', item.shape.y2)[1]
              },
              draggable: true,
              ondrag: item.ondrag,
              onclick: item.onclick,
              invisible: item.invisible,
              silent: item.silent,
              onmouseover: item.onmouseover,
              onmouseout: item.onmouseout,
              style: item.style,
              z: 99
          };
        } else if(item.type == 'text'){

          return {
              type: item.type,
              id: item.id,
              position: chartList[container]['graph'].convertToPixel('grid', item.position),
              draggable: true,
              ondrag: item.ondrag,
              style: item.style,
              z: 101
          };

        } else if(item.type == 'image'){
          return {
              type: item.type,
              id: item.id,
              rotation: item.rotation,
              position: chartList[container]['graph'].convertToPixel('grid', item.position),
              draggable: true,
              ondrag: item.ondrag,
              style: item.style,
              z: 102
          };

        } else {
          return {
              type: item.type,
              position: chartList[container]['graph'].convertToPixel('grid', item.position),
              draggable: true,
              ondrag: item.ondrag,
              invisible: item.invisible,
              silent: item.silent,
              shape: item.shape,
              style: item.style,
              z: 100
          };
        }

      });

      chartList[container]['graph'].setOption(chartList[container]['option']);
}

function unselectSelectedGraphic(){
  if(containerObjectSelected == null) return false;
  $.each(chartList[containerObjectSelected]['graphic'], function(ngraphic, configgraphic){
    if(typeObjectSelected == "trendline"){
      if(configgraphic.id == "trendline-" + objectContainerSelected + "-p1" || configgraphic.id == "trendline-" + objectContainerSelected + "-p2"){
        chartList[containerObjectSelected]['graphic'][ngraphic]['style']['lineWidth'] = 1;
        chartList[containerObjectSelected]['graphic'][ngraphic]['invisible'] = true;
      }
    }
  });
  chartList[containerObjectSelected]['graph'].setOption(chartList[containerObjectSelected]['option']);

  $('.kr-graph-tooledit').hide();
  $('.kr-graph-tooledit-cell-active').removeClass('kr-graph-tooledit-cell-active');
}

function deleteSelectedGraphic(){
  if(objectContainerSelected == null) return false;
  $.each(chartList[containerObjectSelected]['graphic'], function(ngraphic, configgraphic){
    if(typeObjectSelected == "trendline"){
      if(configgraphic != undefined && (configgraphic.id == "trendline-" + objectContainerSelected + "-p1" || configgraphic.id == "trendline-" + objectContainerSelected + "-p2"  || configgraphic.id == "trendline-" + objectContainerSelected + "-line")){
        chartList[containerObjectSelected]['graphic'][ngraphic]['invisible'] = true;
        chartList[containerObjectSelected]['graphic'][ngraphic]['silent'] = true;
      }
    }
  });

  reloadContainerGraphic(containerObjectSelected);
}

function reloadToolBoxBar(container, idgraphic, type = "line"){

  let toolBoxContain = $('.kr-graph-tooledit[kr-toolbox-container="' + container + '"]');
  $('.kr-graph-tooledit').css('display', 'flex');
  toolBoxContain.show();

  toolBoxContain.find('[toolboxedit-type]').hide();
  toolBoxContain.find('[toolboxedit-type="' + type + '"]').show();

  let graphicElement = null;

  if(type == "line"){
    $.each(chartList[container]['graphic'], function(ngraphic, configgraphic){
      if(configgraphic.id == "trendline-" + idgraphic + "-line"){
        loadToolBoxBarComponent(chartList[container]['graphic'][ngraphic], type, ngraphic, container);
      }
    });
  }

}

function loadToolBoxBarComponent(graphicElement, type, ngraphic, container){

  let toolBoxObject = $('.kr-graph-tooledit-cell[toolboxedit-type="' + type + '"]');
  $.each(toolBoxObject, function(){

    let toolboxObjectItem = $(this);

    toolboxObjectItem.off('click').click(function(){

      $('.kr-graph-tooledit-cell-active').removeClass('kr-graph-tooledit-cell-active');
      toolboxObjectItem.addClass('kr-graph-tooledit-cell-active');
    });

    let assetRequire = toolboxObjectItem.attr('toolboxedit-asset').split('->');
    let currentAssetValue = graphicElement;
    $.each(assetRequire, function(k, assetItem){
      currentAssetValue = currentAssetValue[assetItem];
    });

    if(toolboxObjectItem.hasClass('kr-graph-tooledit-color')){
      toolboxObjectItem.find('div.kr-graph-tooledit-preview').css('background-color', currentAssetValue);
    }

    if(toolboxObjectItem.hasClass('kr-graph-tooledit-thickness')){
      toolboxObjectItem.find('div.kr-graph-tooledit-preview').css('height', currentAssetValue);
    }

    toolboxObjectItem.find('li').off('click').click(function(){
      let changeAsset = $(this).attr('kr-dropdown-item');
      if(assetRequire.length == 1) chartList[container]['graphic'][ngraphic][assetRequire[0]] = changeAsset;
      if(assetRequire.length == 2) chartList[container]['graphic'][ngraphic][assetRequire[0]][assetRequire[1]] = changeAsset;
      if(assetRequire.length == 3) chartList[container]['graphic'][ngraphic][assetRequire[0]][assetRequire[1]][assetRequire[2]] = changeAsset;
      if(assetRequire.length == 4) chartList[container]['graphic'][ngraphic][assetRequire[0]][assetRequire[1]][assetRequire[2]][assetRequire[3]] = changeAsset;
      reloadContainerGraphic(container);

      if(toolboxObjectItem.hasClass('kr-graph-tooledit-color')){
        toolboxObjectItem.find('div.kr-graph-tooledit-preview').css('background-color', changeAsset);
      }

      if(toolboxObjectItem.hasClass('kr-graph-tooledit-thickness')){
        toolboxObjectItem.find('div.kr-graph-tooledit-preview').css('height', changeAsset);
      }
    });

  });
}
