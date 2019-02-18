<?php

use yii\base\View;
use yii\helpers\Url;

$bundle = \app\modules\atlex\assets\AtlexAssetBundel::register($this);

?>


<div class="modal fade" id="atl-modal-folder">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Create Folder</h4>
            </div>
            <div class="modal-body">
                <p>
                    Name: <input data-id="name" class="form-control" type="text" placeholder="Folder Name…" >
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary modal-save-btn">OK</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="alert alert-danger atl-alert-panel">

</div>

<div class="row atlborder" id="atlex_widget">
    <div class="row">

        <div class="col-lg-6 local-panel" >
            <div class="row atlrow">
                <div class="col-lg-11">
                    <b>Server Files</b>
                </div>
                <div class="col-lg-1 ">
                    <div class="glyphicon glyphicon-plus atlborder btn-newfolder" atltype="local" data-toggle="modal" data-target="#atl-modal-folder" title="Create Folder"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="atl-bordered local-path-panel">
                        <div class="path-button glyphicon glyphicon-home atlborder"></div>
                    </div>
                </div>
            </div>
            <div class="object_container">

            </div>


        </div>

        <div class="col-lg-6 remote-panel" >
            <div class="row atlrow">
                <div class="col-lg-11 ">
                    <b>Remote Cloud</b>
                </div>
                <div class="col-lg-1">
                    <div class="glyphicon glyphicon-plus atlborder btn-newfolder" atltype="remote" data-toggle="modal" data-target="#atl-modal-folder" title="Create Folder"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 ">
                    <div class="atl-bordered remote-path-panel">
                        <div class="path-button glyphicon glyphicon-home"></div>
                    </div>
                </div>
            </div>

            <div class="object_container"  source="root">

            </div>

        </div>
    </div>
    <div class="row atl-processes-panel">
        <div class="col-lg-12 ">
            <div class="col-lg-12 atl-bordered" style="background: #f5f5f5">
                <img src="<?= $bundle->baseUrl . '/img/loading.gif' ?>" class="atl-loading-img" width="15" height="15" style="display:inline; float:left; padding:2px">
                <div class="col-lg-11 atl-process-status">

                </div>
                <div class="row full-proccess-info">

                </div>


            </div>
        </div>

    </div>



</div>

