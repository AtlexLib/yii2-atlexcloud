<?php

namespace app\modules\atlex\controllers;

use app\modules\atlex\models\AtlexCloudModel;
use Atlex\Adapter\FtpAdapter;
use Atlex\Adapter\OpenStackAdapter;
use Atlex\Adapter\S3Adapter;
use Atlex\AtlexCloud;
use Atlex\Cloud\CloudObjectType;

use yii\web\Controller;





/**
 * Default controller for the `atlexcloud` module
 */
class DefaultController extends Controller
{

    /** @var \Atlex\AtlexCloud $adapter */
    private $remoteCloud;

    private $basePath;
    //private $currentTasks;
    private $session;
    private $projectId;
    private $taskId;
    private $cache;



    public function beforeAction($action)
    {
        $params = \Yii::$app->params['atlexcloud'];
        $this->projectId = $params['project'];
        $this->taskId = "__atl_tasks_" . $params['project'];
        $this->basePath = \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $params['local_folder'];

        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0777);
        }

        $adapterType = $params['default_adapter']; // s3 | openstack | ftp
        $adapter = null;

        switch($adapterType){
            case 's3':
                $adapter = new S3Adapter($params[$adapterType]['url'], $params[$adapterType]['user'],$params[$adapterType]['password']);
                break;

            case 'openstack':
                $adapter = new OpenStackAdapter($params[$adapterType]['url'], $params[$adapterType]['user'],$params[$adapterType]['password'], $this->projectId);
                break;

            case 'ftp':
                $adapter = new FtpAdapter($params[$adapterType]['url'], $params[$adapterType]['user'],$params[$adapterType]['password']);
                break;
        }

        $this->remoteCloud = $this->remoteCloud = new AtlexCloud($adapter);
        $this->cache = \Yii::$app->cache;
        $this->session = \Yii::$app->session;


        session_write_close();

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result) {

        return parent::afterAction($action, $result);
    }

    private function setTask($id, $description)
    {
        $tasks = $this->cache->get($this->taskId);
        if(is_array($tasks)){
            $tasks[$id] = $description;
            $this->cache->set($this->taskId, $tasks);
        }else{
            $tasks = [];
            $tasks[$id] = $description;
            $this->cache->set($this->taskId, $tasks);
        }

    }

    private function removeTask($id)
    {
        $tasks = $this->cache->get($this->taskId);
        if(is_array($tasks)) {
            unset($tasks[$id]);
            $this->cache->set($this->taskId, $tasks);
        }else{
            $this->cache->set($this->taskId, []);
        }

    }

    private function getTasks()
    {
        $tasks = $this->cache->get($this->taskId);
        if(is_array($tasks))
        {
            return $tasks;
        }
        return [];
    }



    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLocal()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $cmd = \Yii::$app->request->post('cmd');
        $data =  \Yii::$app->request->post('data');

        switch($cmd) {
            case "load":


                return [
                    'success' => true,
                    'data' => $this->getLocalFiles($this->basePath . DIRECTORY_SEPARATOR . $data["path"]),
                    'tasks' => $this->getTasks(),

                ];

                break;

            case "upload":

                $localPath = $this->basePath . $data["path"];

                $this->setTask($data["task_id"], $cmd . " " . $data["path"] . " to " . $data["remote_path"]);



                if (file_exists($localPath)) {
                    if (is_dir($localPath)) {
                        $this->remoteCloud->upload($localPath, $data["remote_path"] . DIRECTORY_SEPARATOR . $data["name"]);
                        $this->removeTask($data["task_id"]);

                    } else {
                        $this->remoteCloud->setObject($data["remote_path"] . DIRECTORY_SEPARATOR . $data["name"], fopen($localPath, "r"));
                        $this->removeTask($data["task_id"]);
                    }
                }



                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;

            case "create_folder":

                if (file_exists($this->basePath . $data['path'])) {
                    mkdir($this->basePath . $data['path'] . DIRECTORY_SEPARATOR . $data['name'], 0777);
                } else {
                    return [
                        'success' => false,
                        'data' => $this->basePath . $data['path'] . DIRECTORY_SEPARATOR . $data['name'],
                        'tasks' => $this->getTasks(),
                        'errorMessage' => 'Path not exists ' . $this->basePath . $data['path']
                    ];
                }


                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;


            case "delete":

                $this->setTask($data["task_id"], $cmd . " " . $data["path"]);

                if (file_exists($this->basePath . DIRECTORY_SEPARATOR . $data['path'])) {
                    if (is_dir($this->basePath  . DIRECTORY_SEPARATOR . $data['path'])) {
                        $this->removeDir($this->basePath  . DIRECTORY_SEPARATOR . $data['path']);
                        $this->removeTask($data["task_id"]);
                    } else {
                        unlink($this->basePath  . DIRECTORY_SEPARATOR . $data['path']);
                        $this->removeTask($data["task_id"]);
                    }



                    return [
                        'success' => true,
                        'data' => $data,
                        'tasks' => $this->getTasks()
                    ];

                } else {

                    return [
                        'success' => false,
                        'data' => $data,
                        'tasks' => $this->getTasks(),
                        'errorMessage' => 'Path not exists ' . $this->basePath . DIRECTORY_SEPARATOR . $data['path']
                    ];
                }


                break;


        }


    }

    public function actionRemote()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $cmd = \Yii::$app->request->post('cmd');
        $data =  \Yii::$app->request->post('data');

        switch($cmd){
            case "load":

                /** @var \Atlex\Cloud\CloudCollection $containers */
                $containers = $this->remoteCloud->get($data["path"]);



                $data = [];

                /** @var \Atlex\Cloud\CloudObject $containerObject */
                foreach($containers as $containerObject){
                    $data[] = [
                        'name' => $containerObject->getName(),
                        'type' => ($containerObject->getType() == CloudObjectType::CONTAINER) ? 'dir' : 'file',
                        'path' => $containerObject->getPath(),
                        'key'  => 'r' . implode(unpack("H*", '_remote' . DIRECTORY_SEPARATOR . $containerObject->getPath())),
                    ];
                }

                usort($data, function ($a, $b)
                {
                    return $a['type'].$a['name'] > $b['type'].$b['name'];

                });

                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;


            case "download":

                $localPath = $this->basePath . $data["local_path"] . DIRECTORY_SEPARATOR . $data['name'];

                $this->setTask($data["task_id"], $cmd . " " .  $data["path"] . " to " . $data["local_path"]);

                try {

                    if ($data["type"] == "file") {
                        try {
                            $file = fopen($localPath, "w+");
                            $this->remoteCloud->getObject($data["path"], $file);
                            $this->removeTask($data["task_id"]);
                        } catch (Exception $e) {
                            $this->removeTask($data["task_id"]);
                        }

                    } else {

                        /** @var \Atlex\Cloud\CloudCollection $containers */
                        $containers = $this->remoteCloud->get($data["path"]);

                        if (!file_exists($localPath)) {
                            mkdir($localPath, 0777);
                        }

                        /** @var \Atlex\Cloud\CloudObject $containerObject */
                        foreach ($containers as $containerObject) {
                            $containerObject->downloadTo($localPath);
                        }

                        $this->removeTask($data["task_id"]);
                    }
                }catch(\Exseption $e){
                    $this->setTask($data["task_id"], "###Error " .  $e->getMessage());
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;

            case "create_folder":

                $path = trim($data["path"] . "/" . $data["name"], "/");

                $this->remoteCloud->createContainer($path);


                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;

            case "delete":

                if($data["type"] == "file"){
                    $this->remoteCloud->deleteObject($data["path"]);
                } else if($data["type"] == "dir") {
                    $this->remoteCloud->deleteContainer($data["path"]);
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'tasks' => $this->getTasks(),
                ];

                break;

        }


    }

    private function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file)
        {
            (is_dir($dir.DIRECTORY_SEPARATOR.$file)) ? $this->removeDir($dir.DIRECTORY_SEPARATOR.$file) : unlink($dir.DIRECTORY_SEPARATOR.$file);
        }
        return rmdir($dir);
    }

    private function getLocalFiles($dir, &$results = array()){

        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = $dir.DIRECTORY_SEPARATOR.$value;
            $relPath = $this->getRelativePath($this->basePath, $dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = [
                    'type' => 'file',
                    't' => CloudObjectType::OBJECT,
                    'name' => $value,
                    'path' => $relPath,
                    'key'  => 'l' . implode(unpack("H*", '_local' . $relPath)),

                ];
            } else if($value != "." && $value != "..") {
                $results[] = [
                    'type' => 'dir',
                    't' => CloudObjectType::CONTAINER,
                    'name' => $value,
                    'path' => $this->getRelativePath($this->basePath, $dir.DIRECTORY_SEPARATOR.$value),
                    'key'  => 'l' . implode(unpack("H*", '_local' . $relPath)),
                ];

            }
        }

        usort($results, function ($a, $b)
        {
            if ($a['type'] == $b['type']) {
                return $a['name'] > $b['name'];
            } else {
                return $a['type'] > $b['type'];
            }
        });

        return $results;
    }

    private function getRelativePath($parent, $path)
    {
        $start = strlen($parent) + 1;
        return substr($path, $start, strlen($path) - $start);
    }


}
