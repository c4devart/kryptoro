<?php
/**
 * CryptoIndicators class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoIndicators extends MySQL {

  /**
   * Graph container
   * @var String
   */
  private $container = null;

  /**
   * Indicator key
   * @var String
   */
  private $indicator = null;

  /**
   * Indicator Symbol
   * @var String
   */
  private $symbol = null;

  /**
   * Data indicator
   * @var Array
   */
  public $dataIndicator = null;

  /**
   * CryptoIndicators constructor
   * @param String $container Container
   * @param String $indicator Indicator key
   * @param String $symbol    Indicator symbol
   */
  public function __construct($container, $indicator = null, $symbol = null){
    $this->container = $container;
    if($indicator != null){
      $this->indicator = $indicator;
      $this->symbol = $symbol;

      // Load indicator data
      $this->_loadIndicators();
    }
  }

  /**
   * Get indicator container
   * @return String Container
   */
  public function _getContainer(){
    if(is_null($this->container)) throw new Exception("Error : Indicator container is null", 1);
    return $this->container;
  }

  /**
   * Get indicator key
   * @return String Indicator key
   */
  public function _getIndicator(){
    if(is_null($this->indicator)) throw new Exception("Error : Indicator is null on container (".$this->_getContainer().")", 1);
    return $this->indicator;
  }

  /**
   * Get indicator symbol
   * @return String indicator symbol
   */
  public function _getSymbol(){
    if(is_null($this->symbol)) throw new Exception("Error : Symbol is null on container (".$this->_getContainer().")", 1);
    return $this->symbol;
  }

  /**
   * Load indicator data
   * @return [type] [description]
   */
  public function _loadIndicators(){

    // Fetch Database indicator data
    $data = parent::querySqlRequest("SELECT * FROM indicators_krypto WHERE key_indicators=:key_indicators AND key_graph=:key_graph",
                                    [
                                      'key_indicators' => $this->_getIndicator(),
                                      'key_graph' => $this->_getContainer()
                                    ]);
    if(count($data) > 0){
      $this->dataIndicator = $data[0];
    }
  }

  /**
   * Get list indicator for this container
   * @return Array Indicator Array object
   */
  public function _getListIndicatorsContainer(){

    // Fetch indicator list associate to this container
    $listIndicator = parent::querySqlRequest("SELECT * FROM indicators_krypto WHERE key_graph=:key_graph",
                                              [
                                                'key_graph' => $this->_getContainer()
                                              ]);
    $resList = [];

    // List indicators
    foreach ($listIndicator as $dataIndicator) {
      // Create & append indicator to the list
      $resList[$dataIndicator['key_indicators']] = new CryptoIndicators($this->_getContainer(), $dataIndicator['key_indicators'], $dataIndicator['symbol_indicators']);
    }
    return $resList;
  }

  /**
   * Get indicator data by key
   * @param  String $key Data key
   * @return String      Data value associate to the key
   */
  private function _getKeyData($key){
    if(empty($this->dataIndicator)) throw new Exception("Error : Data not found for indicator", 1);
    if(!array_key_exists($key, $this->dataIndicator)) throw new Exception("Error : Key (".$key.") not found in container = ".$this->_getContainer(), 1);
    return $this->dataIndicator[$key];
  }

  /**
   * Get indicator title
   * @return String Indicator title
   */
  public function _getTitle(){ return $this->_getKeyData('title_indicators'); }

  /**
   * Get color line indicator available
   * @return Array Color list available
   */
  public static function _getColorLineAvailable(){
    return [
      "#18dae6", "#da4931", "#5ff347", "#3b3fe7",
      "#eda129", "#d726dd", "#d0e521", "#e820e1",
      "#c21b26"
    ];
  }

  /**
   * Get line format avalable
   * @return Array List line format
   */
  public static function _getLineAvailable(){
    return [ "1", "2", "3", "4" ];
  }

  /**
   * Get data indicator
   * @return Array Indicator data
   */
  public function _getDataIndicator(){
    if(is_null($this->_getKeyData('data_indicators'))) throw new Exception("Error : Indicator data is empty", 1);
    return json_decode($this->_getKeyData('data_indicators'), true);
  }

  /**
   * Get static data indicator
   * @return Array Static data indicator
   */
  public function _getStaticDataIndicator(){
    return $this->_getIndicatorsList()[$this->_getSymbol()];
  }

  /**
   * Get indicators args list
   * @return Array Args list indicator
   */
  public function _getArgs(){

    if(is_null($this->dataIndicator) || empty($this->_getDataIndicator())){
      $listArgs = [];
      foreach ($this->_getStaticDataIndicator()['cfg'] as $catArgs) {
        foreach ($catArgs as $infosCat) {
          foreach ($infosCat as $keyGlobal => $valGlobal) {
            $listArgs[] = $valGlobal['type']['default'];
          }
        }
      }
      return $listArgs;
    }

    $args = [];
    foreach ($this->_getDataIndicator() as $arg) {
      $args[] = $arg;
    }
    return $args;

  }

  /**
   * Add indicator
   * @param String $type  Type indicator
   * @param String $index Indicator index
   * @param String $title Indicator title
   */
  public function _addIndicator($type, $index, $title){

    // Check if indicator added is allowed
    $dataIndicator = CryptoIndicators::_getIndicatorsList();
    if(!array_key_exists($type, $dataIndicator)) throw new Exception("Error : Fail to find indicator (".$type.")", 1);

    // Insert indicator to the database
    $r = parent::execSqlRequest("INSERT INTO indicators_krypto (key_graph, key_indicators, symbol_indicators, title_indicators) VALUES
                                (:key_graph, :key_indicators, :symbol_indicators, :title_indicators)",
                                [
                                  'key_graph' => $this->_getContainer(),
                                  'key_indicators' => $index,
                                  'symbol_indicators' => $type,
                                  'title_indicators' => $title
                                ]);

    // Check if indicator was inserted
    if(!$r) throw new Exception("Error SQL : Fail to create indicator", 1);
    return true;

  }

  /**
   * Delete indicator from an container
   * @param  String $index Indicator index
   */
  public function _removeIndicator($index){

    // Delete indicator from database
    $r = parent::execSqlRequest("DELETE FROM indicators_krypto WHERE key_indicators=:key_indicators AND key_graph=:key_graph",
                                [
                                  'key_graph' => $this->_getContainer(),
                                  'key_indicators' => $index
                                ]);

    // Check SQL Query result
    if(!$r) throw new Exception("Error SQL : Fail to remove indicator", 1);
    return true;
  }

  public function _getIndicatorInformations($indic, $key){
    return parent::querySqlRequest("SELECT * FROM indicators_krypto WHERE key_graph=:key_graph AND symbol_indicators=:symbol_indicators AND key_indicators=:key_indicators",
                                                      [
                                                        'key_graph' => $this->_getContainer(),
                                                        'symbol_indicators' => $indic,
                                                        'key_indicators' => $key
                                                      ]);
  }

  public function _saveIndicatorInformations($sql, $args){
    $r = parent::execSqlRequest($sql, $args);
    if(!$r) throw new Exception("Error : Fail to save indicator informations (SQL Error)", 1);
    return $r;
  }

  /**
   * Get list indicator available
   * @return Array Indicator list
   */
  public static function _getIndicatorsList(){

    return [
      'EMA' => [
        "args" => ["period"],
        "name" => 'Exponential Moving Average (EMA)',
        "cfg" => [
          [
            "Main settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#c21b26",
                  "value" => "#c21b26"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
            ]
          ]
        ]
      ],
      'SMA' => [
        "args" => ["period"],
        "name" => 'Simple Moving Average (SMA)',
        "cfg" => [
          [
            "Main settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#da4931",
                  "value" => "#da4931"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
            ]
          ]
        ]
      ],
      'BBANDS' => [
        "args" => ["period", "deviation"],
        "name" => 'Bollinger Bands (BBands)',
        "cfg" => [
          [
            "Main settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 20,
                  "value" => 20
                ]
              ],
              "deviation" => [
                "title" => "Deviation",
                "type"  => [
                  "field" => "number",
                  "min" => 0,
                  "max" => 100,
                  "default" => 2,
                  "value" => 2
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
        ],
        [
          "Upper series" => [
              "upper_colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#5ff347",
                  "value" => "#5ff347"
                ]
              ]
          ],
          "Middle series" => [
              "middle_colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#18dae6",
                  "value" => "#18dae6"
                ]
              ]
          ],
          "Lower series" => [
              "lower_colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#c21b26",
                  "value" => "#c21b26"
                ]
              ]
          ]
        ]
      ],
    ],
    'ATR' => [
        "args" => ["period"],
        "name" => 'Average True Range (ATR)',
        "cfg" => [
          [
            "Main settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#ef642e",
                  "value" => "#ef642e"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
        ]
      ]
      ],
      'MACD' => [
        "args" => ["fastperiod", "slowperiod", "signalperiod"],
        "name" => 'MACD',
        "cfg" => [
          [
            "Main settings" => [
              "fastperiod" => [
                "title" => "Fast period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 12,
                  "value" => 12
                ]
              ],
              "slowperiod" => [
                "title" => "Slow period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 26,
                  "value" => 26
                ]
              ],
              "signalperiod" => [
                "title" => "Signal period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 9,
                  "value" => 9
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
        ],
        [
          "MACD series" => [
              "macd_colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#5ff347",
                  "value" => "#5ff347"
                ]
              ]
          ],
          "Signal series" => [
              "signal_colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#18dae6",
                  "value" => "#18dae6"
                ]
              ]
          ]
        ]
      ]
      ],
      'SO' => [
        "args" => ["kperiod", "dperiod"],
        "name" => 'Stochastic Oscillator',
        "cfg" => [
          [
            "Main settings" => [
              "kperiod" => [
                "title" => "kPeriod",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "dperiod" => [
                "title" => "dPeriod",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 3,
                  "value" => 3
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
        ],
        [
          "Overbought" => [
              "overbuy_value" => [
                "title" => "Value",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 100,
                  "default" => 80,
                  "value" => 80
                ]
              ],
              "overbuy_color" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#5ff347",
                  "value" => "#5ff347"
                ]
              ]
          ],
          "Oversold" => [
              "oversold_value" => [
                "title" => "Value",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 100,
                  "default" => 20,
                  "value" => 20
                ]
              ],
              "oversold_color" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#c21b26",
                  "value" => "#c21b26"
                ]
              ]
          ]
        ],
        [
          "kSeries" => [
              "kseries_color" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#d0e521",
                  "value" => "#d0e521"
                ]
              ]
          ],
          "dSeries" => [
              "dseries_color" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#18dae6",
                  "value" => "#18dae6"
                ]
              ]
          ]
        ]
      ]
      ],
      'RSI' => [
        "args" => ["period"],
        "name" => 'Relative Strength Index (RSI)',
        "cfg" => [
          [
            "Period settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#18dae6",
                  "value" => "#18dae6"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
       ],
       [
           "Over" => [
             "over_value" => [
               "title" => "Value",
               "type"  => [
                 "field" => "number",
                 "min" => 1,
                 "max" => 100,
                 "default" => 70,
                 "value" => 70
               ]
             ],
             "over_color" => [
               "title" => "Colour",
               "type"  => [
                 "field" => "color",
                 "default" => "#5ff347",
                 "value" => "#5ff347"
               ]
             ]
         ],
         "Under" => [
           "under_value" => [
             "title" => "Value",
             "type"  => [
               "field" => "number",
               "min" => 1,
               "max" => 100,
               "default" => 30,
               "value" => 30
             ]
           ],
           "under_color" => [
             "title" => "Colour",
             "type"  => [
               "field" => "color",
               "default" => "#c21b26",
               "value" => "#c21b26"
             ]
           ]
       ]
       ]
      ]
    ],
    'CCI' => [
        "args" => ["period"],
        "name" => 'Commodity Channel Index (CCI)',
        "cfg" => [
          [
            "Period settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#5ff347",
                  "value" => "#5ff347"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ],
              "colour_trend" => [
                "title" => "Trend color",
                "type"  => [
                  "field" => "color",
                  "default" => "#eda129",
                  "value" => "#eda129"
                ]
              ]
          ]
       ]
      ]
    ],
    'ROC' => [
        "args" => ["period"],
        "name" => 'Rate of Change (ROC)',
        "cfg" => [
          [
            "Period settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 12,
                  "value" => 12
                ]
              ],
              "colour" => [
                "title" => "Colour",
                "type"  => [
                  "field" => "color",
                  "default" => "#5ff347",
                  "value" => "#5ff347"
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
       ]
      ]
    ],
    'ADX' => [
        "args" => ["period", "adxperiod"],
        "name" => 'Average Directional Index (ADX)',
        "cfg" => [
          [
            "Period settings" => [
              "period" => [
                "title" => "Period",
                "type"  => [
                  "field" => "number",
                  "min" => 1,
                  "max" => 200,
                  "default" => 14,
                  "value" => 14
                ]
              ],
              "thickness" => [
                "title" => "Thickness",
                "type" => [
                  "field" => "line",
                  "default" => "1",
                  "value" => "1"
                ]
              ]
          ]
       ],
       [
         "ADX Series" => [
           "adxseries_color" => [
             "title" => "Colour",
             "type"  => [
               "field" => "color",
               "default" => "#5ff347",
               "value" => "#5ff347"
             ]
           ]
         ],
         "NDI Series (-)" => [
           "ndi_color" => [
             "title" => "Colour",
             "type"  => [
               "field" => "color",
               "default" => "#da4931",
               "value" => "#da4931"
             ]
           ]
         ],
         "PDI Series (+)" => [
           "pdi_color" => [
             "title" => "Colour",
             "type"  => [
               "field" => "color",
               "default" => "#c21b26",
               "value" => "#c21b26"
             ]
           ]
         ]
       ]
      ]
    ]
    ];

  }

}

?>
