<?php

class DashboardToolbox extends MySQL {

  public static function _getColorAvailableList(){
    return ['#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e', '#16a085', '#27ae60', '#2980b9',
            '#8e44ad', '#2c3e50', '#e74c3c', '#ecf0f1', '#95a5a6', '#f39c12', '#d35400', '#c0392b',
            '#e8e00f', '#ff0000', '#00b4ff', '#00ff06', '#ffae00', '#3000ff'];
  }

  public static function _getTicknessAvailableList(){
    return [1,2,3,4,5];
  }

  public static function _getConfigurationItem(){
    return [
      'line' => [
        [
          'type' => 'color',
          'default' => '#f39c12',
          'assets' => 'style->stroke',
          'dropdown' => DashboardToolbox::_getColorAvailableList(),
        ],
        [
          'type' => 'thickness',
          'default' => '2',
          'assets' => 'style->lineWidth',
          'dropdown' => DashboardToolbox::_getTicknessAvailableList(),
        ]
      ]
    ];
  }

}

?>
