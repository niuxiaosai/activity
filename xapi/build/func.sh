#!/bin/env bash
# function 

init(){
    rm -rf output;
    mkdir -p output/config output/lib/ output/working output/log output/data;
    echo $VERSION > output/config/VERSION
    if [ "$env" == "sit" ] ;then
    cmd="git checkout sit;"
    elif [ "$env" == "prod" ] ;then
    cmd="git checkout master;"
    fi
    showMsg 0 "$cmd"
    eval "$cmd"
    git pull --all
}

checkEnv(){
	echo 
}

function handleCssWithFis(){
    mkdir -p $OP_CSS_ROOT
    cd $CSS_ROOT;
    cmd="mkdir tmp; cp -r * tmp 2>/dev/null;cd tmp;"
    cmd=$cmd"sed -s -i \"s#../../images#/ffan_activity/images#g\" *.css;"
    cmd=$cmd"fis release -op -d $OP_CSS_ROOT;"
    cmd=$cmd"cd ../;rm -rf tmp;"
    # 修正FIS对资源处理的路径
    cmd=$cmd"sed -s -i \"s#/[a-zA-Z0-9_-]\+\.\(woff\|svg\|eot\|ttf\)#.\0#g\" $OP_CSS_ROOT/*.css;"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"
    cd $ROOT
}

