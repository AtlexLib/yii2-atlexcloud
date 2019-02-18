


class AtlexCloudWidjet
{

    constructor(widgetPanel)
    {
        this.widgetPanel = widgetPanel;
        this.localPanel = widgetPanel.find('div.local-panel');
        this.remotePanel = widgetPanel.find('div.remote-panel');
        this.processesPanel = widgetPanel.find('div.atl-processes-panel');
        this.alertPanel = $('div.atl-alert-panel');
        var alert = this.alertPanel;
        alert.hide();
        this.alertPanel.on('click', function () {
            if(alert.is(":visible"))
                alert.hide(500);
        });


        this.localContainer = this.localPanel.find("div.object_container");
        this.remoteContainer = this.remotePanel.find("div.object_container");
        this.localPathPanel = this.localPanel.find('div.local-path-panel');
        this.remotePathPanel = this.remotePanel.find('div.remote-path-panel');

        this.loadingImg = '<div style="display: flex;align-items: center; justify-content: center; height: 100%;">' +
            '<img src="' + widgetPanel.find('img.atl-loading-img')[0].src + '" style="background-position: center; display:block;  margin: 0 auto"  width="50" height="50"></div>';
        this.processImg = '<div style="display: inline;align-items: center; justify-content: center; width:20px; height: 20px;">' +
            '<img src="' + widgetPanel.find('img.atl-loading-img')[0].src + '" style="background-position: center; display:inline;  margin: 0 auto"  width="15" height="15"></div>';


        this.remotePath = '/';
        this.localPath = ''
        this.currentTasks = [];
        this.registerEvent();
        this.registerToolsEvent();
    }

    registerToolsEvent(){
        var widget = this;
        var pp = this.processesPanel.find('div.full-proccess-info');
        pp.hide();

        this.processesPanel.on('click', function () {
            if(pp.is(":visible"))
                pp.hide(50);
            else
                pp.show(50);
        });

        $('#atl-modal-folder').on('show.bs.modal', function (event) {
            var type = $(event.relatedTarget).attr('atltype')
            $(this).find('.modal-body input').attr('atltype', type);
        })

        $('button.modal-save-btn').click(function(e) {
            var type = $('#atl-modal-folder').find('.modal-body input').attr('atltype');
            var name = $('#atl-modal-folder').find('.modal-body input').val();
            $('#atl-modal-folder').modal('hide');

            if (type == 'local')
                widget.localRequest('create_folder', {'path': widget.localPath, 'name': name});
            else
                widget.remoteRequest('create_folder', {'path': widget.remotePath, 'name': name});
        });

    }

    updateProcesses(){
        if(Object.keys(this.currentTasks).length == 0){
            this.processesPanel.hide();
        }else{
            this.processesPanel.show();
            this.processesPanel.find('div.atl-process-status').html('<b>Processes  (' + Object.keys(this.currentTasks).length + ') </b><i class="glyphicon glyphicon-triangle-bottom process-btn"></i>' )

            var fullInfo='';
            for (var key in this.currentTasks) {
                fullInfo += '<br>' + this.processImg + ' ' + this.currentTasks[key] ;
            }
            this.processesPanel.find('div.full-proccess-info').html(fullInfo);
        }

    }

    addTask(id, description)
    {
        this.currentTasks[id] = description;
        this.updateProcesses();
    }

    getTask(id)
    {
        return this.currentTasks[id];
    }

    registerEvent()
    {
        var widget = this;

        this.widgetPanel.find("button").unbind('click');
        this.widgetPanel.find("button").on('click', function () {
            var cmd = $(this).attr('atlcmd');
            var cmd = $(this).attr('atlcmd');

            var objectRow = $(this).parent().parent();
            if(objectRow.hasClass('disabled'))
                return;



            var objId = $(this).parent().parent().attr('id');
            if(cmd == 'download')
            {
                objectRow.addClass('disabled')
                widget.addTask(objId, cmd + ' ' + $(this).attr('atlpath')  + ' to ' + widget.localPath);
            }
            if(cmd == 'upload')
            {
                objectRow.addClass('disabled')
                widget.addTask(objId, cmd + ' ' + $(this).attr('atlpath')  + ' to ' + widget.remotePath);
            }



            switch (cmd) {
                case 'upload':
                    widget.localRequest(cmd, {
                        'path': $(this).attr('atlpath'),
                        'name': $(this).attr('atlname'),
                        'remote_path': widget.remotePath,
                        'task_id': objId
                    });
                    break;

                case 'download':
                    //widget.localRequest(cmd, {'path': $( this ).attr('atlpath'), 'name': $( this ).attr('atlname')});
                    widget.remoteRequest(cmd, {
                        'path': $(this).attr('atlpath'),
                        'name': $(this).attr('atlname'),
                        'type': $(this).attr('atlobj'),
                        'local_path': widget.localPath,
                        'task_id': objId
                    });
                    break;

                case 'delete':

                    if(confirm('Do you want to delete '+$( this ).attr('atlpath')+'?'))
                    {
                        objectRow.addClass('disabled')
                        widget.addTask(objId, cmd + ' ' + $(this).attr('atlpath'));

                        var type = $(this).attr('atltype');
                        if(type == 'local')
                        {
                            widget.localRequest(cmd, {
                                'path': $( this ).attr('atlpath'),
                                'type': $(this).attr('atlobj'),
                                'local_path': widget.localPath,
                                'task_id': objId
                            });

                        } else {
                            widget.remoteRequest(cmd, {
                                'path': $( this ).attr('atlpath'),
                                'type': $(this).attr('atlobj'),
                                'remote_path': widget.remotePath,
                                'task_id': objId
                            });

                        }

                    }

                    break;
            }
        });

        this.widgetPanel.find("div.atlobject").unbind('click');
        this.widgetPanel.find("div.atlobject").on('click', function () {
            var type = $(this).attr('atltype');
            var cmd = $(this).attr('atlcmd');

            switch (cmd) {
                case 'load':
                    if (type == 'local')
                        widget.localRequest(cmd, {'path': $(this).attr('atlpath')});
                    else
                        widget.remoteRequest(cmd, {'path': $(this).attr('atlpath')});
                    break;


            }
        });
    }

