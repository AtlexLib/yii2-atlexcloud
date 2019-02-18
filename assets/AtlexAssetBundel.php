<?php

namespace app\modules\atlex\assets;

use yii\web\AssetBundle;
use Yii;

class AtlexAssetBundel extends AssetBundle
{
    public $sourcePath = '@app/modules/atlex/assets/';
    public $js = [
        'js/acw.js'
    ];

    public $css = [
        'css/acw.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\JqueryAsset',
    ];

    public $publishOptions = [
        'forceCopy' => true,
        //'linkAssets' => true
    ];


}

