<?php

namespace app\modules\atlex\components;



use yii\web\View;
use yii\base\Widget;

use Atlex\AtlexCloud;
use Atlex\Adapter\S3Adapter;
use Atlex\Adapter\FtpAdapter;

use app\modules\atlex\assets\AtlexAssetBundel;
use yii\helpers\Url;


class AtlexCloudWidget extends Widget
{

    public $params;



    public function init()
    {
        parent::init();

        $bundle = AtlexAssetBundel::register($this->view);

        $localUrl = Url::to(['default/local']);
        $remoteUrl = Url::to(['default/remote']);

        $this->view->registerJs(" var atlLocalUrl = '{$localUrl}';  var atlRemoteUrl = '{$remoteUrl}';"
            , View::POS_HEAD);




    }


    public function run()
    {
        return $this->render('atlexcloud');
    }

}