    registerPathEvent() {
        var widget = this;

        this.widgetPanel.find(".path-button").unbind('click');
        this.widgetPanel.find(".path-button").on('click', function () {
            var type = $(this).attr('atltype');
            var cmd = $(this).attr('atlcmd');

            switch (cmd) {
                case 'load':
                    if (type == 'local')
                    {
                        widget.localPath = '_';
                        widget.localRequest(cmd, {'path': $(this).attr('atlpath')});
                    } else {
                        widget.remotePath = '_';
                        widget.remoteRequest(cmd, {'path': $(this).attr('atlpath')});
                    }

                    break;


            }
        });
    }

    createLocalObjects(data) {
        this.localContainer.html('');

        for (var key in data) {
            var icon = '<i class="glyphicon glyphicon-file"> </i>';
            var atlobject = '';
            var hasTask = '';
            if (data[key].type == 'dir') {
                icon = '<i class="glyphicon glyphicon-folder-close"> </i>';
                atlobject = 'atlobject';
            }

            var title = '';
            if(this.getTask(data[key].key) != undefined){
                hasTask = 'disabled';
                title = ' title = "' +this.getTask(data[key].key) + '" ';
            }


            this.localContainer.append(
                '<div class="row '+hasTask+'" id="' + data[key].key + '" ' + title + '>' +
                '<div class="warning col-lg-10 ' + atlobject + '" atltype="local" atlcmd="load" atlpath="' + data[key].path + '"> ' + icon + '&nbsp;' + data[key].name + '</div>' +
                '<div class="col-lg-1 text-nowrap">' +
                '<button class="glyphicon glyphicon-remove atlbutton" atltype="local" atlobj="' + data[key].type + '" atlcmd="delete" atlname="' + data[key].name + '" title="Delete" atlpath="' + data[key].path + '"></button>' +
                '<button class="glyphicon glyphicon-cloud-upload atlbutton" atltype="local" atlcmd="upload" atlname="' + data[key].name + '" title="Upload" atlpath="' + data[key].path + '"></button>' +
                '</div>' +
                '<div class="col-lg-1 atlprocessing">' + this.processImg + '</div>' +
                '</div>');

        }
        this.registerEvent();
    }

    createRemoteObjects(data) {
        this.remoteContainer.html('');
        for (var key in data) {
            var icon = '<i class="glyphicon glyphicon-file"> </i>';
            var atlobject = '';
            var hasTask = ''
            if (data[key].type == 'dir') {
                icon = '<i class="glyphicon glyphicon-folder-close"> </i>';
                atlobject = 'atlobject';
            }

            var title = '';
            if(this.getTask(data[key].key) != undefined){
                hasTask = 'disabled'
                title = ' title = "' +this.getTask(data[key].key) + '" ';
            }

            this.remoteContainer.append(
                '<div class="row '+hasTask+'" id="' + data[key].key + '" ' + title + '>' +
                '<div class="warning col-lg-10 ' + atlobject + '" atltype="remote" atlcmd="load" atlpath="' + data[key].path + '"> ' + icon + '&nbsp;' + data[key].name + '</div>' +
                '<div class="col-lg-1 text-nowrap">' +
                '<button class="glyphicon glyphicon-remove atlbutton" atltype="remote" atlobj="' + data[key].type + '" atlcmd="delete" atlname="' + data[key].name + '" title="Delete" atlpath="' + data[key].path + '"></button>' +
                '<button class="glyphicon glyphicon-cloud-download atlbutton" atltype="remote" atlobj="' + data[key].type + '" atlcmd="download" atlname="' + data[key].name + '" title="Download" atlpath="' + data[key].path + '"></button>' +
                '</div>' +
                '<div class="col-lg-1 atlprocessing">' + this.processImg + '</div>' +
                '</div>');

        }
        this.registerEvent();
    }