function handleJsWithFis(){
    mkdir -p $OP_JS_ROOT
    cd $JS_ROOT;
    rm -rf release/*
    cmd="node $JS_ROOT/build_js/build.js; cp config.js release/"
    showMsg 0 "$cmd"
    eval "$cmd"

    SET_FIS_CONF=true
    if [ ! -e $JS_ROOT/release/fis-conf.js ]; then
        SET_FIS_CONF=false
        touch $JS_ROOT/release/fis-conf.js
    fi


    cmd="cd release; fis release -op --root=./ -d $OP_JS_ROOT;"
    # 修正FIS对require.js的兼容问题  请根据自己具体项目调整
    # 问题现象是fis会给requirejs的文件里define(['jquery'])加上路径define("pages/list",['jquery]),要么去掉，要么改为合适自己项目的路径
    cmd=$cmd"sed -i 's/pages/route\/pages/g' $OP_JS_ROOT/pages/h5list.js;"
    cmd=$cmd"sed -i 's/pages/route\/pages/g' $OP_JS_ROOT/pages/list.js;"
    cmd=$cmd"sed -i 's/pages/route\/pages/g' $OP_JS_ROOT/pages/lottery.js;"
    showMsg 0 "$cmd"
    eval "$cmd"
    if [ $SET_FIS_CONF == false ]; then
        rm -rf $JS_ROOT/release/fis-conf.js
    fi

    cd $ROOT
}

function handleImages(){
    mkdir -p $OP_IMG_ROOT
    cmd="cp -r "$IMG_ROOT"/* $OP_IMG_ROOT/"
    #cmd="mv "$IMG_ROOT"/* $OP_IMG_ROOT/"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"
    cd $ROOT
}

function retainFile(){
    path=$1
    type=`echo ","$2 | sed -s -e "s/\s\+//g" -e "s/,/ -type f ! -name *./g" -e "s/*.\([^ ]\+\)/\"*.\1\"/g" -e "s/^ -o//g"`
    cmd="find $1 $type | xargs rm -rf"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval $cmd
    return 0
}

function cleanFile(){
    path=$1
    type=`echo ","$2 | sed -s -e "s/\s\+//g" -e "s/,/ -o -type f -name *./g" -e "s/*.\([^ ]\+\)/\"*.\1\"/g" -e "s/^ -o//g"`
    cmd="find $1 $type | xargs rm -rf"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"
}

function handleWebroot(){
    env=$1
    mkdir -p $OP_WEB_ROOT;
    cmd="cd $OP_WEB_ROOT;"
    cmd=$cmd"cp -r  $WEB_ROOT/* $OP_WEB_ROOT;"
    cmd=$cmd"rm -rf Config.php;"
    if [ "$env" == "sit" ] ;then
        cmd=$cmd"mv ConfigSit.php Config.php;"
    elif [ "$env" == "dev" ] ;then
        cmd=$cmd"mv ConfigDebug.php Config.php;"
    elif [ "$env" == "pre" ] ;then
        cmd=$cmd"mv ConfigPre.php Config.php;"
    elif [ "$env" == "test" ] ;then
        cmd=$cmd"mv ConfigTest.php Config.php;"
    else
        # 为了安全，默认必须只给线上环境的配置，防止调试信息暴漏外部
        cmd=$cmd"mv ConfigRelease.php Config.php;"
    fi
    cmd=$cmd"rm -rf css ConfigSit.php ConfigPre.php ConfigTest.php ConfigRelease.php ConfigDebug.php;"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"

    cmd="echo > Version.php;"
    cmd="echo -e \"<?php\ndefine('VERSION', '$VERSION');\" > Version.php"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"

    cd $ROOT
}

function handleScript(){
    mkdir -p $OP_SCRIPT_ROOT
    cmd="cp $SCRIPT_ROOT/* $OP_SCRIPT_ROOT"
    $SHOW_CMD && showMsg 0 "$cmd"
    cd $ROOT
    eval "$cmd"
}

function cleanVerCtlInfo(){
    cd $ROOT
    cmd="find ./output | egrep \".svn|.git\" | xargs rm -rf"
    $SHOW_CMD && showMsg 0 "$cmd"
    cd $ROOT
    eval "$cmd"
}

function handleDeps(){
    cd $ROOT
    cmd="mkdir -p $OP_UTILS_ROOT $OP_COMMON_ROOT $OP_BASE_LIB $OP_DAO_ROOT;";
    cmd=$cmd"cd $BASE_LIB/ && svn up; cd $DAO_ROOT/ && svn up;"
    cmd=$cmd"cp -r $BASE_LIB/* $OP_BASE_LIB; cp -r $UTILS_ROOT/* $OP_UTILS_ROOT; cp -r $COMMON_ROOT/* $OP_COMMON_ROOT; cp -r $DAO_ROOT/* $OP_DAO_ROOT"
    $SHOW_CMD && showMsg 0 "$cmd"
    cd $ROOT
    eval "$cmd"
}

function pack(){
    cmd="cd $ROOT/output; tar zcf $PACK_NAME ./*"
    $SHOW_CMD && showMsg 0 "$cmd"
    eval "$cmd"
    cd $ROOT
}

function upload(){
    cmd="rsync -avr $ROOT/output/$PACK_NAME root@10.77.130.21::release/$DCMD_APP_NAME/"
    showMsg 0 "$cmd"
    cd $ROOT

    eval "$cmd"
}

function genDcmdUrl(){
    env=$1
    version=$2
    tmpt_name=$DCMD_TMPT_NAME
    app=$DCMD_APP_NAME

    #暂时只支持sit和prod
    if [ "$env" == "sit" ] ;then
        poll=$DCMD_POLL_SIT
    elif [ "$env" == "prod" ] ;then
        poll=$DCMD_POLL_PROD
    else
        #安全起见，未指定则部署到sit
        poll=$DCMD_POLL_SIT
    fi
    url="http://dcmd2.dianshang.wanda.cn/app/openapi/task/create_task.php?app=$app&version=$version&tmpt_name=$tmpt_name&pool=$poll"
    echo "$url";
    return 0
}

function showMsg(){
    type=$1
    msg=$2

    if [ $type -eq 2 ];then
        msg="\033[33m\033[01m[Warn] $msg\033[0m"
    elif [ $type -eq 3 ];then
        msg="\033[31m\033[01m[Err.] $msg\033[0m"
    elif [ $type -eq 4 ];then
        msg="\033[33m\033[01m[Info] $msg\033[0m"
    elif [ $type -eq 0 ];then
        msg="\033[36m\033[01m[ RUN CMD ]\033[0m $msg\033[0m"
    else
        msg="\033[32m[Info]\033[0m $msg"
    fi
    echo -e "\033[01m[`date '+%H:%M:%S'`]\033[0m "$msg
}

function usage(){
    echo "plase usage: $0 env";
    echo "env may be sit/prod"
    echo
}
