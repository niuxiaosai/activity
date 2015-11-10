#!/bin/env bash
function doCheckEnv(){
	showMsg 1 "start check environment ..."
	checkEnv
	showMsg 1 "end check environment ..."
}

function doInit(){
	showMsg 1 "start initialization ..."
	init $1
	showMsg 1 "end initialization ..."
}

# 处理CSS
function doHandleCssWithFis(){
	showMsg 1 "start handle CSS with FIS ..."
	handleCssWithFis
	retainFile $OP_CSS_ROOT "css,eot,svg,ttf,woff"
	showMsg 1 "end handle CSS with FIS ..."
}

# 处理JS
function doHandleJsWithFis(){
	showMsg 1 "start handle JS with NODE && FIS ..."
	handleJsWithFis
	retainFile $OP_JS_ROOT "js"
	showMsg 1 "end handle JS with NODE && FIS ..."
}

#处理图像资源
function doHandleImages(){
	showMsg 1 "start handle Image resource ..."
	handleImages
	#仅保留这三类，严格区分大写。利于规范和维护
	retainFile $OP_IMG_ROOT "jpg,gif,png"
	showMsg 1 "end handle Image resource"
}

# 处理PHP
function doHandleWebroot(){
	showMsg 1 "start handle php ..."
	# prod/pre/sit/test
	handleWebroot $1
	#handleWebroot prod
	#仅保留这三类，严格区分大写。利于规范和维护
	retainFile $OP_WEB_ROOT "html,htm,php,txt,ico,swf,ttf,pem,tpl,properties,crt,plist"
	showMsg 1 "end handle php"
}

#处理Script脚本
function doHandleScript(){
	showMsg 1 "start handle script ..."
	handleScript
	showMsg 1 "end handle script"
}

#清理版本管理软件信息
function doCleanVerCtlInfo(){
	showMsg 1 "start clean version control info"
	cleanVerCtlInfo
	showMsg 1 "end clean version control info"
}

# 加入依赖包
function doDeps(){
	showMsg 1 "start handle dependence ..."
	handleDeps
	showMsg 1 "end handle dependence"
}

# 打包
function doPack(){
	showMsg 1 "start pack all file ..."
	pack
	showMsg 4 "static resource Version is: $VERSION"
	showMsg 4 "pack Version is: $PACK_VER"
	showMsg 1 "end pack all file"
}

function doOthers(){
	#### @TODO 真不明白图片为啥要保留两次呢。真是蛋疼, 临时这么加。 by hongbo
	#### 以后图片迁移走，不再放入git/svn中管理
	rm -rf $OP_WEB_ROOT/images
	cp -r $OP_IMG_ROOT/ $OP_WEB_ROOT/ 
}

# 上传包
function doUpload(){
	showMsg 1 "start upload pack ..."
	upload
	showMsg 1 "end upload pack"
}

function doDcmdUrl(){
	url=`genDcmdUrl $1 $2`
	showMsg 1 "You can check forward URL to start DCMD deploy"
	showMsg 4 "Env is:$1"
	showMsg 4 "DCMD START URL: $url"
}

function common(){
	env=$1
	doCheckEnv $env
	doInit $env
	doHandleWebroot $env
	doHandleScript $env
	doCleanVerCtlInfo $env
	doDeps $env
	doPack $env
	doUpload $env
	doDcmdUrl $env $PACK_VER
}

function doDev(){
	showMsg 1 "do nothing..."
}