    localRequest(cmd, data) {
        var widget = this;

        if (cmd == 'load')
            this.createLocalPath(data.path);


        $.ajax({
            url: atlLocalUrl,
            data: {'data': data, 'cmd': cmd},
            type: 'POST',
            //global: true,
            //async: true,
            success: function (result) {

                if (result.success) {

                    switch (cmd) {
                        case 'load':
                            widget.createLocalObjects(result.data);
                            break;

                        case 'upload':
                            widget.remoteRequest('load', {'path': widget.remotePath});
                            break;

                        case 'delete':
                            if(result.data.local_path == widget.localPath) {
                                widget.localPath = '_';
                                widget.localRequest('load', {'path': result.data.local_path});
                            }

                            break;

                        case 'create_folder':
                            if(result.data.path == widget.localPath) {
                                widget.localPath = '_';
                                widget.localRequest('load', {'path': result.data.path});
                            }

                            break;

                    }

                    if(result.data.task_id != undefined){
                        $('#' + result.data.task_id).removeClass('disabled');
                    }

                    if(result.tasks != undefined){
                        widget.currentTasks = result.tasks;
                    }
                    widget.updateProcesses();

                } else {
                    if(result.errorMessage != undefined) {
                        widget.alertPanel.html('<b>Error</b> '+ result.errorMessage);
                        widget.alertPanel.show();
                    }
                }

            },
            error: function (response) {
                var data = response.responseJSON;
                if(data.name != undefined && data.message != undefined){
                    widget.alertPanel.html('<b>'+data.name+'</b> '+ data.message);
                    widget.alertPanel.show();
                }

            }
        });
    };

    remoteRequest(cmd, data) {

        var widget = this;

        if (cmd == 'load')
            widget.createRemotePath(data.path)

        $.ajax({
            url: atlRemoteUrl,
            data: {'data': data, 'cmd': cmd},
            type: 'POST',
            //global: false,
            //async: false,
            success: function (result) {
                if (result.success) {
                    switch (cmd) {
                        case 'load':
                            widget.createRemoteObjects(result.data);
                            break;

                        case 'download':
                            widget.localRequest('load', {'path': widget.localPath});
                            break;

                        case 'delete':
                            if(result.data.remote_path == widget.remotePath) {
                                widget.remotePath = '_';
                                widget.remoteRequest('load', {'path': result.data.remote_path});
                            }

                            break;

                        case 'create_folder':
                            if(result.data.path == widget.remotePath) {
                                widget.remotePath = '_';
                                widget.remoteRequest('load', {'path': result.data.path});
                            }

                            break;

                    }

                    if(result.data.task_id != undefined){
                        $('#' + result.data.task_id).removeClass('disabled');
                    }

                    if(result.tasks != undefined){
                        widget.currentTasks = result.tasks;
                    }
                    widget.updateProcesses();

                } else {
                    if(result.errorMessage != undefined) {
                        widget.alertPanel.html('<b>Error</b> '+ result.errorMessage);
                        widget.alertPanel.show();
                    }
                }

            },
            error: function (response) {
                var data = response.responseJSON;
                if(data.name != undefined && data.message != undefined){
                    widget.alertPanel.html('<b>'+data.name+'</b> '+ data.message);
                    widget.alertPanel.show();
                }

                widget.alertPanel.show();
            }
        });
    }

    createLocalPath(path) {
        if(this.localPath != path){
            this.localContainer.html(this.loadingImg);
        }

        this.localPath = path;
        this.localPathPanel.html('');

        var pathParts = path.split('/');

        this.localPathPanel.append('<div class="path-button glyphicon glyphicon-home" atltype="local" atlcmd="load" atlpath=""></div>');

        var currentPath = "";

        for (var key in pathParts) {
            if (key == 0)
                continue;

            currentPath += "/" + pathParts[key];
            this.localPathPanel.append('/<div class="path-button" atltype="local" atlcmd="load" atlpath="' + currentPath + '">' + pathParts[key] + '</div>');

        }
        this.registerPathEvent();
    }

    createRemotePath(path) {
        if(this.remotePath != path)
        {
            this.remoteContainer.html(this.loadingImg);
        }

        this.remotePath = path;
        this.remotePathPanel.html('');

        var pathParts = path.split('/');

        this.remotePathPanel.append('<div class="path-button glyphicon glyphicon-home"  atltype="remote" atlcmd="load" atlpath=""></div>');

        var currentPath = "";

        for (var key in pathParts) {
            if (key != 0)
                currentPath += "/"

            currentPath += pathParts[key];
            this.remotePathPanel.append('/<div class="path-button" atltype="remote" atlcmd="load" atlpath="' + currentPath + '">' + pathParts[key] + '</div>');

        }
        this.registerPathEvent();
    }
}



$(function() {
    var acw = new AtlexCloudWidjet( $('#atlex_widget'));
    acw.localRequest('load', {'path':''});
    acw.remoteRequest('load', {'path':''});
});






